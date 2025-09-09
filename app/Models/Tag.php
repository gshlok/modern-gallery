<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Support\Str;

/**
 * Tag Model
 * 
 * Represents tags that can be attached to images for categorization.
 */
class Tag extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'slug',
        'color',
        'description',
        'usage_count',
    ];

    protected static function boot()
    {
        parent::boot();

        static::creating(function ($tag) {
            if (empty($tag->slug)) {
                $tag->slug = Str::slug($tag->name);
            }
        });

        static::updating(function ($tag) {
            if ($tag->isDirty('name')) {
                $tag->slug = Str::slug($tag->name);
            }
        });
    }

    /**
     * Get all images associated with this tag
     */
    public function images(): BelongsToMany
    {
        return $this->belongsToMany(Image::class)->withTimestamps();
    }

    /**
     * Get public images associated with this tag
     */
    public function publicImages(): BelongsToMany
    {
        return $this->images()->where('visibility', 'public');
    }

    /**
     * Increment usage count
     */
    public function incrementUsage(): void
    {
        $this->increment('usage_count');
    }

    /**
     * Decrement usage count
     */
    public function decrementUsage(): void
    {
        $this->decrement('usage_count');
    }

    /**
     * Scope for popular tags
     */
    public function scopePopular($query, int $limit = 20)
    {
        return $query->orderBy('usage_count', 'desc')->limit($limit);
    }

    /**
     * Get the route key name for model binding
     */
    public function getRouteKeyName(): string
    {
        return 'slug';
    }
}
