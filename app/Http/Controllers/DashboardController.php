<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class DashboardController extends Controller
{
    /**
     * Show dashboard based on user role
     */
    public function index()
    {
        // All authenticated users go to admin dashboard
        return redirect()->route('admin.dashboard');
    }
}
