<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\MorphMany;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

/**
 * Album Model
 * 
 * Represents a collection of images with metadata and privacy settings.
 */
class Album extends Model
{
    use HasFactory;

    protected $fillable = [
        'title',
        'description',
        'slug',
        'cover_image',
        'visibility',
        'is_featured',
        'sort_order',
        'settings',
    ];

    protected $casts = [
        'is_featured' => 'boolean',
        'settings' => 'array',
    ];

    /**
     * Boot the model and set up event listeners
     */
    protected static function boot()
    {
        parent::boot();

        static::creating(function ($album) {
            if (empty($album->slug)) {
                $album->slug = Str::slug($album->title);

                // Ensure slug uniqueness
                $count = static::where('slug', 'like', $album->slug . '%')->count();
                if ($count > 0) {
                    $album->slug = $album->slug . '-' . ($count + 1);
                }
            }
        });

        static::updating(function ($album) {
            if ($album->isDirty('title') && empty($album->getOriginal('slug'))) {
                $album->slug = Str::slug($album->title);
            }
        });
    }

    /**
     * Get the user that owns the album
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Get all images in this album
     */
    public function images(): HasMany
    {
        return $this->hasMany(Image::class)->orderBy('created_at', 'desc');
    }

    /**
     * Get visible images (respects privacy settings)
     */
    public function visibleImages()
    {
        return $this->images()->where(function ($query) {
            $query->where('visibility', 'public')
                ->orWhere(function ($subQuery) {
                    $subQuery->where('visibility', 'unlisted')
                        ->where('user_id', auth()->id());
                });
        });
    }

    /**
     * Get all likes for this album
     */
    public function likes(): MorphMany
    {
        return $this->morphMany(Like::class, 'likeable');
    }

    /**
     * Get analytics events for this album
     */
    public function analytics(): MorphMany
    {
        return $this->morphMany(AnalyticsEvent::class, 'trackable');
    }

    /**
     * Get vector embeddings for this album
     */
    public function embeddings(): MorphMany
    {
        return $this->morphMany(VectorEmbedding::class, 'embeddable');
    }

    /**
     * Check if current user can view this album
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
     * Check if current user can edit this album
     */
    public function canEdit(?User $user = null): bool
    {
        if (!$user) {
            return false;
        }

        return $user->id === $this->user_id || $user->canModerate();
    }

    /**
     * Get the cover image URL
     */
    public function getCoverImageUrlAttribute(): ?string
    {
        if ($this->cover_image) {
            return Storage::disk('public')->url($this->cover_image);
        }

        // Use first image as cover if no explicit cover set
        $firstImage = $this->visibleImages()->first();
        return $firstImage?->thumbnail_url;
    }

    /**
     * Get image count for this album
     */
    public function getImageCountAttribute(): int
    {
        return $this->images()->count();
    }

    /**
     * Get visible image count
     */
    public function getVisibleImageCountAttribute(): int
    {
        return $this->visibleImages()->count();
    }

    /**
     * Get like count for this album
     */
    public function getLikeCountAttribute(): int
    {
        return $this->likes()->count();
    }

    /**
     * Check if user has liked this album
     */
    public function isLikedBy(?User $user = null): bool
    {
        if (!$user) {
            return false;
        }

        return $this->likes()->where('user_id', $user->id)->exists();
    }

    /**
     * Scope for public albums
     */
    public function scopePublic($query)
    {
        return $query->where('visibility', 'public');
    }

    /**
     * Scope for featured albums
     */
    public function scopeFeatured($query)
    {
        return $query->where('is_featured', true);
    }

    /**
     * Scope for albums visible to a user
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
     * Get the route key name for model binding
     */
    public function getRouteKeyName(): string
    {
        return 'slug';
    }
}
