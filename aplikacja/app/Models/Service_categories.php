<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Service_categories extends Model
{
    /** @use HasFactory<\Database\Factories\ServiceCategoriesFactory> */
    use HasFactory;

    protected $fillable = [
        'name',
        'description'
    ];

    
}
