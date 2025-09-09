<?php

namespace App\Http\Controllers;

use App\Models\Image;
use App\Models\Album;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Inertia\Inertia;
use Inertia\Response;

/**
 * Gallery Controller
 * 
 * Handles public gallery views and search functionality.
 */
class GalleryController extends Controller
{
    /**
     * Display the home page
     */
    public function index(): Response
    {
        // Get featured content
        $featuredImages = Image::visibleTo(Auth::user())
            ->featured()
            ->with(['user', 'tags'])
            ->limit(8)
            ->get()
            ->map(fn($image) => $this->transformImageForFrontend($image));

        $featuredAlbums = Album::visibleTo(Auth::user())
            ->featured()
            ->with(['user'])
            ->withCount(['images' => function ($query) {
                $query->visibleTo(Auth::user());
            }])
            ->limit(6)
            ->get()
            ->map(fn($album) => $this->transformAlbumForFrontend($album));

        // Get recent images
        $recentImages = Image::visibleTo(Auth::user())
            ->with(['user', 'tags'])
            ->orderBy('created_at', 'desc')
            ->limit(12)
            ->get()
            ->map(fn($image) => $this->transformImageForFrontend($image));

        // Get stats
        $stats = [
            'totalImages' => Image::visibleTo(Auth::user())->count(),
            'totalAlbums' => Album::visibleTo(Auth::user())->count(),
            'totalUsers' => User::active()->count(),
        ];

        return Inertia::render('Home', [
            'featuredImages' => $featuredImages,
            'featuredAlbums' => $featuredAlbums,
            'recentImages' => $recentImages,
            'stats' => $stats,
        ]);
    }

    /**
     * Display the main gallery page
     */
    public function gallery(Request $request): Response
    {
        $query = Image::visibleTo(Auth::user())
            ->with(['user', 'tags', 'album']);

        // Apply search filter
        if ($request->filled('search')) {
            $query->search($request->search);
        }

        // Apply tag filter
        if ($request->filled('tag')) {
            $query->whereHas('tags', function ($q) use ($request) {
                $q->where('slug', $request->tag);
            });
        }

        // Apply user filter
        if ($request->filled('user')) {
            $query->where('user_id', $request->user);
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

        $images = $query->paginate(24)->through(fn($image) => $this->transformImageForFrontend($image));

        // Get featured albums for the sidebar
        $featuredAlbums = Album::visibleTo(Auth::user())
            ->featured()
            ->with(['user'])
            ->withCount(['images' => function ($query) {
                $query->visibleTo(Auth::user());
            }])
            ->limit(3)
            ->get()
            ->map(fn($album) => $this->transformAlbumForFrontend($album));

        // Get stats
        $stats = [
            'totalImages' => Image::visibleTo(Auth::user())->count(),
            'totalAlbums' => Album::visibleTo(Auth::user())->count(),
            'totalUsers' => User::active()->count(),
        ];

        return Inertia::render('Gallery', [
            'images' => $images,
            'featuredAlbums' => $featuredAlbums,
            'filters' => $request->only(['search', 'tag', 'user', 'sort']),
            'stats' => $stats,
        ]);
    }

    /**
     * Search images and albums
     */
    public function search(Request $request): Response
    {
        $query = $request->get('q', '');
        $type = $request->get('type', 'all'); // all, images, albums

        $results = [];

        if ($query) {
            if ($type === 'all' || $type === 'images') {
                $images = Image::visibleTo(Auth::user())
                    ->search($query)
                    ->with(['user', 'tags'])
                    ->limit(20)
                    ->get()
                    ->map(fn($image) => $this->transformImageForFrontend($image));

                $results['images'] = $images;
            }

            if ($type === 'all' || $type === 'albums') {
                $albums = Album::visibleTo(Auth::user())
                    ->where(function ($q) use ($query) {
                        $q->where('title', 'LIKE', "%{$query}%")
                            ->orWhere('description', 'LIKE', "%{$query}%");
                    })
                    ->with(['user'])
                    ->withCount(['images' => function ($q) {
                        $q->visibleTo(Auth::user());
                    }])
                    ->limit(10)
                    ->get()
                    ->map(fn($album) => $this->transformAlbumForFrontend($album));

                $results['albums'] = $albums;
            }
        }

        return Inertia::render('Search', [
            'query' => $query,
            'type' => $type,
            'results' => $results,
        ]);
    }

    /**
     * Transform image for frontend consumption
     */
    private function transformImageForFrontend($image): array
    {
        return [
            'id' => $image->id,
            'title' => $image->title,
            'description' => $image->description,
            'alt_text' => $image->alt_text,
            'slug' => $image->slug,
            'url' => $image->url,
            'thumbnail_url' => $image->thumbnail_url,
            'small_thumbnail_url' => $image->small_thumbnail_url,
            'large_thumbnail_url' => $image->large_thumbnail_url,
            'width' => $image->width,
            'height' => $image->height,
            'aspect_ratio' => $image->aspect_ratio,
            'file_size_human' => $image->file_size_human,
            'visibility' => $image->visibility,
            'license' => $image->license,
            'allow_download' => $image->allow_download,
            'is_featured' => $image->is_featured,
            'view_count' => $image->view_count,
            'download_count' => $image->download_count,
            'like_count' => $image->like_count,
            'comment_count' => $image->comment_count,
            'is_liked' => $image->isLikedBy(Auth::user()),
            'created_at' => $image->created_at,
            'updated_at' => $image->updated_at,
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
            'is_ai_generated' => $image->is_ai_generated,
            'generation_prompt' => $image->generation_prompt,
        ];
    }

    /**
     * Transform album for frontend consumption
     */
    private function transformAlbumForFrontend($album): array
    {
        return [
            'id' => $album->id,
            'title' => $album->title,
            'description' => $album->description,
            'slug' => $album->slug,
            'cover_image_url' => $album->cover_image_url,
            'visibility' => $album->visibility,
            'is_featured' => $album->is_featured,
            'image_count' => $album->visible_image_count ?? $album->images_count ?? 0,
            'like_count' => $album->like_count ?? 0,
            'is_liked' => $album->isLikedBy(Auth::user()),
            'created_at' => $album->created_at,
            'updated_at' => $album->updated_at,
            'user' => [
                'id' => $album->user->id,
                'name' => $album->user->name,
                'avatar_url' => $album->user->avatar_url,
            ],
        ];
    }
}
