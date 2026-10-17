<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Reservation extends Model
{
    /** @use HasFactory<\Database\Factories\ReservationFactory> */
    use HasFactory;

    protected $primaryKey = 'uuid';   // if uuid IS your PK column
    public $incrementing = false;
    protected $keyType = 'string';
    
    protected $fillable = [
        'uuid',
        'user_id',
        'employee_id', 
        'service_id',
        'reservation_date',
        'start_time',
        'end_time',
        'status',
        'comment'
    ];

    protected $casts = [
        'reservation_date' => 'date',
        'start_time' => 'datetime:H:i',
        'end_time' => 'datetime:H:i',
    ];

    /**
     * Get the user that owns the reservation.
     */
    public function user()
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    /**
     * Get the employee that owns the reservation.
     */
    public function employee()
    {
        return $this->belongsTo(Employee::class, 'employee_id');
    }

    /**
     * Get the service that is reserved.
     */
    public function service()
    {
        return $this->belongsTo(Service::class, 'service_id');
    }

    /**
     * Scope a query to only include pending reservations.
     */
    public function scopePending($query)
    {
        return $query->where('status', 'pending');
    }

    /**
    * Scope a query to only include active (pending or confirmed) reservations.
    */
    public function scopeActive($query)
    {
        return $query->whereIn('status', ['pending', 'confirmed']);
    }
    /**
     * Scope a query to only include confirmed reservations.
     */
    public function scopeConfirmed($query)
    {
        return $query->where('status', 'confirmed');
    }

    /**
     * Scope a query to only include completed reservations.
     */
    public function scopeCompleted($query)
    {
        return $query->where('status', 'completed');
    }

    /**
     * Scope a query to only include cancelled reservations.
     */
    public function scopeCancelled($query)
    {
        return $query->where('status', 'cancelled');
    }

    /**
     * Get the reservation's status label.
     */
    public function getStatusLabelAttribute()
    {
        $statuses = [
            'pending' => 'Oczekująca',
            'confirmed' => 'Potwierdzona',
            'completed' => 'Zakończona',
            'cancelled' => 'Anulowana'
        ];

        return $statuses[$this->status] ?? $this->status;
    }

    /**
     * Get the reservation's full date and time.
     */
    public function getFullDateTimeAttribute()
    {
        return $this->reservation_date->format('d.m.Y') . ' ' . 
               $this->start_time->format('H:i') . '-' . 
               $this->end_time->format('H:i');
    }

    /**
     * Scope a query to only include reservations that haven't started yet.
     */
    public function scopeUpcoming($query)
    {
        return $query->whereIn('status', ['pending', 'confirmed'])
            ->where(function ($q) {
                $q->whereDate('reservation_date', '>', today())
                    ->orWhere(function ($q) {
                        $q->whereDate('reservation_date', today())
                            ->whereTime('start_time', '>', now()->format('H:i:s'));
                    });
            });
    }

    /**
     * Scope a query to only include reservations that have already started.
     */
    public function scopePast($query)
    {
        return $query->where(function ($q) {
            $q->whereDate('reservation_date', '<', today())
                ->orWhere(function ($q) {
                    $q->whereDate('reservation_date', today())
                        ->whereTime('start_time', '<=', now()->format('H:i:s'));
                });
        });
    }

    /**
     * $exceptUuid skips one reservation, so a rescheduled visit doesn't collide with its own old slot.
     */
    public function scopeOverlapping($query, string $employeeId, string $date, string $start, string $end, ?string $exceptUuid = null)
    {
        return $query->where('employee_id', $employeeId)
            ->whereDate('reservation_date', $date)
            ->where('status', '!=', 'cancelled')
            ->where('start_time', '<', $end)
            ->where('end_time', '>', $start)
            ->when($exceptUuid, fn($q) => $q->whereKeyNot($exceptUuid));
    }
}
