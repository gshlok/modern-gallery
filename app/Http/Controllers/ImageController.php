<?php

namespace App\Http\Controllers;

use App\Models\Image;
use App\Models\Album;
use App\Models\User;
use App\Services\ImageUploadService;
use App\Http\Requests\ImageStoreRequest;
use App\Http\Requests\ImageUpdateRequest;
use Illuminate\Http\Request;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
use Inertia\Inertia;
use Inertia\Response;

/**
 * Image Controller
 * 
 * Handles image CRUD operations, uploads, and viewing.
 */
class ImageController extends Controller
{
    public function __construct(
        private ImageUploadService $imageUploadService
    ) {}

    /**
     * Display the specified image
     */
    public function show(Image $image): Response
    {
        // Check if user can view this image
        if (!$image->canView(Auth::user())) {
            abort(404);
        }

        // Track view
        $image->incrementViews(Auth::user());

        // Load relationships
        $image->load([
            'user',
            'album',
            'tags',
            'approvedComments.user',
            'approvedComments.replies.user'
        ]);

        // Get related images (from same album or by same user)
        $relatedImages = $this->getRelatedImages($image);

        return Inertia::render('Images/Show', [
            'image' => $this->transformImageForFrontend($image),
            'relatedImages' => $relatedImages->map(fn($img) => $this->transformImageForFrontend($img)),
            'canEdit' => $image->canEdit(Auth::user()),
            'canDownload' => $image->canDownload(Auth::user()),
        ]);
    }

    /**
     * Show the form for creating a new image
     */
    public function create(): Response
    {
        $this->authorize('create', Image::class);

        $albums = Auth::user()->albums()->orderBy('title')->get();

        return Inertia::render('Images/Create', [
            'albums' => $albums,
        ]);
    }

    /**
     * Store a newly created image
     */
    public function store(ImageStoreRequest $request): RedirectResponse
    {
        $this->authorize('create', Image::class);

        try {
            $image = $this->imageUploadService->uploadImage(
                $request->file('image'),
                $request->validated(),
                Auth::user()
            );

            return redirect()->route('images.show', $image->slug)
                ->with('success', 'Image uploaded successfully!');
        } catch (\Exception $e) {
            return back()->withErrors(['image' => 'Failed to upload image: ' . $e->getMessage()]);
        }
    }

    /**
     * Show the form for editing the specified image
     */
    public function edit(Image $image): Response
    {
        $this->authorize('update', $image);

        $albums = Auth::user()->albums()->orderBy('title')->get();

        return Inertia::render('Images/Edit', [
            'image' => $this->transformImageForFrontend($image),
            'albums' => $albums,
        ]);
    }

    /**
     * Update the specified image
     */
    public function update(ImageUpdateRequest $request, Image $image): RedirectResponse
    {
        $this->authorize('update', $image);

        $image->update($request->validated());

        // Handle tag updates
        if ($request->has('tags')) {
            $this->syncTags($image, $request->tags);
        }

        return redirect()->route('images.show', $image->slug)
            ->with('success', 'Image updated successfully!');
    }

    /**
     * Remove the specified image
     */
    public function destroy(Image $image): RedirectResponse
    {
        $this->authorize('delete', $image);

        $image->delete();

        return redirect()->route('dashboard')
            ->with('success', 'Image deleted successfully!');
    }

    /**
     * Download the specified image
     */
    public function download(Image $image)
    {
        if (!$image->canDownload(Auth::user())) {
            abort(403);
        }

        // Track download
        $image->incrementDownloads(Auth::user());

        return Storage::disk($image->disk)->download($image->path, $image->filename);
    }

    /**
     * Show user's images
     */
    public function myImages(Request $request): Response
    {
        $query = Auth::user()->images()
            ->with(['album', 'tags'])
            ->orderBy('created_at', 'desc');

        // Apply filters
        if ($request->filled('album')) {
            $query->where('album_id', $request->album);
        }

        if ($request->filled('visibility')) {
            $query->where('visibility', $request->visibility);
        }

        if ($request->filled('search')) {
            $query->search($request->search);
        }

        $images = $query->paginate(24)->through(fn($image) => $this->transformImageForFrontend($image));

        $albums = Auth::user()->albums()->orderBy('title')->get();

        return Inertia::render('Images/MyImages', [
            'images' => $images,
            'albums' => $albums,
            'filters' => $request->only(['album', 'visibility', 'search']),
        ]);
    }

    /**
     * Get related images for the current image
     */
    private function getRelatedImages(Image $image, int $limit = 6)
    {
        $query = Image::visibleTo(Auth::user())
            ->where('id', '!=', $image->id)
            ->with(['user', 'album']);

        // Prefer images from the same album
        if ($image->album_id) {
            $albumImages = (clone $query)->where('album_id', $image->album_id)->limit($limit)->get();

            if ($albumImages->count() < $limit) {
                $remaining = $limit - $albumImages->count();
                $userImages = (clone $query)
                    ->where('user_id', $image->user_id)
                    ->where('album_id', '!=', $image->album_id)
                    ->limit($remaining)
                    ->get();

                return $albumImages->concat($userImages);
            }

            return $albumImages;
        }

        // Otherwise, get images from the same user
        return $query->where('user_id', $image->user_id)->limit($limit)->get();
    }

    /**
     * Transform image for frontend consumption
     */
    private function transformImageForFrontend(Image $image): array
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
     * Sync tags for an image
     */
    private function syncTags(Image $image, array $tagNames): void
    {
        $tagIds = [];

        foreach ($tagNames as $tagName) {
            $tag = \App\Models\Tag::firstOrCreate(
                ['name' => trim($tagName)],
                ['color' => '#' . substr(md5($tagName), 0, 6)]
            );
            $tagIds[] = $tag->id;
        }

        $image->tags()->sync($tagIds);
    }
}
