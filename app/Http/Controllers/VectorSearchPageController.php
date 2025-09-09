<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Inertia\Inertia;

/**
 * Vector Search Page Controller
 * 
 * Handles the web interface for vector-based image search.
 */
class VectorSearchPageController extends Controller
{
    /**
     * Display the vector search page
     * 
     * @param Request $request
     * @return \Inertia\Response
     */
    public function index(Request $request)
    {
        return Inertia::render('VectorSearch', [
            'user' => $request->user(),
        ]);
    }
}
