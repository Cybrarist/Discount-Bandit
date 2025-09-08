<?php

namespace App\Observers;

use App\Http\Controllers\Actions\ResetUserSettingToDefaultAction;
use App\Models\User;

class UserObserver
{
    /**
     * Handle the User "created" event.
     */
    public function created(User $user): void
    {
        new ResetUserSettingToDefaultAction()->__invoke($user);
    }

    /**
     * Handle the User "updated" event.
     */
    public function updated(User $user): void {}

    /**
     * Handle the User "deleted" event.
     */
    public function deleted(User $user): void
    {
        //
    }

    /**
     * Handle the User "restored" event.
     */
    public function restored(User $user): void
    {
        //
    }

    /**
     * Handle the User "force deleted" event.
     */
    public function forceDeleted(User $user): void
    {
        //
    }
}
