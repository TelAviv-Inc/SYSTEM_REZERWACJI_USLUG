<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ServiceCategory extends Model
{
    /** @use HasFactory<\Database\Factories\ServiceCategoryFactory> */
    use HasFactory;

    protected $fillable = [
        'name',
        'description',
        'icon'
    ];

    /**
     * Get the services for the category.
     */
    public function services()
    {
        return $this->hasMany(Service::class, 'category_id');
    }
}
