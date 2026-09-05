<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Employee_service extends Model
{
    /** @use HasFactory<\Database\Factories\EmployeeServiceFactory> */
    use HasFactory;

    protected $table = 'employee_services';

    protected $fillable = [
        'employee_id',
        'service_id'
    ];

    /**
     * Get the employee that owns the service.
     */
    public function employee()
    {
        return $this->belongsTo(Employee::class, 'employee_id');
    }

    /**
     * Get the service that owns the employee.
     */
    public function service()
    {
        return $this->belongsTo(Service::class, 'service_id');
    }
}
