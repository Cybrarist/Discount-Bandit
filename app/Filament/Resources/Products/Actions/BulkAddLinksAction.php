<?php

namespace App\Filament\Resources\Products\Actions;

use App\Filament\Resources\NotificationSettings\Schemas\NotificationSettingForm;
use App\Http\Controllers\Actions\Links\AttachLinkToProductAction;
use App\Models\Link;
use App\Models\Product;
use App\Services\URLParserService;
use Closure;
use Filament\Actions\Action;
use Filament\Forms\Components\Hidden;
use Filament\Forms\Components\Repeater;
use Filament\Forms\Components\TextInput;
use Filament\Notifications\Notification;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Components\Text;
use Filament\Schemas\Components\Utilities\Get;
use Filament\Schemas\Components\Utilities\Set;
use Filament\Schemas\Components\Wizard\Step;
use Filament\Schemas\Schema;
use Filament\Support\Enums\Width;
use Filament\Support\Icons\Heroicon;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;
use Livewire\Component;
use Throwable;

class BulkAddLinksAction extends Action
{
    public const string STATUS_OK = 'ok';

    public const string STATUS_TRACKED_ELSEWHERE = 'tracked_elsewhere';

    public const string STATUS_DUPLICATE = 'duplicate';

    public const string STATUS_ERROR = 'error';

    public static function getDefaultName(): ?string
    {
        return 'bulkAddLinks';
    }

    protected function setUp(): void
    {
        parent::setUp();

        $this->label('Bulk Add Links')
            ->icon(Heroicon::QueueList)
            ->modalWidth(Width::FiveExtraLarge)
            ->modalSubmitActionLabel('Add links')
            ->steps([

                Step::make('Links')
                    ->description('Default notification settings and URLs')
                    ->schema([

                        // Default notification that will be shared across all links
                        Section::make('Notification settings')
                            ->description('Applied to every link, you can adjust each link in the next step')
                            ->collapsible()
                            ->columns(3)
                            ->schema(NotificationSettingForm::configure(Schema::make())
                                ->getComponents()),

                        Repeater::make('urls')
                            ->label(fn (Get $get): string => 'URLs ('.count(self::filledUrls($get('urls'))).')')
                            ->helperText('Paste a list of links into any row to split it into rows, press Enter to add another row.')
                            ->simple(TextInput::make('url')
                                ->placeholder('https://')
                                ->live(onBlur: true)
                                ->afterStateUpdated(fn (?string $state, TextInput $component) => self::splitPastedUrls($state, $component))
                                ->extraInputAttributes([
                                    'x-init'                     => "\$nextTick(() => { if (! \$el.value && \$el.closest('.fi-fo-simple-repeater-item') === \$el.closest('.fi-fo-simple-repeater-items')?.lastElementChild) \$el.focus() })",
                                    'x-on:keydown.enter.prevent' => "if (\$el.value.trim()) { \$el.blur(); \$el.closest('.fi-fo-simple-repeater').querySelector('.fi-fo-simple-repeater-add button')?.click() }",
                                    'x-on:paste'                 => "const urls = \$event.clipboardData.getData('text').trim().split(/[\\s,]+/).filter(Boolean); if (urls.length > 1) { \$event.preventDefault(); \$el.value = urls.join(' '); \$el.dispatchEvent(new Event('input')); \$el.blur() }",
                                ]), )
                            ->defaultItems(1)
                            ->addActionLabel('Add URL')
                            ->reorderable(false)
                            ->rules([
                                fn (): Closure => function (string $attribute, mixed $value, Closure $fail): void {
                                    if (blank(self::filledUrls($value))) {
                                        $fail('Add at least one URL.');
                                    }
                                },
                            ]),
                    ])
                    ->afterValidation(function (Get $get, Set $set, Component $livewire): void {
                        $set('links', self::analyzeUrls($livewire->getRecord(), self::filledUrls($get('urls')), collect(AttachLinkToProductAction::NOTIFICATION_SETTING_FIELDS)
                            ->mapWithKeys(fn (string $field): array => [$field => $get($field)])
                            ->all()));
                    }),

                Step::make('Review')
                    ->description('Adjust notification settings per link')
                    ->schema([
                        Text::make(fn (Get $get): string => self::summary($get('links') ?? [])),

                        Repeater::make('links')
                            ->hiddenLabel()
                            ->defaultItems(0)
                            ->addable(false)
                            ->deletable(false)
                            ->reorderable(false)
                            ->schema([
                                Hidden::make('url'),
                                Hidden::make('store_id'),
                                Hidden::make('key'),
                                Hidden::make('status'),
                                Hidden::make('message'),
                                Hidden::make('parsed_status'),
                                Hidden::make('parsed_message'),

                                Section::make(fn (Get $get): ?string => $get('url'))
                                    ->description(fn (Get $get): ?string => $get('message'))
                                    ->icon(fn (Get $get): Heroicon => self::statusIcon($get('status')))
                                    ->iconColor(fn (Get $get): string => self::statusColor($get('status')))
                                    ->extraAttributes(fn (Get $get): array => self::statusOutline($get('status')))
                                    ->headerActions([
                                        Action::make('removeLink')
                                            ->label('Remove')
                                            ->tooltip('Remove this link')
                                            ->icon(Heroicon::Trash)
                                            ->iconButton()
                                            ->color('danger')
                                            ->action(function (Section $component): void {
                                                $item = $component->getContainer();
                                                $repeater = $item->getParentComponent();
                                                $items = $repeater->getRawState() ?? [];

                                                unset($items[Str::afterLast($item->getStatePath(), '.')]);

                                                $repeater->rawState(self::flagBatchDuplicates($items));
                                            }),
                                    ])
                                    ->key('linkSection')
                                    ->collapsed()
                                    ->compact()
                                    ->columns(3)
                                    ->schema(NotificationSettingForm::configure(Schema::make())->getComponents()),
                            ]),
                    ]),
            ])
            ->action(function (array $data, Component $livewire): void {
                $product = $livewire->getRecord();
                $added = 0;
                $skipped = 0;

                foreach ($data['links'] ?? [] as $item) {
                    if (in_array($item['status'], [
                        self::STATUS_ERROR,
                        self::STATUS_DUPLICATE,
                    ])) {
                        $skipped++;

                        continue;
                    }

                    try {
                        new AttachLinkToProductAction()->__invoke($product, $item);
                        $added++;
                    } catch (ValidationException) {
                        $skipped += count($data['links']) - $added - $skipped;
                        break;
                    }
                }

                Notification::make()
                    ->title("{$added} link(s) added")
                    ->body($skipped ? "{$skipped} link(s) skipped" : null)
                    ->status($skipped ? 'warning' : 'success')
                    ->send();

                $livewire->dispatch('refresh');
            });
    }

    /**
     * Normalise the URL rows (raw or dehydrated repeater state) into a list of non-empty URLs.
     *
     * @param  array<array-key, string|array{url?: ?string}|null>|null  $rows
     * @return list<string>
     */
    public static function filledUrls(?array $rows): array
    {
        return collect($rows ?? [])
            ->map(fn (string|array|null $row): string => trim((string) (is_array($row) ? ($row['url'] ?? '') : $row)))
            ->filter()
            ->values()
            ->all();
    }

    /**
     * When a row holds several URLs (pasted list), keep the first in that row and insert the rest as new rows after it.
     */
    private static function splitPastedUrls(?string $state, TextInput $component): void
    {
        $urls = preg_split('/[\s,]+/', trim((string) $state), -1, PREG_SPLIT_NO_EMPTY);

        if (count($urls) < 2) {
            return;
        }

        $row = $component->getContainer();
        $repeater = $row->getParentComponent();
        $currentRowKey = Str::afterLast($row->getStatePath(), '.');
        $rows = [];

        foreach ($repeater->getRawState() ?? [] as $rowKey => $rowData) {
            if ((string) $rowKey !== $currentRowKey) {
                $rows[$rowKey] = $rowData;

                continue;
            }

            $rows[$rowKey] = ['url' => array_shift($urls)];

            foreach ($urls as $url) {
                $rows[$repeater->generateUuid() ?? (string) Str::uuid()] = ['url' => $url];
            }
        }

        $repeater->rawState($rows);
    }

    /**
     * Parse each URL and flag errors, links already on the product and duplicates within the batch.
     *
     * @param  list<string>  $urls
     * @param  array<string, mixed>  $defaults
     * @return array<string, array<string, mixed>>
     */
    public static function analyzeUrls(Product $product, array $urls, array $defaults): array
    {
        $items = collect($urls)
            ->mapWithKeys(function (string $url) use ($product, $defaults): array {
                [
                    $status,
                    $message,
                    $storeId,
                    $key
                ] = self::parseUrl($product, $url);

                return [
                    (string) Str::uuid() => [
                        ...$defaults,
                        'url'            => $url,
                        'store_id'       => $storeId,
                        'key'            => $key,
                        'parsed_status'  => $status,
                        'parsed_message' => $message,
                    ],
                ];
            })
            ->all();

        return self::flagBatchDuplicates($items);
    }

    /**
     * Recalculate the status of each item, marking any later repeat of the same store and key as a duplicate.
     *
     * @param  array<array-key, array<string, mixed>>  $items
     * @return array<array-key, array<string, mixed>>
     */
    public static function flagBatchDuplicates(array $items): array
    {
        $seenLinks = [];

        foreach ($items as $itemKey => $item) {
            $identifier = "{$item['store_id']}|{$item['key']}";
            $isBatchDuplicate = filled($item['store_id']) && in_array($identifier, $seenLinks);

            $items[$itemKey]['status'] = $isBatchDuplicate ? self::STATUS_DUPLICATE : $item['parsed_status'];
            $items[$itemKey]['message'] = $isBatchDuplicate ? 'Duplicate of another URL in this list' : $item['parsed_message'];

            $seenLinks[] = $identifier;
        }

        return $items;
    }

    /**
     * @return array{0: string, 1: ?string, 2: ?int, 3: ?string}
     */
    private static function parseUrl(Product $product, string $url): array
    {
        try {
            $service = new URLParserService;
            $service->setup($url);
        } catch (Throwable $exception) {
            return [
                self::STATUS_ERROR,
                $exception->getMessage() ?: "Couldn't parse this URL",
                null,
                null,
            ];
        }

        $key = $service->product_key;

        $existingLink = Link::withoutGlobalScopes()
            ->where('store_id', $service->store->id)
            ->where('key', $key)
            ->whereExists(fn ($query) => $query->selectRaw(1)
                ->from('link_product')
                ->whereColumn('link_product.link_id', 'links.id')
                ->where('link_product.user_id', Auth::id()))
            ->with('products:id')
            ->first();

        return match (true) {
            (bool) $existingLink?->products->contains('id', $product->id) => [
                self::STATUS_DUPLICATE,
                'Already added to this product',
                $service->store->id,
                $key,
            ],
            (bool) $existingLink => [
                self::STATUS_TRACKED_ELSEWHERE,
                "{$service->store->name} - already tracked on another product",
                $service->store->id,
                $key,
            ],
            default => [
                self::STATUS_OK,
                $service->store->name,
                $service->store->id,
                $key,
            ],
        };
    }

    /**
     * @param  array<array-key, array<string, mixed>>  $items
     */
    private static function summary(array $items): string
    {
        $counts = collect($items)->countBy('status');
        $skipped = $counts->get(self::STATUS_ERROR, 0) + $counts->get(self::STATUS_DUPLICATE, 0);

        return count($items).' link(s): '.(count($items) - $skipped)." will be added, {$skipped} will be skipped (errors and duplicates are outlined).";
    }

    private static function statusIcon(?string $status): Heroicon
    {
        return match ($status) {
            self::STATUS_ERROR             => Heroicon::XCircle,
            self::STATUS_DUPLICATE         => Heroicon::DocumentDuplicate,
            self::STATUS_TRACKED_ELSEWHERE => Heroicon::InformationCircle,
            default                        => Heroicon::CheckCircle,
        };
    }

    private static function statusColor(?string $status): string
    {
        return match ($status) {
            self::STATUS_ERROR             => 'danger',
            self::STATUS_DUPLICATE         => 'warning',
            self::STATUS_TRACKED_ELSEWHERE => 'info',
            default                        => 'success',
        };
    }

    /**
     * @return array<string, string>
     */
    private static function statusOutline(?string $status): array
    {
        return match ($status) {
            self::STATUS_ERROR     => ['style' => 'outline: 2px solid var(--danger-500); outline-offset: 2px;'],
            self::STATUS_DUPLICATE => ['style' => 'outline: 2px solid var(--warning-500); outline-offset: 2px;'],
            default                => [],
        };
    }
}
