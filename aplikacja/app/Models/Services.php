<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Services extends Model
{
    /** @use HasFactory<\Database\Factories\ServicesFactory> */
    use HasFactory;

    protected $fillable = [
        'category_id',
        'name',
        'description',
        'duration',
        'price',
        'active'
    ];

    /**
     * Get the category that owns the service.
     */
    public function category()
    {
        return $this->belongsTo(Service_category::class, 'category_id');
    }

    /**
     * Get the employees that provide this service.
     */
    public function employees()
    {
        return $this->belongsToMany(Employee::class, 'employee_services', 'service_id', 'employee_id');
    }

    /**
     * Get the reservations for this service.
     */
    public function reservations()
    {
        return $this->hasMany(Reservation::class, 'service_id');
    }
}
