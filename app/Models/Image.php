<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Image extends Model
{
    use HasFactory;

    protected $fillable = [
        'uuid',
        'filename',
        'title',
        'caption',
        'mime_type',
        'width',
        'height',
        'size_bytes',
        'exif_data',
        'user_id',
        'album_id',
    ];

    protected $casts = [
        'exif_data' => 'array',
    ];

    /**
     * Image belongs to a user.
     */
    public function user()
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Image belongs to an album.
     */
    public function album()
    {
        return $this->belongsTo(Album::class);
    }

    /**
     * Boot method to automatically generate UUID on creating.
     */
    protected static function boot()
    {
        parent::boot();

        static::creating(function ($image) {
            if (empty($image->uuid)) {
                $image->uuid = \Illuminate\Support\Str::uuid()->toString();
            }
        });
    }
}
