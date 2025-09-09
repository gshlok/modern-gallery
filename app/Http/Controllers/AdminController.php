<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Inertia\Inertia;

class AdminController extends Controller
{
    /**
     * Display the admin dashboard.
     */
    public function dashboard()
    {
        return Inertia::render('Admin/Dashboard');
    }

    /**
     * Display users management.
     */
    public function users()
    {
        return Inertia::render('Admin/Users');
    }

    /**
     * Display images management.
     */
    public function images()
    {
        return Inertia::render('Admin/Images');
    }

    /**
     * Display comments management.
     */
    public function comments()
    {
        return Inertia::render('Admin/Comments');
    }

    /**
     * Display analytics.
     */
    public function analytics()
    {
        return Inertia::render('Admin/Analytics');
    }
}
