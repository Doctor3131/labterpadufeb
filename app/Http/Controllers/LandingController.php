<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Lab;

class LandingController extends Controller
{

    /**
     * Show landing page
     * Schedule data is loaded via AJAX from /schedules/week endpoint
     */
    public function index()
    {
        // Labs for any server-side needs (passed but schedule is AJAX-loaded)
        $labs = Lab::orderBy('name')->get(['id', 'name']);

        return view('landing', compact('labs'));
    }
}
