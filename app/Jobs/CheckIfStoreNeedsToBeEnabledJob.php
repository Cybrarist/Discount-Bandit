<?php

namespace App\Jobs;

use App\Enums\StoreStatusEnum;
use App\Models\Store;
use Illuminate\Contracts\Queue\ShouldBeUniqueUntilProcessing;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;
use Illuminate\Queue\Middleware\WithoutOverlapping;
use Illuminate\Support\Facades\Context;
use Illuminate\Support\Facades\Log;

class CheckIfStoreNeedsToBeEnabledJob implements ShouldBeUniqueUntilProcessing, ShouldQueue
{
    use Queueable;

    public int $uniqueFor = 600;

    public function uniqueId(): string
    {
        return "{$this->storeId}";
    }

    public function middleware(): array
    {
        return [
            // prevent concurrent processing for the same key
            new WithoutOverlapping($this->uniqueId())
                ->expireAfter($this->uniqueFor),
        ];
    }

    /**
     * Create a new job instance.
     */
    public function __construct(
        private int $storeId
    ) {
        //
    }

    /**
     * Execute the job.
     */
    public function handle(): void
    {
        $currentStore = Store::findOrFail($this->storeId);

        $linksForStore = $currentStore->links()->withoutGlobalScopes()->count();

        if ($linksForStore > 0 && $currentStore->status == StoreStatusEnum::Disabled) {
            $currentStore->update([
                'status' => StoreStatusEnum::Active,
            ]);

        } elseif (! $linksForStore && $currentStore->status == StoreStatusEnum::Active) {
            $currentStore->update([
                'status' => StoreStatusEnum::Disabled,
            ]);
        }

    }
}
