<?php

return [
    /*
    |--------------------------------------------------------------------------
    | Default AI Provider
    |--------------------------------------------------------------------------
    |
    | This option controls the default AI provider that will be used
    | for image generation. You may change this value to any of the
    | providers defined in the "providers" array below.
    |
    */

    'default_provider' => env('AI_PROVIDER', 'mock'),

    /*
    |--------------------------------------------------------------------------
    | AI Generation Providers
    |--------------------------------------------------------------------------
    |
    | Here you may configure the AI providers for image generation.
    | Each provider can have its own configuration and credentials.
    |
    */

    'providers' => [
        'mock' => [
            'enabled' => true,
            'models' => ['mock-model-v1'],
            'cost_per_generation' => 0.00,
            'max_concurrent' => 5,
            'timeout' => 30,
        ],

        'openai' => [
            'enabled' => env('OPENAI_ENABLED', false),
            'api_key' => env('OPENAI_API_KEY'),
            'organization' => env('OPENAI_ORGANIZATION'),
            'models' => [
                'dall-e-3' => [
                    'max_prompt_length' => 4000,
                    'sizes' => ['1024x1024', '1024x1792', '1792x1024'],
                    'cost_per_generation' => 0.04,
                    'quality' => ['standard', 'hd'],
                    'style' => ['vivid', 'natural'],
                ],
                'dall-e-2' => [
                    'max_prompt_length' => 1000,
                    'sizes' => ['256x256', '512x512', '1024x1024'],
                    'cost_per_generation' => 0.02,
                    'variations' => true,
                ],
            ],
            'rate_limit' => [
                'requests_per_minute' => 50,
                'requests_per_day' => 1000,
            ],
        ],

        'stability' => [
            'enabled' => env('STABILITY_ENABLED', false),
            'api_key' => env('STABILITY_API_KEY'),
            'host' => env('STABILITY_HOST', 'https://api.stability.ai'),
            'models' => [
                'stable-diffusion-xl-1024-v1-0' => [
                    'dimensions' => ['1024x1024', '1152x896', '896x1152', '1216x832', '832x1216'],
                    'steps' => [10, 50],
                    'cfg_scale' => [0, 35],
                    'cost_per_generation' => 0.03,
                ],
                'stable-diffusion-v1-6' => [
                    'dimensions' => ['512x512', '768x768'],
                    'steps' => [10, 150],
                    'cfg_scale' => [0, 35],
                    'cost_per_generation' => 0.02,
                ],
            ],
            'rate_limit' => [
                'requests_per_minute' => 30,
                'requests_per_hour' => 500,
            ],
        ],

        'midjourney' => [
            'enabled' => env('MIDJOURNEY_ENABLED', false),
            'api_key' => env('MIDJOURNEY_API_KEY'),
            'webhook_url' => env('MIDJOURNEY_WEBHOOK_URL'),
            'models' => [
                'midjourney-v6' => [
                    'aspect_ratios' => ['1:1', '16:9', '9:16', '3:2', '2:3'],
                    'quality' => [0.25, 0.5, 1, 2],
                    'stylize' => [0, 1000],
                    'cost_per_generation' => 0.05,
                ],
                'midjourney-v5.2' => [
                    'aspect_ratios' => ['1:1', '16:9', '9:16'],
                    'quality' => [0.25, 0.5, 1, 2],
                    'cost_per_generation' => 0.04,
                ],
            ],
            'rate_limit' => [
                'requests_per_minute' => 20,
                'requests_per_hour' => 200,
            ],
        ],
    ],

    /*
    |--------------------------------------------------------------------------
    | Generation Settings
    |--------------------------------------------------------------------------
    |
    | Global settings for AI image generation.
    |
    */

    'generation' => [
        'enabled' => env('AI_GENERATION_ENABLED', true), // Enable AI generation
        'queue' => 'ai-generation', // Queue for background processing
        'timeout' => 300, // Timeout in seconds
        'retry_attempts' => 3, // Number of retry attempts on failure
        'max_concurrent_per_user' => 3, // Max concurrent generations per user
        'default_album' => null, // Default album for generated images
        'default_visibility' => 'public', // Default visibility for generated images
        'default_provider' => env('AI_DEFAULT_PROVIDER', 'mock'),
        'default_model' => env('AI_DEFAULT_MODEL', 'mock-model-v1'),
    ],

    /*
    |--------------------------------------------------------------------------
    | Cost Management
    |--------------------------------------------------------------------------
    |
    | Settings for managing generation costs and limits.
    |
    */

    'costs' => [
        'track_costs' => true, // Track generation costs
        'user_limits' => [
            'daily_cost_limit' => 5.00, // Max daily cost per user
            'monthly_cost_limit' => 50.00, // Max monthly cost per user
            'free_tier_daily_limit' => 3, // Free generations per day
        ],
        'billing' => [
            'currency' => 'USD',
            'decimal_places' => 4,
            'invoice_threshold' => 10.00, // Minimum amount to invoice
        ],
    ],

    /*
    |--------------------------------------------------------------------------
    | Content Moderation
    |--------------------------------------------------------------------------
    |
    | Settings for moderating generated content.
    |
    */

    'moderation' => [
        'enabled' => true,
        'auto_reject_nsfw' => true,
        'auto_reject_violence' => true,
        'review_flagged_content' => true,
        'blocked_keywords' => [
            // Add blocked keywords here
        ],
        'safety_checkers' => [
            'openai_moderation' => env('OPENAI_MODERATION_ENABLED', true),
            'aws_rekognition' => env('AWS_REKOGNITION_ENABLED', false),
        ],
    ],

    /*
    |--------------------------------------------------------------------------
    | Storage and Processing
    |--------------------------------------------------------------------------
    |
    | Settings for storing and processing generated images.
    |
    */

    'storage' => [
        'disk' => 'public', // Storage disk for generated images
        'path' => 'generated', // Path within the disk
        'generate_thumbnails' => true,
        'thumbnail_sizes' => [150, 300, 600],
        'compress_images' => true,
        'compression_quality' => 85,
    ],

    /*
    |--------------------------------------------------------------------------
    | Webhooks and Notifications
    |--------------------------------------------------------------------------
    |
    | Settings for webhooks and user notifications.
    |
    */

    'notifications' => [
        'notify_on_completion' => true,
        'notify_on_failure' => true,
        'email_notifications' => false,
        'push_notifications' => true,
        'webhook_endpoints' => [
            // Custom webhook endpoints
        ],
    ],

    /*
    |--------------------------------------------------------------------------
    | Analytics and Monitoring
    |--------------------------------------------------------------------------
    |
    | Settings for tracking generation analytics.
    |
    */

    'analytics' => [
        'track_usage' => true,
        'track_performance' => true,
        'track_costs' => true,
        'retention_days' => 365, // How long to keep analytics data
        'metrics' => [
            'generation_time',
            'success_rate',
            'popular_prompts',
            'model_usage',
            'cost_per_user',
        ],
    ],

    /*
    |--------------------------------------------------------------------------
    | Security and Privacy
    |--------------------------------------------------------------------------
    |
    | Security settings for AI generation.
    |
    */

    'security' => [
        'encrypt_prompts' => false, // Encrypt stored prompts
        'anonymize_analytics' => false, // Remove PII from analytics
        'audit_generations' => true, // Keep audit trail
        'rate_limiting' => [
            'enabled' => true,
            'requests_per_minute' => 10,
            'requests_per_hour' => 100,
            'requests_per_day' => 500,
        ],
    ],
];
