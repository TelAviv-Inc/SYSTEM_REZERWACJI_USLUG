<?php

namespace App\Policies;

use App\Models\Employee;
use App\Models\User;

class ReservationPolicy
{
    /**
     * Create a new policy instance.
     */
    public function __construct()
    {
        //
    }

    public function create(User $user, Employee $employee) : bool
    {
        return $employee->user_id !== $user->uuid;
    }
}
