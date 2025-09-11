<?php

namespace App\Http\Controllers;

use App\Models\AIGeneration;
use App\Services\AIImageService;
use Illuminate\Http\Request;

class AIController extends Controller
{
    public function __construct(protected AIImageService $aiService) {}

    public function create()
    {
        return view('ai.create');
    }

    public function generate(Request $request)
    {
        $request->validate([
            'prompt' => 'required|string|min:3|max:500',
            'style' => 'nullable|string',
            'size' => 'nullable|in:512x512,768x768,1024x1024'
        ]);

        $generation = $this->aiService->generateImage(
            $request->input('prompt'),
            [
                'style' => $request->input('style'),
                'size' => $request->input('size', '512x512')
            ]
        );

        if ($generation->status === 'completed') {
            return redirect()->route('gallery.show', $generation->image->uuid)
                ->with('success', 'Image generated successfully!');
        }

        return redirect()->back()->with('error', 'Generation failed: ' . $generation->error_message);
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
