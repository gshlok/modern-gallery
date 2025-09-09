<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\AiGeneration;
use App\Models\Image;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Auth;

/**
 * AI Generation Controller
 * 
 * Placeholder endpoints for AI image generation integration.
 * Ready for Stable Diffusion, DALL-E, or other AI providers.
 */
class AiGenerationController extends Controller
{
    /**
     * Generate image from text prompt
     */
    public function generate(Request $request): JsonResponse
    {
        $request->validate([
            'prompt' => 'required|string|max:1000',
            'negative_prompt' => 'nullable|string|max:500',
            'width' => 'nullable|integer|min:256|max:2048',
            'height' => 'nullable|integer|min:256|max:2048',
            'steps' => 'nullable|integer|min:10|max:100',
            'guidance_scale' => 'nullable|numeric|min:1|max:20',
            'seed' => 'nullable|integer',
            'provider' => 'nullable|string|in:openai,stability,midjourney',
            'model' => 'nullable|string',
        ]);

        // Check if AI generation is enabled
        if (!config('ai.generation.enabled', false)) {
            return response()->json([
                'error' => 'AI generation is not enabled on this platform.',
                'message' => 'Contact the administrator to enable AI image generation features.',
            ], 503);
        }

        // Check user permissions
        if (!Auth::user()->hasPermissionTo('generate ai images')) {
            return response()->json([
                'error' => 'Permission denied.',
                'message' => 'You do not have permission to generate AI images.',
            ], 403);
        }

        // Create generation record
        $generation = AiGeneration::create([
            'user_id' => Auth::id(),
            'prompt' => $request->prompt,
            'negative_prompt' => $request->negative_prompt,
            'provider' => $request->provider ?? config('ai.generation.default_provider', 'openai'),
            'model' => $request->model ?? config('ai.generation.default_model', 'dall-e-3'),
            'parameters' => [
                'width' => $request->width ?? 1024,
                'height' => $request->height ?? 1024,
                'steps' => $request->steps ?? 30,
                'guidance_scale' => $request->guidance_scale ?? 7.5,
                'seed' => $request->seed ?? rand(1, 1000000),
            ],
            'status' => 'pending',
        ]);

        // TODO: Dispatch job to generate image
        // GenerateAiImageJob::dispatch($generation);

        return response()->json([
            'generation_id' => $generation->id,
            'status' => 'pending',
            'message' => 'Image generation started. Check back in a few moments.',
            'estimated_completion' => now()->addMinutes(2),
        ]);
    }

    /**
     * List user's AI generations
     */
    public function index(Request $request): JsonResponse
    {
        $generations = Auth::user()
            ->aiGenerations()
            ->with('image')
            ->orderBy('created_at', 'desc')
            ->paginate(20);

        return response()->json([
            'data' => $generations->map(function ($generation) {
                return [
                    'id' => $generation->id,
                    'prompt' => $generation->prompt,
                    'negative_prompt' => $generation->negative_prompt,
                    'provider' => $generation->provider,
                    'model' => $generation->model,
                    'parameters' => $generation->parameters,
                    'status' => $generation->status,
                    'error_message' => $generation->error_message,
                    'processing_time' => $generation->processing_time,
                    'cost' => $generation->cost,
                    'created_at' => $generation->created_at,
                    'image' => $generation->image ? [
                        'id' => $generation->image->id,
                        'title' => $generation->image->title,
                        'slug' => $generation->image->slug,
                        'thumbnail_url' => $generation->image->thumbnail_url,
                        'url' => $generation->image->url,
                    ] : null,
                ];
            }),
            'meta' => [
                'current_page' => $generations->currentPage(),
                'total' => $generations->total(),
                'per_page' => $generations->perPage(),
            ],
        ]);
    }

    /**
     * Get specific generation details
     */
    public function show(AiGeneration $generation): JsonResponse
    {
        // Check ownership
        if ($generation->user_id !== Auth::id() && !Auth::user()->canModerate()) {
            return response()->json(['error' => 'Generation not found.'], 404);
        }

        $generation->load('image', 'user');

        return response()->json([
            'id' => $generation->id,
            'prompt' => $generation->prompt,
            'negative_prompt' => $generation->negative_prompt,
            'provider' => $generation->provider,
            'model' => $generation->model,
            'parameters' => $generation->parameters,
            'status' => $generation->status,
            'error_message' => $generation->error_message,
            'processing_time' => $generation->processing_time,
            'cost' => $generation->cost,
            'response_data' => $generation->response_data,
            'created_at' => $generation->created_at,
            'updated_at' => $generation->updated_at,
            'user' => [
                'id' => $generation->user->id,
                'name' => $generation->user->name,
            ],
            'image' => $generation->image ? [
                'id' => $generation->image->id,
                'title' => $generation->image->title,
                'slug' => $generation->image->slug,
                'thumbnail_url' => $generation->image->thumbnail_url,
                'url' => $generation->image->url,
                'width' => $generation->image->width,
                'height' => $generation->image->height,
                'created_at' => $generation->image->created_at,
            ] : null,
        ]);
    }

    /**
     * Delete a generation record
     */
    public function destroy(AiGeneration $generation): JsonResponse
    {
        // Check ownership
        if ($generation->user_id !== Auth::id() && !Auth::user()->canModerate()) {
            return response()->json(['error' => 'Generation not found.'], 404);
        }

        // If there's an associated image, optionally delete it too
        if ($generation->image) {
            $generation->image->delete();
        }

        $generation->delete();

        return response()->json([
            'message' => 'Generation deleted successfully.',
        ]);
    }

    /**
     * Get available AI providers and models
     */
    public function providers(): JsonResponse
    {
        $providers = [
            'openai' => [
                'name' => 'OpenAI DALL-E',
                'models' => [
                    'dall-e-3' => [
                        'name' => 'DALL-E 3',
                        'description' => 'Latest and most capable image generation model',
                        'max_resolution' => '1024x1024',
                        'cost_per_image' => 0.04,
                    ],
                    'dall-e-2' => [
                        'name' => 'DALL-E 2',
                        'description' => 'Previous generation model, faster and cheaper',
                        'max_resolution' => '1024x1024',
                        'cost_per_image' => 0.02,
                    ],
                ],
                'enabled' => config('ai.providers.openai.enabled', false),
            ],
            'stability' => [
                'name' => 'Stability AI',
                'models' => [
                    'stable-diffusion-xl' => [
                        'name' => 'Stable Diffusion XL',
                        'description' => 'High-quality open-source image generation',
                        'max_resolution' => '1024x1024',
                        'cost_per_image' => 0.01,
                    ],
                    'stable-diffusion-2-1' => [
                        'name' => 'Stable Diffusion 2.1',
                        'description' => 'Previous version, good balance of quality and speed',
                        'max_resolution' => '768x768',
                        'cost_per_image' => 0.008,
                    ],
                ],
                'enabled' => config('ai.providers.stability.enabled', false),
            ],
            'midjourney' => [
                'name' => 'Midjourney',
                'models' => [
                    'v6' => [
                        'name' => 'Midjourney v6',
                        'description' => 'Latest Midjourney model with enhanced photorealism',
                        'max_resolution' => '1792x1024',
                        'cost_per_image' => 0.05,
                    ],
                    'v5-2' => [
                        'name' => 'Midjourney v5.2',
                        'description' => 'Previous version with artistic focus',
                        'max_resolution' => '1024x1024',
                        'cost_per_image' => 0.04,
                    ],
                ],
                'enabled' => config('ai.providers.midjourney.enabled', false),
            ],
        ];

        return response()->json([
            'providers' => $providers,
            'default_provider' => config('ai.generation.default_provider', 'openai'),
            'enabled' => config('ai.generation.enabled', false),
        ]);
    }
}
