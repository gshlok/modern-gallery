<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * AI Generation Model
 * 
 * Tracks AI image generation requests and results.
 */
class AiGeneration extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'image_id',
        'prompt',
        'negative_prompt',
        'provider',
        'model',
        'parameters',
        'status',
        'error_message',
        'processing_time',
        'cost',
        'response_data',
    ];

    protected $casts = [
        'parameters' => 'array',
        'response_data' => 'array',
    ];

    /**
     * Get the user who requested this generation
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Get the resulting image (if generation was successful)
     */
    public function image(): BelongsTo
    {
        return $this->belongsTo(Image::class);
    }

    /**
     * Check if generation is completed
     */
    public function isCompleted(): bool
    {
        return $this->status === 'completed';
    }

    /**
     * Check if generation failed
     */
    public function isFailed(): bool
    {
        return $this->status === 'failed';
    }

    /**
     * Check if generation is processing
     */
    public function isProcessing(): bool
    {
        return $this->status === 'processing';
    }

    /**
     * Mark as completed
     */
    public function markCompleted(Image $image, int $processingTime = null): void
    {
        $this->update([
            'status' => 'completed',
            'image_id' => $image->id,
            'processing_time' => $processingTime,
        ]);
    }

    /**
     * Mark as failed
     */
    public function markFailed(string $errorMessage): void
    {
        $this->update([
            'status' => 'failed',
            'error_message' => $errorMessage,
        ]);
    }

    /**
     * Scope for completed generations
     */
    public function scopeCompleted($query)
    {
        return $query->where('status', 'completed');
    }

    /**
     * Scope for failed generations
     */
    public function scopeFailed($query)
    {
        return $query->where('status', 'failed');
    }

    /**
     * Scope for pending/processing generations
     */
    public function scopePending($query)
    {
        return $query->whereIn('status', ['pending', 'processing']);
    }
}
