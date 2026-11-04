<?php

namespace App\Models;

use Database\Factories\EmployeeFactory;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Carbon;

class Employee extends Model
{
    /** @use HasFactory<EmployeeFactory> */
    use HasFactory, HasUuids;

    protected $primaryKey = 'uuid';   // if uuid IS your PK column

    public $incrementing = false;

    protected $keyType = 'string';

    protected $fillable = [
        'uuid',
        'user_id',
        'description',
        'active',
    ];

    /**
     * Get the user that owns the employee.
     */
    public function user()
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    /**
     * Get the services for the employee.
     */
    public function services()
    {
        return $this->belongsToMany(Service::class, 'employee_services', 'employee_id', 'service_id');
    }

    /**
     * Get the availability records for the employee.
     */
    public function availability()
    {
        return $this->hasMany(EmployeeAvailability::class, 'employee_id')
            ->orderBy('day_of_week')
            ->orderBy('start_time');
    }

    public function reservations()
    {
        return $this->hasMany(Reservation::class, 'employee_id');
    }

    /**
     * Employees clients can book: an active profile and an account that can log in.
     */
    public function scopeBookable($query)
    {
        return $query->where('employees.active', 1)
            ->whereHas('user', fn ($q) => $q->where('active', true));
    }

    /**
     * Upcoming visits that no single weekly working range covers any more. They stay
     * booked, someone has to move them.
     */
    public function upcomingOutsideHoursCount(): int
    {
        $ranges = $this->availability()
            ->whereNotNull('day_of_week')
            ->toBase()
            ->get(['day_of_week', 'start_time', 'end_time']);
        $time = fn ($value) => Carbon::parse($value)->format('H:i:s');

        return $this->reservations()
            ->upcoming()
            ->toBase()
            ->get(['reservation_date', 'start_time', 'end_time'])
            ->reject(function ($reservation) use ($ranges, $time) {
                $day = Carbon::parse($reservation->reservation_date)->isoWeekday();

                return $ranges->contains(fn ($range) => (int) $range->day_of_week === $day
                    && $time($range->start_time) <= $time($reservation->start_time)
                    && $time($range->end_time) >= $time($reservation->end_time));
            })
            ->count();
    }
}
