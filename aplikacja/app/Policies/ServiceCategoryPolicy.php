<?php

namespace App\Policies;

use App\Models\ServiceCategory;
use App\Models\User;

class ServiceCategoryPolicy
{
    // categories tab in the admin panel
    public function viewAny(User $user): bool
    {
        return $user->role === 'admin';
    }

    public function create(User $user): bool
    {
        return $user->role === 'admin';
    }

    public function update(User $user, ServiceCategory $category): bool
    {
        return $user->role === 'admin';
    }

    // deleting a category would cascade to its services, so it has to be empty first
    public function delete(User $user, ServiceCategory $category): bool
    {
        return $user->role === 'admin' && ! $category->services()->exists();
    }
}
