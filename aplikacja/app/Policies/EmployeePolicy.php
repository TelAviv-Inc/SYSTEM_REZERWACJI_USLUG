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

    // services and working hours, managed from the same details modal
    public function update(User $user, Employee $employee): bool
    {
        return $user->role === 'admin';
    }

    // booked visits stay, the employee just disappears from the booking modal
    public function deactivate(User $user, Employee $employee): bool
    {
        return $user->role === 'admin' && $employee->active;
    }

    // only someone who still has the employee role and an account that can log in
    public function activate(User $user, Employee $employee): bool
    {
        return $user->role === 'admin'
            && ! $employee->active
            && $employee->user?->role === 'employee'
            && $employee->user->active;
    }
}
