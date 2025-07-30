<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Config Cache TTL
    |--------------------------------------------------------------------------
    |
    | This value determines how long configuration values should be cached
    | in seconds. A higher value reduces database queries but may result
    | in outdated configuration being served.
    |
    */

    'config_cache_ttl' => env('CONFIG_CACHE_TTL', 3600), // 1 hour

    /*
    |--------------------------------------------------------------------------
    | View Cache
    |--------------------------------------------------------------------------
    |
    | When enabled, compiled views will be cached for better performance.
    | This should be enabled in production environments.
    |
    */

    'view_cache_enabled' => env('VIEW_CACHE_ENABLED', true),

    /*
    |--------------------------------------------------------------------------
    | Route Cache
    |--------------------------------------------------------------------------
    |
    | When enabled, routes will be cached for better performance.
    | This should be enabled in production environments.
    |
    */

    'route_cache_enabled' => env('ROUTE_CACHE_ENABLED', true),

    /*
    |--------------------------------------------------------------------------
    | Query Optimization
    |--------------------------------------------------------------------------
    |
    | Configuration for database query optimization
    |
    */

    'query_optimization' => [
        'enable_query_cache' => env('ENABLE_QUERY_CACHE', true),
        'default_chunk_size' => env('DEFAULT_CHUNK_SIZE', 1000),
        'max_execution_time' => env('MAX_EXECUTION_TIME', 30),
    ],

    /*
    |--------------------------------------------------------------------------
    | Cache Prefixes
    |--------------------------------------------------------------------------
    |
    | Prefixes for different types of cached data
    |
    */

    'cache_prefixes' => [
        'dropdown_options' => 'dropdown_',
        'user_permissions' => 'user_perm_',
        'system_config' => 'sys_config_',
        'file_metadata' => 'file_meta_',
    ],

    /*
    |--------------------------------------------------------------------------
    | Performance Monitoring
    |--------------------------------------------------------------------------
    |
    | Configuration for performance monitoring and logging
    |
    */

    'performance' => [
        'log_slow_queries' => env('LOG_SLOW_QUERIES', true),
        'slow_query_threshold' => env('SLOW_QUERY_THRESHOLD', 100), // milliseconds
        'enable_debug_bar' => env('ENABLE_DEBUG_BAR', false),
        'monitor_memory_usage' => env('MONITOR_MEMORY_USAGE', true),
    ],

];
