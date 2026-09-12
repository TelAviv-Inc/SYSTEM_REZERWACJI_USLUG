<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class PreviewController extends Controller
{

    public function loginPage()
    {
        return view('login-page');
    }

}
