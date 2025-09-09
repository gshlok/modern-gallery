<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\MorphTo;

/**
 * Like Model
 * 
 * Represents user likes on various content (images, albums, comments).
 */
class Like extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'likeable_id',
        'likeable_type',
        'ip_address',
    ];

    /**
     * Get the user who made the like
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Get the likeable entity (image, album, comment)
     */
    public function likeable(): MorphTo
    {
        return $this->morphTo();
    }
}
