<?php

namespace App\Policies;

use App\Models\Employee;
use App\Models\Reservation;
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

    public function before(User $user )
    {
        return $user->role === 'admin' ? true : null;
    }

    public function create(User $user, Employee $employee) : bool
    {
        return $employee->active && $employee->user_id !== $user->uuid;
    }

    public function view(User $user, Reservation $reservation)
    {
        return $reservation->user_id === $user->uuid || $reservation->employee?->id === $user->uuid;
    }

    public function cancel (User $user, Reservation $reservation)
    {
        return ($reservation->user_id === $user->uuid || $reservation->employee?->id === $user->uuid) && in_array($reservation->satus, ['pending', 'confirmed']) && $reservation->reservation_date->isFuture();
    }

    public function confirm(User $user, Reservation $reservation): bool
    {
        // only the employee being booked can confirm
        return $reservation->status === 'pending'
            && $reservation->employee?->user_id === $user->uuid;
    }
}
