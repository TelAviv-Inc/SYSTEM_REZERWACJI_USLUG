<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Employee extends Model
{
    /** @use HasFactory<\Database\Factories\EmployeeFactory> */
    use HasFactory;

    protected $fillable = [
        'user_id',
        'description',
        'active'
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
        return $this->hasMany(Employee_availability::class, 'employee_id');
    }
}
