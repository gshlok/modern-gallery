<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Image;
use App\Models\Album;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;

class SearchController extends Controller
{
    /**
     * Search images and albums.
     */
    public function search(Request $request): JsonResponse
    {
        $query = $request->get('q', '');
        $type = $request->get('type', 'all'); // all, images, albums

        $results = [];

        if ($query) {
            if ($type === 'all' || $type === 'images') {
                $images = Image::visibleTo(auth()->user())
                    ->search($query)
                    ->with(['user', 'tags'])
                    ->limit(20)
                    ->get();

                $results['images'] = $images;
            }

            if ($type === 'all' || $type === 'albums') {
                $albums = Album::visibleTo(auth()->user())
                    ->where(function ($q) use ($query) {
                        $q->where('title', 'LIKE', "%{$query}%")
                            ->orWhere('description', 'LIKE', "%{$query}%");
                    })
                    ->with(['user'])
                    ->withCount(['images' => function ($q) {
                        $q->visibleTo(auth()->user());
                    }])
                    ->limit(10)
                    ->get();

                $results['albums'] = $albums;
            }
        }

        return response()->json([
            'query' => $query,
            'type' => $type,
            'results' => $results,
        ]);
    }

    /**
     * Advanced search.
     */
    public function advanced(Request $request): JsonResponse
    {
        // Implementation would go here
        return response()->json(['message' => 'Not implemented'], 501);
    }
}
