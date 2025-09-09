<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\MorphTo;

/**
 * Vector Embedding Model
 * 
 * Stores vector embeddings for images and other content.
 * Supports various embedding providers and models.
 */
class VectorEmbedding extends Model
{
    use HasFactory;

    protected $fillable = [
        'embeddable_id',
        'embeddable_type',
        'vector',
        'dimensions',
        'model',
        'provider',
        'metadata',
    ];

    protected $casts = [
        'vector' => 'array',
        'metadata' => 'array',
        'dimensions' => 'integer',
    ];

    /**
     * Get the embeddable entity (image, user, etc.)
     */
    public function embeddable(): MorphTo
    {
        return $this->morphTo();
    }

    /**
     * Scope for specific model
     */
    public function scopeForModel($query, string $model)
    {
        return $query->where('model', $model);
    }

    /**
     * Scope for specific provider
     */
    public function scopeForProvider($query, string $provider)
    {
        return $query->where('provider', $provider);
    }

    /**
     * Scope for specific embeddable type
     */
    public function scopeForType($query, string $type)
    {
        return $query->where('embeddable_type', $type);
    }

    /**
     * Get vector length/magnitude
     */
    public function getVectorMagnitudeAttribute(): float
    {
        if (!$this->vector) {
            return 0.0;
        }

        $sum = array_sum(array_map(fn($x) => $x * $x, $this->vector));
        return sqrt($sum);
    }

    /**
     * Normalize the vector
     */
    public function getNormalizedVectorAttribute(): array
    {
        $magnitude = $this->vector_magnitude;

        if ($magnitude == 0) {
            return $this->vector;
        }

        return array_map(fn($x) => $x / $magnitude, $this->vector);
    }
}
