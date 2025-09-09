<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Image;
use App\Models\Album;
use App\Models\Tag;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Auth;

/**
 * Search Controller
 * 
 * Handles advanced search and filtering functionality with keyword search
 * across title, caption, tags, and metadata as specified in requirements.
 */
class SearchController extends Controller
{
    /**
     * Global search across images, albums, users, and tags
     */
    public function search(Request $request): JsonResponse
    {
        $query = $request->get('q', '');
        $type = $request->get('type', 'all'); // all, images, albums, users, tags
        $filters = $request->only([
            'album_id',
            'user_id',
            'tag_id',
            'license',
            'date_from',
            'date_to',
            'min_views',
            'max_views',
            'camera',
            'lens',
            'iso_min',
            'iso_max',
            'featured_only',
            'has_downloads',
        ]);

        $results = [];

        if (empty($query) && empty(array_filter($filters))) {
            return response()->json([
                'query' => $query,
                'filters' => $filters,
                'results' => $results,
                'total' => 0,
            ]);
        }

        // Search Images
        if ($type === 'all' || $type === 'images') {
            $imageQuery = Image::visibleTo(Auth::user())
                ->with(['user', 'tags', 'album']);

            // Apply text search
            if ($query) {
                $imageQuery->where(function ($q) use ($query) {
                    $q->where('title', 'LIKE', "%{$query}%")
                        ->orWhere('description', 'LIKE', "%{$query}%")
                        ->orWhere('alt_text', 'LIKE', "%{$query}%")
                        ->orWhereHas('tags', function ($tagQuery) use ($query) {
                            $tagQuery->where('name', 'LIKE', "%{$query}%");
                        })
                        ->orWhereJsonContains('exif_data->camera', $query)
                        ->orWhereJsonContains('exif_data->lens', $query);
                });
            }

            // Apply filters
            $this->applyImageFilters($imageQuery, $filters);

            $images = $imageQuery->orderBy('created_at', 'desc')
                ->limit(20)
                ->get()
                ->map(fn($image) => $this->transformImageForApi($image));

            $results['images'] = $images;
        }

        // Search Albums
        if ($type === 'all' || $type === 'albums') {
            $albumQuery = Album::visibleTo(Auth::user())
                ->with(['user'])
                ->withCount(['images' => function ($q) {
                    $q->visibleTo(Auth::user());
                }]);

            if ($query) {
                $albumQuery->where(function ($q) use ($query) {
                    $q->where('title', 'LIKE', "%{$query}%")
                        ->orWhere('description', 'LIKE', "%{$query}%");
                });
            }

            // Apply album-specific filters
            if (!empty($filters['user_id'])) {
                $albumQuery->where('user_id', $filters['user_id']);
            }

            if (!empty($filters['featured_only'])) {
                $albumQuery->where('is_featured', true);
            }

            if (!empty($filters['date_from'])) {
                $albumQuery->where('created_at', '>=', $filters['date_from']);
            }

            if (!empty($filters['date_to'])) {
                $albumQuery->where('created_at', '<=', $filters['date_to']);
            }

            $albums = $albumQuery->orderBy('created_at', 'desc')
                ->limit(10)
                ->get()
                ->map(fn($album) => $this->transformAlbumForApi($album));

            $results['albums'] = $albums;
        }

        // Search Users
        if ($type === 'all' || $type === 'users') {
            $userQuery = User::active();

            if ($query) {
                $userQuery->where(function ($q) use ($query) {
                    $q->where('name', 'LIKE', "%{$query}%")
                        ->orWhere('bio', 'LIKE', "%{$query}%");
                });
            }

            $users = $userQuery->withCount(['images' => function ($q) {
                $q->visibleTo(Auth::user());
            }])
                ->orderBy('images_count', 'desc')
                ->limit(10)
                ->get()
                ->map(fn($user) => [
                    'id' => $user->id,
                    'name' => $user->name,
                    'bio' => $user->bio,
                    'avatar_url' => $user->avatar_url,
                    'image_count' => $user->images_count,
                ]);

            $results['users'] = $users;
        }

        // Search Tags
        if ($type === 'all' || $type === 'tags') {
            $tagQuery = Tag::query();

            if ($query) {
                $tagQuery->where('name', 'LIKE', "%{$query}%");
            }

            $tags = $tagQuery->orderBy('usage_count', 'desc')
                ->limit(20)
                ->get()
                ->map(fn($tag) => [
                    'id' => $tag->id,
                    'name' => $tag->name,
                    'slug' => $tag->slug,
                    'color' => $tag->color,
                    'usage_count' => $tag->usage_count,
                ]);

            $results['tags'] = $tags;
        }

        $total = collect($results)->sum(fn($items) => count($items));

        return response()->json([
            'query' => $query,
            'filters' => $filters,
            'results' => $results,
            'total' => $total,
        ]);
    }

    /**
     * Get search suggestions for autocomplete
     */
    public function suggestions(Request $request): JsonResponse
    {
        $query = $request->get('q', '');

        if (strlen($query) < 2) {
            return response()->json(['suggestions' => []]);
        }

        $suggestions = [];

        // Image titles
        $imageTitles = Image::visibleTo(Auth::user())
            ->where('title', 'LIKE', "%{$query}%")
            ->distinct()
            ->pluck('title')
            ->take(5);

        foreach ($imageTitles as $title) {
            $suggestions[] = [
                'text' => $title,
                'type' => 'image_title',
                'category' => 'Images',
            ];
        }

        // Tags
        $tags = Tag::where('name', 'LIKE', "%{$query}%")
            ->orderBy('usage_count', 'desc')
            ->take(8)
            ->get();

        foreach ($tags as $tag) {
            $suggestions[] = [
                'text' => $tag->name,
                'type' => 'tag',
                'category' => 'Tags',
                'color' => $tag->color,
            ];
        }

        // Users
        $users = User::active()
            ->where('name', 'LIKE', "%{$query}%")
            ->take(5)
            ->get();

        foreach ($users as $user) {
            $suggestions[] = [
                'text' => $user->name,
                'type' => 'user',
                'category' => 'Photographers',
                'avatar_url' => $user->avatar_url,
            ];
        }

        // Album titles
        $albumTitles = Album::visibleTo(Auth::user())
            ->where('title', 'LIKE', "%{$query}%")
            ->distinct()
            ->pluck('title')
            ->take(5);

        foreach ($albumTitles as $title) {
            $suggestions[] = [
                'text' => $title,
                'type' => 'album_title',
                'category' => 'Albums',
            ];
        }

        return response()->json([
            'suggestions' => $suggestions,
        ]);
    }

    /**
     * Get available filter options
     */
    public function filters(): JsonResponse
    {
        $filters = [
            'cameras' => Image::visibleTo(Auth::user())
                ->whereNotNull('exif_data->camera')
                ->distinct()
                ->pluck('exif_data->camera')
                ->filter()
                ->values()
                ->take(20),

            'lenses' => Image::visibleTo(Auth::user())
                ->whereNotNull('exif_data->lens')
                ->distinct()
                ->pluck('exif_data->lens')
                ->filter()
                ->values()
                ->take(20),

            'licenses' => Image::visibleTo(Auth::user())
                ->whereNotNull('license')
                ->distinct()
                ->pluck('license')
                ->filter()
                ->values(),

            'popular_tags' => Tag::orderBy('usage_count', 'desc')
                ->take(30)
                ->get()
                ->map(fn($tag) => [
                    'id' => $tag->id,
                    'name' => $tag->name,
                    'slug' => $tag->slug,
                    'color' => $tag->color,
                    'usage_count' => $tag->usage_count,
                ]),

            'top_photographers' => User::active()
                ->withCount(['images' => function ($q) {
                    $q->visibleTo(Auth::user());
                }])
                ->having('images_count', '>', 0)
                ->orderBy('images_count', 'desc')
                ->take(20)
                ->get()
                ->map(fn($user) => [
                    'id' => $user->id,
                    'name' => $user->name,
                    'avatar_url' => $user->avatar_url,
                    'image_count' => $user->images_count,
                ]),
        ];

        return response()->json($filters);
    }

    /**
     * Apply advanced filters to image query
     */
    private function applyImageFilters($query, array $filters): void
    {
        if (!empty($filters['album_id'])) {
            $query->where('album_id', $filters['album_id']);
        }

        if (!empty($filters['user_id'])) {
            $query->where('user_id', $filters['user_id']);
        }

        if (!empty($filters['tag_id'])) {
            $query->whereHas('tags', function ($q) use ($filters) {
                $q->where('tag_id', $filters['tag_id']);
            });
        }

        if (!empty($filters['license'])) {
            $query->where('license', $filters['license']);
        }

        if (!empty($filters['date_from'])) {
            $query->where('created_at', '>=', $filters['date_from']);
        }

        if (!empty($filters['date_to'])) {
            $query->where('created_at', '<=', $filters['date_to']);
        }

        if (!empty($filters['min_views'])) {
            $query->where('view_count', '>=', $filters['min_views']);
        }

        if (!empty($filters['max_views'])) {
            $query->where('view_count', '<=', $filters['max_views']);
        }

        if (!empty($filters['camera'])) {
            $query->whereJsonContains('exif_data->camera', $filters['camera']);
        }

        if (!empty($filters['lens'])) {
            $query->whereJsonContains('exif_data->lens', $filters['lens']);
        }

        if (!empty($filters['iso_min'])) {
            $query->where('exif_data->iso', '>=', $filters['iso_min']);
        }

        if (!empty($filters['iso_max'])) {
            $query->where('exif_data->iso', '<=', $filters['iso_max']);
        }

        if (!empty($filters['featured_only'])) {
            $query->where('is_featured', true);
        }

        if (!empty($filters['has_downloads'])) {
            $query->where('allow_download', true);
        }
    }

    /**
     * Transform image for API response
     */
    private function transformImageForApi($image): array
    {
        return [
            'id' => $image->id,
            'title' => $image->title,
            'description' => $image->description,
            'slug' => $image->slug,
            'thumbnail_url' => $image->thumbnail_url,
            'width' => $image->width,
            'height' => $image->height,
            'aspect_ratio' => $image->aspect_ratio,
            'view_count' => $image->view_count,
            'like_count' => $image->like_count,
            'is_liked' => $image->isLikedBy(Auth::user()),
            'created_at' => $image->created_at,
            'user' => [
                'id' => $image->user->id,
                'name' => $image->user->name,
                'avatar_url' => $image->user->avatar_url,
            ],
            'album' => $image->album ? [
                'id' => $image->album->id,
                'title' => $image->album->title,
                'slug' => $image->album->slug,
            ] : null,
            'tags' => $image->tags->map(fn($tag) => [
                'id' => $tag->id,
                'name' => $tag->name,
                'slug' => $tag->slug,
                'color' => $tag->color,
            ]),
            'exif_data' => $image->exif_data,
        ];
    }

    /**
     * Transform album for API response
     */
    private function transformAlbumForApi($album): array
    {
        return [
            'id' => $album->id,
            'title' => $album->title,
            'description' => $album->description,
            'slug' => $album->slug,
            'cover_image_url' => $album->cover_image_url,
            'image_count' => $album->images_count,
            'created_at' => $album->created_at,
            'user' => [
                'id' => $album->user->id,
                'name' => $album->user->name,
                'avatar_url' => $album->user->avatar_url,
            ],
        ];
    }
}
