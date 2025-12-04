<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Lab;

class DashboardController extends Controller
{
    /**
     * Show dashboard based on user role
     */
    public function index()
    {
        $user = auth()->user();
        $labs = Lab::orderBy('name')->get();

        if ($user && $user->isAdmin()) {
            return view('dashboard.admin', compact('labs'));
        }

        return view('dashboard.mahasiswa', compact('labs'));
    }
}
