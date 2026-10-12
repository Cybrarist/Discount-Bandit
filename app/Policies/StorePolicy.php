<?php

namespace App\Policies;

use App\Enums\RoleEnum;
use App\Models\Store;
use App\Models\User;

class StorePolicy
{
    public function before(User $user, string $ability): ?bool
    {
        if ($user->role === RoleEnum::Admin) {
            return true;
        }

        return null;
    }

    public function viewAny(User $user): bool
    {
        return true;
    }

    public function view(User $user, Store $store): bool
    {
        return true;
    }

    public function create(User $user): bool
    {
        return false;
    }

    public function update(User $user, Store $store): bool
    {
        return false;
    }

    public function delete(User $user, Store $store): bool
    {
        return false;
    }

    public function restore(User $user, Store $store): bool
    {
        return false;
    }

    public function forceDelete(User $user, Store $store): bool
    {
        return false;
    }
}
