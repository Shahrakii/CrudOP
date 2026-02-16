<?php

return [
    /*
    |--------------------------------------------------------------------------
    | Crudly Configuration
    |--------------------------------------------------------------------------
    |
    | Configuration for the Crudly CRUD generator package
    |
    */

    // Route prefix for Crudly endpoints
    'route_prefix' => env('CRUDLY_ROUTE_PREFIX', 'admin'),

    // Middleware to apply to Crudly routes
    'middleware' => env('CRUDLY_MIDDLEWARE', ['web', 'auth']),

    // Global filters - columns to exclude from all CRUD operations
    'global_filters' => [
        'id',
        'created_at',
        'updated_at',
        'deleted_at',
    ],

    // Table-specific column filters
    'table_filters' => [
        // 'users' => ['password', 'remember_token', 'api_token'],
    ],

    // Tables to exclude from generation
    'exclude_tables' => [
        'migrations',
        'failed_jobs',
        'password_resets',
        'password_reset_tokens',
        'personal_access_tokens',
        'sessions',
    ],

    // Pagination
    'pagination' => env('CRUDLY_PAGINATION', 15),

    // CSS Framework (tailwind or bootstrap)
    'css_framework' => env('CRUDLY_CSS_FRAMEWORK', 'tailwind'),

    // View paths
    'views' => [
        'index' => 'crudly::crud.index',
        'create' => 'crudly::crud.create',
        'edit' => 'crudly::crud.edit',
        'show' => 'crudly::crud.show',
    ],

    // Model namespace
    'model_namespace' => 'App\\Models',

    // Controller namespace
    'controller_namespace' => 'App\\Http\\Controllers',

    // Generate routes automatically
    'auto_routes' => env('CRUDLY_AUTO_ROUTES', false),

    // Generate factories
    'generate_factories' => env('CRUDLY_GENERATE_FACTORIES', true),

    // Generate migrations
    'generate_migrations' => env('CRUDLY_GENERATE_MIGRATIONS', false),
];
