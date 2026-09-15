<?php

namespace App\Http\Controllers;

use App\Models\Service;
use App\Models\ServiceCategory;
use Illuminate\Http\Request;

class DashboardController extends Controller
{
    public function index()
    {
        $categories = ServiceCategory::all(['name', 'description', 'icon']);
        return view('dashboard.dashboard', ["categories" => $categories]);
    }

    public function show(ServiceCategory $category)
    {       
        return view('dashboard.categories.category-view', ['services' => $category->services]);
    }
}
