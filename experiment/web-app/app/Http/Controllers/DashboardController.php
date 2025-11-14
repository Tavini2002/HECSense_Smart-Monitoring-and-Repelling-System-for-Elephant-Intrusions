<?php

namespace App\Http\Controllers;

use App\Models\Organ;
use App\Models\MobileUser;
use App\Models\OrganRequest;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;


class DashboardController extends Controller
{
    public function showDashboard()
    {
        return view('dashboard');
    }

    
}
