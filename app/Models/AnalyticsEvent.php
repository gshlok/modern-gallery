<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\MorphTo;

/**
 * Analytics Event Model
 * 
 * Tracks user interactions and events across the platform.
 */
class AnalyticsEvent extends Model
{
    use HasFactory;

    protected $fillable = [
        'event_type',
        'trackable_id',
        'trackable_type',
        'user_id',
        'session_id',
        'ip_address',
        'user_agent',
        'referer',
        'metadata',
    ];

    protected $casts = [
        'metadata' => 'array',
    ];

    /**
     * Get the user who triggered this event
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Get the trackable entity
     */
    public function trackable(): MorphTo
    {
        return $this->morphTo();
    }

    /**
     * Scope for specific event types
     */
    public function scopeEventType($query, string $type)
    {
        return $query->where('event_type', $type);
    }

    /**
     * Scope for events in date range
     */
    public function scopeDateRange($query, $startDate, $endDate = null)
    {
        $endDate = $endDate ?? now();
        return $query->whereBetween('created_at', [$startDate, $endDate]);
    }

    /**
     * Scope for unique sessions
     */
    public function scopeUniqueSessions($query)
    {
        return $query->distinct('session_id');
    }
}
