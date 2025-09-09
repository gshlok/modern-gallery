<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * Theme Model
 * 
 * Manages custom color palettes and theming options.
 */
class Theme extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'name',
        'description',
        'is_public',
        'is_default',
        'colors',
        'typography',
        'spacing',
        'layout',
        'usage_count',
    ];

    protected $casts = [
        'is_public' => 'boolean',
        'is_default' => 'boolean',
        'colors' => 'array',
        'typography' => 'array',
        'spacing' => 'array',
        'layout' => 'array',
    ];

    /**
     * Get the user who created this theme
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Generate CSS custom properties from colors
     */
    public function getCssVariables(): string
    {
        $css = '';

        if ($this->colors) {
            foreach ($this->colors as $name => $value) {
                $css .= "--color-{$name}: {$value};\n";
            }
        }

        if ($this->typography) {
            foreach ($this->typography as $property => $value) {
                if (is_array($value)) {
                    foreach ($value as $variant => $variantValue) {
                        $css .= "--font-{$property}-{$variant}: {$variantValue};\n";
                    }
                } else {
                    $css .= "--font-{$property}: {$value};\n";
                }
            }
        }

        if ($this->spacing) {
            foreach ($this->spacing as $size => $value) {
                $css .= "--spacing-{$size}: {$value};\n";
            }
        }

        return $css;
    }

    /**
     * Get default colors if not set
     */
    public function getDefaultColors(): array
    {
        return [
            'primary' => '#3b82f6',
            'secondary' => '#6b7280',
            'accent' => '#f59e0b',
            'background' => '#ffffff',
            'surface' => '#f9fafb',
            'text' => '#111827',
            'text-muted' => '#6b7280',
            'border' => '#e5e7eb',
            'success' => '#10b981',
            'warning' => '#f59e0b',
            'error' => '#ef4444',
        ];
    }

    /**
     * Increment usage count
     */
    public function incrementUsage(): void
    {
        $this->increment('usage_count');
    }

    /**
     * Scope for public themes
     */
    public function scopePublic($query)
    {
        return $query->where('is_public', true);
    }

    /**
     * Scope for default themes
     */
    public function scopeDefault($query)
    {
        return $query->where('is_default', true);
    }

    /**
     * Scope for popular themes
     */
    public function scopePopular($query, int $limit = 10)
    {
        return $query->orderBy('usage_count', 'desc')->limit($limit);
    }
}
