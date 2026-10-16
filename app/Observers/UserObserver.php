<?php

namespace App\Observers;

use App\Enums\RoleEnum;
use App\Http\Controllers\Actions\ResetUserSettingToDefaultAction;
use App\Models\User;
use Illuminate\Support\Facades\Auth;

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
     * Prevent non-admins from changing their own role.
     * This guards against crafted Livewire/HTTP requests that bypass form controls.
     */
    public function saving(User $user): void
    {
        $authUser = Auth::user();

        if ($authUser && $authUser->role !== RoleEnum::Admin && $user->isDirty('role')) {
            $user->role = $user->getOriginal('role') ?? RoleEnum::User;
        }
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
