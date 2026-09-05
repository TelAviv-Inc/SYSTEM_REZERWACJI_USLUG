<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Service_category extends Model
{
    /** @use HasFactory<\Database\Factories\ServiceCategorysFactory> */
    use HasFactory;

    protected $fillable = [
        'name',
        'description'
    ];

    /**
     * Get the services for the category.
     */
    public function services()
    {
        return $this->hasMany(Service::class, 'category_id');
    }
}
