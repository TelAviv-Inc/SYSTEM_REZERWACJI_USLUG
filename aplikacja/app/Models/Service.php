<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;

class Service extends Model
{
    /** @use HasFactory<\Database\Factories\ServicesFactory> */
    use HasFactory;
    protected $primaryKey = 'uuid';   // if uuid IS your PK column
    public $incrementing = false;
    protected $keyType = 'string';
    protected $fillable = [
        'category_id',
        'name',
        'slug',
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
        return $this->belongsTo(ServiceCategory::class, 'category_id');
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

    protected static function boot()
    {
        parent::boot();
        static::creating(function ($service){
            $service->slug = Str::slug($service->name);
        });
    }
    public function getRouteKeyName()
    {
        return 'slug';
    }
}
