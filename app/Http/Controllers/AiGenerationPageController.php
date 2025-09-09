<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Inertia\Inertia;

/**
 * AI Generation Page Controller
 * 
 * Handles the web interface for AI image generation.
 */
class AiGenerationPageController extends Controller
{
    /**
     * Display the AI generation page
     * 
     * @param Request $request
     * @return \Inertia\Response
     */
    public function index(Request $request)
    {
        // Get available AI providers from configuration
        $providers = array_keys(config('ai.providers', ['openai', 'stability', 'midjourney']));

        return Inertia::render('AiGeneration', [
            'user' => $request->user(),
            'providers' => $providers,
        ]);
    }
}
