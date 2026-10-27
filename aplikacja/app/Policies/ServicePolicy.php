<?php

namespace App\Policies;

use App\Models\Service;
use App\Models\User;

class ServicePolicy
{
    // services tab in the admin panel
    public function viewAny(User $user): bool
    {
        return $user->role === 'admin';
    }

    public function create(User $user): bool
    {
        return $user->role === 'admin';
    }

    public function update(User $user, Service $service): bool
    {
        return $user->role === 'admin';
    }

    // reservations keep a foreign key to the service, a booked one can only be deactivated
    public function delete(User $user, Service $service): bool
    {
        return $user->role === 'admin' && ! $service->reservations()->exists();
    }
}
