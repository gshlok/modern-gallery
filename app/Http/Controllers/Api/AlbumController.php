<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Album;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;

class AlbumController extends Controller
{
    /**
     * Display a listing of albums.
     */
    public function index(Request $request): JsonResponse
    {
        $query = Album::visibleTo(auth()->user())
            ->with(['user'])
            ->withCount(['images' => function ($query) {
                $query->visibleTo(auth()->user());
            }]);

        // Apply filters
        if ($request->filled('search')) {
            $query->where(function ($q) use ($request) {
                $q->where('title', 'LIKE', "%{$request->search}%")
                    ->orWhere('description', 'LIKE', "%{$request->search}%");
            });
        }

        $albums = $query->paginate(24);

        return response()->json($albums);
    }

    /**
     * Store a newly created album.
     */
    public function store(Request $request): JsonResponse
    {
        // Implementation would go here
        return response()->json(['message' => 'Not implemented'], 501);
    }

    /**
     * Display the specified album.
     */
    public function show(Album $album): JsonResponse
    {
        return response()->json($album->load(['user', 'images']));
    }

    /**
     * Update the specified album.
     */
    public function update(Request $request, Album $album): JsonResponse
    {
        // Implementation would go here
        return response()->json(['message' => 'Not implemented'], 501);
    }

    /**
     * Remove the specified album.
     */
    public function destroy(Album $album): JsonResponse
    {
        // Implementation would go here
        return response()->json(['message' => 'Not implemented'], 501);
    }

    /**
     * Add images to album.
     */
    public function addImages(Request $request, Album $album): JsonResponse
    {
        // Implementation would go here
        return response()->json(['message' => 'Not implemented'], 501);
    }
}
