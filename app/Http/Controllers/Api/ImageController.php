<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Image;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;

class ImageController extends Controller
{
    /**
     * Display a listing of images.
     */
    public function index(Request $request): JsonResponse
    {
        $query = Image::visibleTo(auth()->user())
            ->with(['user', 'tags', 'album']);

        // Apply filters
        if ($request->filled('search')) {
            $query->search($request->search);
        }

        if ($request->filled('tag')) {
            $query->whereHas('tags', function ($q) use ($request) {
                $q->where('slug', $request->tag);
            });
        }

        // Apply sorting
        $sortBy = $request->get('sort', 'recent');
        switch ($sortBy) {
            case 'popular':
                $query->orderBy('view_count', 'desc');
                break;
            case 'liked':
                $query->orderBy('like_count', 'desc');
                break;
            case 'oldest':
                $query->orderBy('created_at', 'asc');
                break;
            default:
                $query->orderBy('created_at', 'desc');
        }

        $images = $query->paginate(24);

        return response()->json($images);
    }

    /**
     * Store a newly created image.
     */
    public function store(Request $request): JsonResponse
    {
        // Implementation would go here
        return response()->json(['message' => 'Not implemented'], 501);
    }

    /**
     * Display the specified image.
     */
    public function show(Image $image): JsonResponse
    {
        return response()->json($image->load(['user', 'tags', 'album']));
    }

    /**
     * Update the specified image.
     */
    public function update(Request $request, Image $image): JsonResponse
    {
        // Implementation would go here
        return response()->json(['message' => 'Not implemented'], 501);
    }

    /**
     * Remove the specified image.
     */
    public function destroy(Image $image): JsonResponse
    {
        // Implementation would go here
        return response()->json(['message' => 'Not implemented'], 501);
    }

    /**
     * Like an image.
     */
    public function like(Image $image): JsonResponse
    {
        // Implementation would go here
        return response()->json(['message' => 'Not implemented'], 501);
    }

    /**
     * Unlike an image.
     */
    public function unlike(Image $image): JsonResponse
    {
        // Implementation would go here
        return response()->json(['message' => 'Not implemented'], 501);
    }
}
