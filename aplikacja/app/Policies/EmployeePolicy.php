<?php

namespace App\Policies;

use App\Models\Employee;
use App\Models\User;

class EmployeePolicy
{
    // details page in the admin panel: services, upcoming reservations, weekly bookings
    public function view(User $user, Employee $employee): bool
    {
        return $user->role === 'admin';
    }
}
