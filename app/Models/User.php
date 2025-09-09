<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Illuminate\Support\Facades\Storage;
use Laravel\Sanctum\HasApiTokens;
use Spatie\Permission\Traits\HasRoles;

/**
 * User Model
 * 
 * Handles user authentication, profile management, and relationships
 * with albums, images, and other user-generated content.
 */
class User extends Authenticatable
{
    use HasApiTokens, HasFactory, Notifiable, HasRoles;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'name',
        'email',
        'password',
        'avatar',
        'bio',
        'website',
        'is_active',
        'last_active_at',
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var array<int, string>
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'email_verified_at' => 'datetime',
        'last_active_at' => 'datetime',
        'is_active' => 'boolean',
        'password' => 'hashed',
    ];

    /**
     * Get all albums owned by the user
     */
    public function albums()
    {
        return $this->hasMany(Album::class);
    }

    /**
     * Get all images uploaded by the user
     */
    public function images()
    {
        return $this->hasMany(Image::class);
    }

    /**
     * Get all comments made by the user
     */
    public function comments()
    {
        return $this->hasMany(Comment::class);
    }

    /**
     * Get all likes made by the user
     */
    public function likes()
    {
        return $this->hasMany(Like::class);
    }

    /**
     * Get all AI generations requested by the user
     */
    public function aiGenerations()
    {
        return $this->hasMany(AiGeneration::class);
    }

    /**
     * Get all custom themes created by the user
     */
    public function themes()
    {
        return $this->hasMany(Theme::class);
    }

    /**
     * Check if user has admin role
     */
    public function isAdmin(): bool
    {
        return $this->hasRole('admin');
    }

    /**
     * Check if user has editor role
     */
    public function isEditor(): bool
    {
        return $this->hasRole(['admin', 'editor']);
    }

    /**
     * Check if user can upload images
     */
    public function canUpload(): bool
    {
        return $this->hasPermissionTo('upload images') || $this->isEditor();
    }

    /**
     * Check if user can moderate content
     */
    public function canModerate(): bool
    {
        return $this->hasPermissionTo('moderate content') || $this->isEditor();
    }

    /**
     * Get user's avatar URL
     */
    public function getAvatarUrlAttribute(): ?string
    {
        if ($this->avatar) {
            return Storage::disk('public')->url($this->avatar);
        }

        // Generate Gravatar URL as fallback
        $hash = md5(strtolower(trim($this->email)));
        return "https://www.gravatar.com/avatar/{$hash}?d=identicon&s=200";
    }

    /**
     * Update user's last active timestamp
     */
    public function updateLastActive(): void
    {
        $this->update(['last_active_at' => now()]);
    }

    /**
     * Scope for active users
     */
    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    /**
     * Scope for recently active users
     */
    public function scopeRecentlyActive($query, $days = 30)
    {
        return $query->where('last_active_at', '>', now()->subDays($days));
    }
}
