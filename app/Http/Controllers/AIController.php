<?php

namespace App\Http\Controllers;

use App\Jobs\GenerateAiImage;
use App\Models\AIGeneration;
use Illuminate\Http\Request;

class AIController extends Controller
{
    public function create()
    {
        return view('ai.create');
    }

    public function generate(Request $request)
    {
        $validated = $request->validate([
            'prompt' => 'required|string|min:3|max:1000',
            'style' => 'nullable|string',
            'size' => 'nullable|in:512x512,768x768,1024x1024'
        ]);

        // 1. Create a record to track the AI generation task
        $generation = AIGeneration::create([
            'user_id' => auth()->id(),
            'provider' => 'gemini', // Or your preferred provider
            'model_name' => 'gemini-pro', // Or your specific image model
            'prompt' => $validated['prompt'],
            'parameters' => [
                'style' => $request->input('style'),
                'size' => $request->input('size', '1024x1024')
            ],
            'status' => 'pending' // The job is waiting in the queue
        ]);

        // 2. Dispatch the job to the queue for background processing
        GenerateAiImage::dispatch($generation);

        // 3. Immediately redirect the user with a success message
        return redirect()->route('ai.history')->with('success', 'Image generation started! It will appear here shortly.');
    }

    public function history()
    {
        $generations = AIGeneration::with(['image'])
            ->where('user_id', auth()->id())
            ->latest()
            ->paginate(12);

        return view('ai.history', compact('generations'));
    }
}