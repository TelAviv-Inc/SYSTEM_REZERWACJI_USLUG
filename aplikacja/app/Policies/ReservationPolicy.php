<?php

namespace App\Policies;

use App\Models\Employee;
use App\Models\Reservation;
use App\Models\User;

// Admins may act on any reservation, but the status and date rules apply to them too -
// a cancelled or completed visit can't be moved or reopened by anyone.
class ReservationPolicy
{
    // reservations tab in the admin panel
    public function viewAny(User $user): bool
    {
        return $this->isAdmin($user);
    }

    // an employee without an active account can't log in, so they can't be booked either
    public function create(User $user, Employee $employee): bool
    {
        return $employee->active && $employee->user?->active && $employee->user_id !== $user->uuid;
    }

    public function view(User $user, Reservation $reservation): bool
    {
        return $this->isAdmin($user) || $this->isClient($user, $reservation) || $this->isEmployee($user, $reservation);
    }

    // clients and employees up to the day before; an admin also after the start, e.g. for a no-show
    public function cancel(User $user, Reservation $reservation): bool
    {
        if (! $reservation->isActive()) {
            return false;
        }

        return $this->isAdmin($user)
            || (($this->isClient($user, $reservation) || $this->isEmployee($user, $reservation)) && $reservation->reservation_date->isFuture());
    }

    // the client who booked or an admin can move the visit; same time window as cancel
    public function update(User $user, Reservation $reservation): bool
    {
        return ($this->isAdmin($user) || $this->isClient($user, $reservation))
            && $reservation->employee?->active
            && $reservation->isActive()
            && $reservation->reservation_date->isFuture();
    }

    // the employee being booked or an admin, before the visit starts
    public function confirm(User $user, Reservation $reservation): bool
    {
        return ($this->isAdmin($user) || $this->isEmployee($user, $reservation))
            && $reservation->status === 'pending'
            && ! $reservation->hasStarted();
    }

    // once the visit has started; a pending one too, in case nobody confirmed it in time
    public function complete(User $user, Reservation $reservation): bool
    {
        return ($this->isAdmin($user) || $this->isEmployee($user, $reservation))
            && $reservation->isActive()
            && $reservation->hasStarted();
    }

    protected function isAdmin(User $user): bool
    {
        return $user->role === 'admin';
    }

    protected function isClient(User $user, Reservation $reservation): bool
    {
        return $reservation->user_id === $user->uuid;
    }

    protected function isEmployee(User $user, Reservation $reservation): bool
    {
        return $reservation->employee?->user_id === $user->uuid;
    }
}
