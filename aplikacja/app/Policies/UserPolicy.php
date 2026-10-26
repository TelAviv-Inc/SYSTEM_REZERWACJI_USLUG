<?php

namespace App\Policies;

use App\Models\User;

class UserPolicy
{
    /**
     * Create a new policy instance.
     */
    public function __construct()
    {
        //
    }

    public function update(User $actor, User $target): bool
    {
        return $actor->role === "admin";
    }

    public function deactivateUser(User $actor, User $target): bool
    {
        return $actor->role === "admin" && ! $actor->is($target) && $target->active;
    }

    public function changeRole(User $actor, User $target): bool
    {
        return $actor->role === 'admin' && ! $actor->is($target);
    }
}
