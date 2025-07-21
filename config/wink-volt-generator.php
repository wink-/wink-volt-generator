<?php

return [
    /*
    |--------------------------------------------------------------------------
    | Default Path
    |--------------------------------------------------------------------------
    |
    | This value determines the default path where Volt components will be
    | generated. You can customize this to match your project structure.
    |
    */
    'path' => 'app/Livewire',

    /*
    |--------------------------------------------------------------------------
    | DataTable Configuration
    |--------------------------------------------------------------------------
    |
    | Configuration options for DataTable component generation.
    |
    */
    'datatable' => [
        /*
        | Columns to exclude from generated DataTables. These are typically
        | internal fields that shouldn't be displayed to users.
        */
        'exclude_columns' => [
            'id',
            'password',
            'remember_token',
            'email_verified_at',
            'created_at',
            'updated_at',
            'deleted_at',
        ],

        /*
        | The base layout view that generated components should extend.
        | Leave null to generate components without a layout.
        */
        'base_view' => null,
    ],

    /*
    |--------------------------------------------------------------------------
    | Chart Configuration
    |--------------------------------------------------------------------------
    |
    | Configuration options for Chart component generation.
    |
    */
    'chart' => [
        /*
        | Default chart type when not specified in the command.
        */
        'default_type' => 'bar',

        /*
        | Default color palette for chart datasets. These colors will be
        | used in sequence for chart elements.
        */
        'colors' => [
            '#3B82F6', // Blue
            '#EF4444', // Red
            '#10B981', // Green
            '#F59E0B', // Yellow
            '#8B5CF6', // Purple
            '#F97316', // Orange
            '#06B6D4', // Cyan
            '#84CC16', // Lime
            '#EC4899', // Pink
            '#6B7280', // Gray
        ],
    ],

    /*
    |--------------------------------------------------------------------------
    | Form Configuration
    |--------------------------------------------------------------------------
    |
    | Configuration options for Form component generation.
    |
    */
    'form' => [
        /*
        | Columns to exclude from generated forms. These are typically
        | internal fields that shouldn't be editable by users.
        */
        'exclude_columns' => [
            'id',
            'password',
            'remember_token',
            'email_verified_at',
            'created_at',
            'updated_at',
            'deleted_at',
        ],

        /*
        | Default form action when not specified in the command.
        */
        'default_action' => 'create',

        /*
        | Field type mappings for automatic form field generation.
        | These can be overridden by column name patterns.
        */
        'field_types' => [
            'email' => 'email',
            'password' => 'password',
            'phone' => 'tel',
            'url' => 'url',
            'website' => 'url',
        ],
    ],

    /*
    |--------------------------------------------------------------------------
    | Card Configuration
    |--------------------------------------------------------------------------
    |
    | Configuration options for Card component generation.
    |
    */
    'card' => [
        /*
        | Columns to exclude from generated cards. These are typically
        | internal fields that shouldn't be displayed to users.
        */
        'exclude_columns' => [
            'id',
            'password',
            'remember_token',
            'email_verified_at',
            'created_at',
            'updated_at',
            'deleted_at',
        ],

        /*
        | Default layout when not specified in the command.
        */
        'default_layout' => 'grid',

        /*
        | Default number of columns for grid layout.
        */
        'default_columns' => 3,

        /*
        | Default number of items per page for pagination.
        */
        'per_page' => 12,
    ],

    /*
    |--------------------------------------------------------------------------
    | Modal Configuration
    |--------------------------------------------------------------------------
    |
    | Configuration options for Modal component generation.
    |
    */
    'modal' => [
        /*
        | Columns to exclude from generated modals. These are typically
        | internal fields that shouldn't be displayed or edited.
        */
        'exclude_columns' => [
            'id',
            'password',
            'remember_token',
            'email_verified_at',
            'created_at',
            'updated_at',
            'deleted_at',
        ],

        /*
        | Default modal type when not specified in the command.
        */
        'default_type' => 'crud',

        /*
        | Modal size configurations for different types.
        */
        'sizes' => [
            'confirm' => 'sm',
            'view' => 'lg',
            'crud' => 'md',
            'custom' => 'md',
        ],
    ],

    /*
    |--------------------------------------------------------------------------
    | Search Configuration
    |--------------------------------------------------------------------------
    |
    | Configuration options for Search/Filter component generation.
    |
    */
    'search' => [
        /*
        | Columns to exclude from search/filter generation. These are typically
        | internal fields that shouldn't be searchable or filterable.
        */
        'exclude_columns' => [
            'password',
            'remember_token',
            'email_verified_at',
        ],

        /*
        | Default search debounce delay in milliseconds.
        */
        'debounce_delay' => 300,

        /*
        | Default number of results per page.
        */
        'per_page' => 10,

        /*
        | Fields that should default to being searchable if not specified.
        */
        'default_searchable_fields' => [
            'name',
            'title',
            'description',
            'email',
        ],
    ],
];