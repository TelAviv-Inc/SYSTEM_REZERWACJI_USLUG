<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class EmployeeAvailability extends Model
{
    /** @use HasFactory<\Database\Factories\EmployeeAvailabilityFactory> */
    use HasFactory;

    protected $table = 'employee_availability';
    protected $primaryKey = 'uuid';   // if uuid IS your PK column
    public $incrementing = false;
    protected $keyType = 'string';
    protected $fillable = [
        'employee_id',
        'day_of_week',
        'specific_date',
        'start_time',
        'end_time',
    ];

    protected $casts = [
        'day_of_week' => 'integer',
        'specific_date' => 'date',
        'start_time' => 'time',
        'end_time' => 'time',
    ];

    public function employee()
    {
        return $this->belongsTo(Employee::class, 'employee_id', 'id');
    }
}
