<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class AIGeneration extends Model
{
    protected $fillable = [
        'image_id', 'user_id', 'provider', 'model_name',
        'prompt', 'parameters', 'external_id', 'status', 'error_message'
    ];

    protected $casts = [
        'parameters' => 'array',
    ];

    public function image(): BelongsTo
    {
        return $this->belongsTo(Image::class);
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
