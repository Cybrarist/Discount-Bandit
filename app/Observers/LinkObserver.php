<?php

namespace App\Observers;

use App\Enums\RoleEnum;
use App\Helpers\LinkHelper;
use App\Models\Link;
use Filament\Notifications\Notification;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\ValidationException;

class LinkObserver
{
    public function saving(Link $link)
    {
        if (
            Auth::check() &&
            Auth::user()->role == RoleEnum::Admin->value &&
            Auth::user()->other_settings['max_links'] &&
            Auth::user()->links()->count() >= Auth::user()->other_settings['max_links']
        ) {

            Notification::make()
                ->title('You have reached the maximum number of links')
                ->body('Please delete some links before adding a new one, or speak to admin')
                ->danger()
                ->send();

            throw ValidationException::withMessages([
                'max_links' => 'You have reached the maximum number of links. Please delete some before adding a new one.',
            ]);

        }

        [$link_base, $link_params] = LinkHelper::prepare_base_key_and_params($link);
        $link->key = $link_base."?".$link_params;
    }

    /**
     * Handle the Link "created" event.
     */
    public function created(Link $link): void
    {
        //
    }

    /**
     * Handle the Link "updated" event.
     */
    public function updated(Link $link): void
    {
        //
    }

    /**
     * Handle the Link "deleted" event.
     */
    public function deleted(Link $link): void
    {
        //
    }

    /**
     * Handle the Link "restored" event.
     */
    public function restored(Link $link): void
    {
        //
    }

    /**
     * Handle the Link "force deleted" event.
     */
    public function forceDeleted(Link $link): void
    {
        //
    }
}
