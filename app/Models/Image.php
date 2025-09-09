<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\MorphMany;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Intervention\Image\Facades\Image as InterventionImage;

/**
 * Image Model
 * 
 * Represents an uploaded image with metadata, privacy settings, and relationships.
 */
class Image extends Model
{
    use HasFactory;

    protected $fillable = [
        'title',
        'description',
        'alt_text',
        'slug',
        'filename',
        'path',
        'disk',
        'mime_type',
        'file_size',
        'hash',
        'width',
        'height',
        'aspect_ratio',
        'visibility',
        'license',
        'allow_download',
        'is_featured',
        'exif_data',
        'thumbnails',
        'view_count',
        'download_count',
        'last_viewed_at',
        'is_ai_generated',
        'generation_prompt',
        'generation_metadata',
        'vector_embedding',
        'embedding_generated_at',
    ];

    protected $casts = [
        'exif_data' => 'array',
        'thumbnails' => 'array',
        'generation_metadata' => 'array',
        'vector_embedding' => 'array',
        'is_featured' => 'boolean',
        'allow_download' => 'boolean',
        'is_ai_generated' => 'boolean',
        'last_viewed_at' => 'datetime',
        'embedding_generated_at' => 'datetime',
        'aspect_ratio' => 'decimal:3',
    ];

    /**
     * Boot the model and set up event listeners
     */
    protected static function boot()
    {
        parent::boot();

        static::creating(function ($image) {
            if (empty($image->slug)) {
                $image->slug = Str::slug($image->title);

                // Ensure slug uniqueness
                $count = static::where('slug', 'like', $image->slug . '%')->count();
                if ($count > 0) {
                    $image->slug = $image->slug . '-' . ($count + 1);
                }
            }

            // Calculate aspect ratio
            if ($image->width && $image->height) {
                $image->aspect_ratio = round($image->width / $image->height, 3);
            }
        });

        static::deleting(function ($image) {
            // Delete physical files when model is deleted
            $image->deleteFiles();
        });
    }

    /**
     * Get the user who uploaded this image
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Get the album this image belongs to
     */
    public function album(): BelongsTo
    {
        return $this->belongsTo(Album::class);
    }

    /**
     * Get all tags associated with this image
     */
    public function tags(): BelongsToMany
    {
        return $this->belongsToMany(Tag::class)->withTimestamps();
    }

    /**
     * Get all comments on this image
     */
    public function comments(): HasMany
    {
        return $this->hasMany(Comment::class)->orderBy('created_at', 'desc');
    }

    /**
     * Get approved comments only
     */
    public function approvedComments(): HasMany
    {
        return $this->hasMany(Comment::class)->where('status', 'approved');
    }

    /**
     * Get all likes for this image
     */
    public function likes(): MorphMany
    {
        return $this->morphMany(Like::class, 'likeable');
    }

    /**
     * Get analytics events for this image
     */
    public function analytics(): MorphMany
    {
        return $this->morphMany(AnalyticsEvent::class, 'trackable');
    }

    /**
     * Get vector embeddings for this image
     */
    public function embeddings(): MorphMany
    {
        return $this->morphMany(VectorEmbedding::class, 'embeddable');
    }

    /**
     * Get AI generation record if this is an AI-generated image
     */
    public function aiGeneration(): BelongsTo
    {
        return $this->belongsTo(AiGeneration::class, 'id', 'image_id');
    }

    /**
     * Get the full URL of the original image
     */
    public function getUrlAttribute(): string
    {
        return Storage::disk($this->disk)->url($this->path);
    }

    /**
     * Get thumbnail URL for specified size
     */
    public function getThumbnailUrl(int $size = 300): ?string
    {
        $thumbnails = $this->thumbnails ?? [];

        // Find closest size
        $closestSize = null;
        $minDiff = PHP_INT_MAX;

        foreach ($thumbnails as $thumbSize => $path) {
            $diff = abs($thumbSize - $size);
            if ($diff < $minDiff) {
                $minDiff = $diff;
                $closestSize = $thumbSize;
            }
        }

        if ($closestSize && isset($thumbnails[$closestSize])) {
            return Storage::disk($this->disk)->url($thumbnails[$closestSize]);
        }

        // Fallback to original image
        return $this->url;
    }

    /**
     * Get default thumbnail URL (300px)
     */
    public function getThumbnailUrlAttribute(): string
    {
        return $this->getThumbnailUrl(300);
    }

    /**
     * Get small thumbnail URL (150px)
     */
    public function getSmallThumbnailUrlAttribute(): string
    {
        return $this->getThumbnailUrl(150);
    }

    /**
     * Get large thumbnail URL (600px)
     */
    public function getLargeThumbnailUrlAttribute(): string
    {
        return $this->getThumbnailUrl(600);
    }

    /**
     * Get human-readable file size
     */
    public function getFileSizeHumanAttribute(): string
    {
        $bytes = $this->file_size;
        $units = ['B', 'KB', 'MB', 'GB'];

        for ($i = 0; $bytes > 1024 && $i < count($units) - 1; $i++) {
            $bytes /= 1024;
        }

        return round($bytes, 2) . ' ' . $units[$i];
    }

    /**
     * Get like count for this image
     */
    public function getLikeCountAttribute(): int
    {
        return $this->likes()->count();
    }

    /**
     * Get comment count for this image
     */
    public function getCommentCountAttribute(): int
    {
        return $this->approvedComments()->count();
    }

    /**
     * Check if user has liked this image
     */
    public function isLikedBy(?User $user = null): bool
    {
        if (!$user) {
            return false;
        }

        return $this->likes()->where('user_id', $user->id)->exists();
    }

    /**
     * Check if current user can view this image
     */
    public function canView(?User $user = null): bool
    {
        if ($this->visibility === 'public') {
            return true;
        }

        if ($this->visibility === 'unlisted') {
            return true; // Unlisted is viewable if you have the link
        }

        if ($this->visibility === 'private') {
            return $user && ($user->id === $this->user_id || $user->canModerate());
        }

        return false;
    }

    /**
     * Check if current user can edit this image
     */
    public function canEdit(?User $user = null): bool
    {
        if (!$user) {
            return false;
        }

        return $user->id === $this->user_id || $user->canModerate();
    }

    /**
     * Check if current user can download this image
     */
    public function canDownload(?User $user = null): bool
    {
        if (!$this->allow_download) {
            return false;
        }

        return $this->canView($user);
    }

    /**
     * Increment view count
     */
    public function incrementViews(?User $user = null): void
    {
        $this->increment('view_count');
        $this->update(['last_viewed_at' => now()]);

        // Track analytics
        AnalyticsEvent::create([
            'event_type' => 'image_view',
            'trackable_id' => $this->id,
            'trackable_type' => static::class,
            'user_id' => $user?->id,
            'session_id' => session()->getId(),
            'ip_address' => request()->ip(),
            'user_agent' => request()->userAgent(),
        ]);
    }

    /**
     * Increment download count
     */
    public function incrementDownloads(?User $user = null): void
    {
        $this->increment('download_count');

        // Track analytics
        AnalyticsEvent::create([
            'event_type' => 'image_download',
            'trackable_id' => $this->id,
            'trackable_type' => static::class,
            'user_id' => $user?->id,
            'session_id' => session()->getId(),
            'ip_address' => request()->ip(),
            'user_agent' => request()->userAgent(),
        ]);
    }

    /**
     * Generate thumbnails for this image
     */
    public function generateThumbnails(): void
    {
        $sizes = config('media.thumbnail_sizes', [150, 300, 600, 1200]);
        $thumbnails = [];

        foreach ($sizes as $size) {
            $thumbnailPath = $this->generateThumbnail($size);
            if ($thumbnailPath) {
                $thumbnails[$size] = $thumbnailPath;
            }
        }

        $this->update(['thumbnails' => $thumbnails]);
    }

    /**
     * Generate a single thumbnail
     */
    private function generateThumbnail(int $size): ?string
    {
        try {
            $originalPath = Storage::disk($this->disk)->path($this->path);
            $thumbnailPath = 'thumbnails/' . pathinfo($this->path, PATHINFO_FILENAME) . "_{$size}." . pathinfo($this->path, PATHINFO_EXTENSION);

            $image = InterventionImage::make($originalPath);
            $image->resize($size, $size, function ($constraint) {
                $constraint->aspectRatio();
                $constraint->upsize();
            });

            Storage::disk($this->disk)->put($thumbnailPath, $image->encode());

            return $thumbnailPath;
        } catch (\Exception $e) {
            \Log::error("Failed to generate thumbnail for image {$this->id}: " . $e->getMessage());
            return null;
        }
    }

    /**
     * Delete all files associated with this image
     */
    public function deleteFiles(): void
    {
        // Delete original image
        if (Storage::disk($this->disk)->exists($this->path)) {
            Storage::disk($this->disk)->delete($this->path);
        }

        // Delete thumbnails
        if ($this->thumbnails) {
            foreach ($this->thumbnails as $thumbnailPath) {
                if (Storage::disk($this->disk)->exists($thumbnailPath)) {
                    Storage::disk($this->disk)->delete($thumbnailPath);
                }
            }
        }
    }

    /**
     * Scope for public images
     */
    public function scopePublic($query)
    {
        return $query->where('visibility', 'public');
    }

    /**
     * Scope for featured images
     */
    public function scopeFeatured($query)
    {
        return $query->where('is_featured', true);
    }

    /**
     * Scope for images visible to a user
     */
    public function scopeVisibleTo($query, ?User $user = null)
    {
        return $query->where(function ($q) use ($user) {
            $q->where('visibility', 'public')
                ->orWhere('visibility', 'unlisted');

            if ($user) {
                $q->orWhere(function ($subQ) use ($user) {
                    $subQ->where('visibility', 'private')
                        ->where('user_id', $user->id);
                });
            }
        });
    }

    /**
     * Scope for searching images by text
     */
    public function scopeSearch($query, string $search)
    {
        return $query->where(function ($q) use ($search) {
            $q->where('title', 'LIKE', "%{$search}%")
                ->orWhere('description', 'LIKE', "%{$search}%")
                ->orWhere('alt_text', 'LIKE', "%{$search}%")
                ->orWhereHas('tags', function ($tagQuery) use ($search) {
                    $tagQuery->where('name', 'LIKE', "%{$search}%");
                });
        });
    }

    /**
     * Get the route key name for model binding
     */
    public function getRouteKeyName(): string
    {
        return 'slug';
    }
}
