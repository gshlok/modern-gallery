<?php

return [
    /*
    |--------------------------------------------------------------------------
    | Default Vector Search Provider
    |--------------------------------------------------------------------------
    |
    | This option controls the default vector search provider that will be used
    | by the vector search service. You may change this value to any of the
    | providers defined in the "providers" array below.
    |
    */

    'default_provider' => env('VECTOR_PROVIDER', 'mock'),

    /*
    |--------------------------------------------------------------------------
    | Vector Search Providers
    |--------------------------------------------------------------------------
    |
    | Here you may configure the vector search providers for your application.
    | Each provider can have its own configuration and credentials.
    |
    */

    'providers' => [
        'mock' => [
            'model' => 'mock-embedding-v1',
            'dimensions' => 512,
            'similarity_threshold' => 0.7,
        ],

        'openai' => [
            'api_key' => env('OPENAI_API_KEY'),
            'model' => 'text-embedding-ada-002',
            'dimensions' => 1536,
            'max_tokens' => 8191,
            'similarity_threshold' => 0.8,
            'rate_limit' => [
                'requests_per_minute' => 1000,
                'tokens_per_minute' => 1000000,
            ],
        ],

        'pinecone' => [
            'api_key' => env('PINECONE_API_KEY'),
            'environment' => env('PINECONE_ENVIRONMENT'),
            'index_name' => env('PINECONE_INDEX_NAME', 'gallery-embeddings'),
            'dimensions' => 1536,
            'metric' => 'cosine',
            'similarity_threshold' => 0.8,
        ],

        'weaviate' => [
            'endpoint' => env('WEAVIATE_ENDPOINT'),
            'api_key' => env('WEAVIATE_API_KEY'),
            'class_name' => 'GalleryImage',
            'vectorizer' => 'clip',
            'dimensions' => 512,
            'similarity_threshold' => 0.8,
        ],

        'pgvector' => [
            'connection' => 'pgsql', // PostgreSQL connection
            'table' => 'vector_embeddings',
            'vector_column' => 'vector',
            'dimensions' => 1536,
            'index_type' => 'ivfflat', // or 'hnsw'
            'similarity_function' => 'cosine', // 'cosine', 'l2', 'inner_product'
            'similarity_threshold' => 0.8,
        ],

        'clip_local' => [
            'model_path' => storage_path('models/clip'),
            'model_name' => 'ViT-B/32',
            'dimensions' => 512,
            'device' => 'cpu', // or 'cuda' if GPU available
            'similarity_threshold' => 0.8,
        ],
    ],

    /*
    |--------------------------------------------------------------------------
    | Embedding Generation Settings
    |--------------------------------------------------------------------------
    |
    | These settings control how embeddings are generated and processed.
    |
    */

    'embedding' => [
        'batch_size' => 10, // Number of images to process in batch
        'queue' => 'vector-processing', // Queue for background processing
        'timeout' => 300, // Timeout in seconds for embedding generation
        'retry_attempts' => 3, // Number of retry attempts on failure
        'cache_ttl' => 86400, // Cache TTL in seconds (24 hours)
    ],

    /*
    |--------------------------------------------------------------------------
    | Search Configuration
    |--------------------------------------------------------------------------
    |
    | Settings for search operations and result formatting.
    |
    */

    'search' => [
        'default_limit' => 20,
        'max_limit' => 100,
        'default_threshold' => 0.7,
        'enable_fallback' => true, // Fallback to text search if vector search fails
        'result_cache_ttl' => 300, // Cache search results for 5 minutes
        'highlight_matches' => true,
    ],

    /*
    |--------------------------------------------------------------------------
    | Performance and Optimization
    |--------------------------------------------------------------------------
    |
    | Settings for optimizing vector search performance.
    |
    */

    'optimization' => [
        'enable_indexing' => true, // Enable vector indexing for faster searches
        'index_refresh_interval' => 3600, // Refresh indexes every hour
        'precompute_similarities' => false, // Precompute similarity matrices
        'enable_clustering' => false, // Enable vector clustering for faster search
        'clustering_algorithm' => 'kmeans', // 'kmeans', 'hierarchical'
        'num_clusters' => 100,
    ],

    /*
    |--------------------------------------------------------------------------
    | Monitoring and Logging
    |--------------------------------------------------------------------------
    |
    | Configuration for monitoring vector search operations.
    |
    */

    'monitoring' => [
        'log_searches' => env('VECTOR_LOG_SEARCHES', false),
        'log_level' => 'info', // 'debug', 'info', 'warning', 'error'
        'metrics_enabled' => true,
        'slow_query_threshold' => 5.0, // Log queries taking longer than 5 seconds
        'track_analytics' => true, // Track search analytics
    ],

    /*
    |--------------------------------------------------------------------------
    | Security and Privacy
    |--------------------------------------------------------------------------
    |
    | Security settings for vector operations.
    |
    */

    'security' => [
        'encrypt_vectors' => false, // Encrypt stored vectors (impacts performance)
        'anonymize_queries' => false, // Remove PII from search queries
        'access_control' => true, // Enforce access control on searches
        'rate_limiting' => [
            'enabled' => true,
            'requests_per_minute' => 60,
            'requests_per_hour' => 1000,
        ],
    ],
];
