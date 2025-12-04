<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Lab;

class LandingController extends Controller
{
    /**
     * Show landing page with lab information
     */
    public function index()
    {
        $labs = Lab::where('status', '!=', 'maintenance')
                   ->orderBy('name')
                   ->get();

        return view('landing', compact('labs'));
    }
}
