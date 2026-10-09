<?php

namespace App\Http\Controllers;

use App\Models\Service;
use App\Models\ServiceCategory;
use Illuminate\Http\Request;

class DashboardController extends Controller
{
    public function index()
    {
        
        return view('dashboard.dashboard');
    }
    public function profile()
    {
        
        return view('dashboard.user-profile');
    }



}
