<?php

namespace App\Http\Controllers;

use App\Models\Album;
use App\Http\Requests\AlbumStoreRequest;
use App\Http\Requests\AlbumUpdateRequest;
use Illuminate\Http\Request;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Auth;
use Inertia\Inertia;
use Inertia\Response;

/**
 * Album Controller
 * 
 * Handles album CRUD operations and viewing.
 */
class AlbumController extends Controller
{
    /**
     * Display a listing of albums
     */
    public function index(Request $request): Response
    {
        $query = Album::visibleTo(Auth::user())
            ->with(['user', 'images' => function ($query) {
                $query->visibleTo(Auth::user())->limit(4);
            }])
            ->withCount(['images' => function ($query) {
                $query->visibleTo(Auth::user());
            }]);

        // Apply filters
        if ($request->filled('featured')) {
            $query->featured();
        }

        if ($request->filled('user')) {
            $query->where('user_id', $request->user);
        }

        if ($request->filled('search')) {
            $query->where(function ($q) use ($request) {
                $q->where('title', 'LIKE', "%{$request->search}%")
                    ->orWhere('description', 'LIKE', "%{$request->search}%");
            });
        }

        $albums = $query->orderBy('created_at', 'desc')
            ->paginate(12)
            ->through(fn($album) => $this->transformAlbumForFrontend($album));

        return Inertia::render('Albums/Index', [
            'albums' => $albums,
            'filters' => $request->only(['featured', 'user', 'search']),
        ]);
    }

    /**
     * Display the specified album
     */
    public function show(Album $album): Response
    {
        // Check if user can view this album
        if (!$album->canView(Auth::user())) {
            abort(404);
        }

        // Load relationships
        $album->load([
            'user',
            'images' => function ($query) {
                $query->visibleTo(Auth::user())->with(['tags'])->orderBy('created_at', 'desc');
            }
        ]);

        return Inertia::render('Albums/Show', [
            'album' => $this->transformAlbumForFrontend($album, true),
            'canEdit' => $album->canEdit(Auth::user()),
        ]);
    }

    /**
     * Show the form for creating a new album
     */
    public function create(): Response
    {
        $this->authorize('create', Album::class);

        return Inertia::render('Albums/Create');
    }

    /**
     * Store a newly created album
     */
    public function store(AlbumStoreRequest $request): RedirectResponse
    {
        $this->authorize('create', Album::class);

        $album = Album::create([
            'user_id' => Auth::id(),
            ...$request->validated(),
        ]);

        return redirect()->route('albums.show', $album->slug)
            ->with('success', 'Album created successfully!');
    }

    /**
     * Show the form for editing the specified album
     */
    public function edit(Album $album): Response
    {
        $this->authorize('update', $album);

        return Inertia::render('Albums/Edit', [
            'album' => $this->transformAlbumForFrontend($album),
        ]);
    }

    /**
     * Update the specified album
     */
    public function update(AlbumUpdateRequest $request, Album $album): RedirectResponse
    {
        $this->authorize('update', $album);

        $album->update($request->validated());

        return redirect()->route('albums.show', $album->slug)
            ->with('success', 'Album updated successfully!');
    }

    /**
     * Remove the specified album
     */
    public function destroy(Album $album): RedirectResponse
    {
        $this->authorize('delete', $album);

        // Move images to no album
        $album->images()->update(['album_id' => null]);

        $album->delete();

        return redirect()->route('dashboard')
            ->with('success', 'Album deleted successfully!');
    }

    /**
     * Show user's albums
     */
    public function myAlbums(Request $request): Response
    {
        $query = Auth::user()->albums()
            ->withCount('images')
            ->orderBy('created_at', 'desc');

        // Apply search filter
        if ($request->filled('search')) {
            $query->where('title', 'LIKE', "%{$request->search}%");
        }

        $albums = $query->paginate(12)->through(fn($album) => $this->transformAlbumForFrontend($album));

        return Inertia::render('Albums/MyAlbums', [
            'albums' => $albums,
            'filters' => $request->only(['search']),
        ]);
    }

    /**
     * Transform album for frontend consumption
     */
    private function transformAlbumForFrontend(Album $album, bool $includeImages = false): array
    {
        $data = [
            'id' => $album->id,
            'title' => $album->title,
            'description' => $album->description,
            'slug' => $album->slug,
            'cover_image_url' => $album->cover_image_url,
            'visibility' => $album->visibility,
            'is_featured' => $album->is_featured,
            'image_count' => $album->visible_image_count,
            'like_count' => $album->like_count,
            'is_liked' => $album->isLikedBy(Auth::user()),
            'created_at' => $album->created_at,
            'updated_at' => $album->updated_at,
            'user' => [
                'id' => $album->user->id,
                'name' => $album->user->name,
                'avatar_url' => $album->user->avatar_url,
            ],
        ];

        if ($includeImages && $album->relationLoaded('images')) {
            $data['images'] = $album->images->map(function ($image) {
                return [
                    'id' => $image->id,
                    'title' => $image->title,
                    'slug' => $image->slug,
                    'thumbnail_url' => $image->thumbnail_url,
                    'width' => $image->width,
                    'height' => $image->height,
                    'aspect_ratio' => $image->aspect_ratio,
                    'view_count' => $image->view_count,
                    'like_count' => $image->like_count,
                    'created_at' => $image->created_at,
                    'tags' => $image->tags->map(fn($tag) => [
                        'id' => $tag->id,
                        'name' => $tag->name,
                        'slug' => $tag->slug,
                        'color' => $tag->color,
                    ]),
                ];
            });
        }

        return $data;
    }
}
