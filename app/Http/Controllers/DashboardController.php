<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Inertia\Inertia;

class DashboardController extends Controller
{
    /**
     * Display the dashboard.
     */
    public function index()
    {
        return Inertia::render('Dashboard', [
            'user' => auth()->user(),
        ]);
    }
}
