<?php

return [
    'class_namespace' => 'App\\Http\\Livewire',
    'view_path' => resource_path('views/livewire'),
    'layout' => 'layouts.app',
    'asset_url' => null,
    'app_url' => null,
    'middleware_group' => 'web',
    'temporary_file_upload' => [
        'disk' => null,
        'rules' => 'file|mimes:png,gif,jpg,jpeg,svg,webp|max:10240', // 10MB max, (a 4mb photo = 4 * 1024).
        'chunks' => 'livewire-uploads',
        'chunk_size' => 1024 * 1024 * 4, // 4MB per chunk.
        'max_upload_time' => 60 * 60 * 24, // 24 hours.
    ],
    'manifest_path' => public_path('livewire-manifest.json'),
    'back_button_cache' => true,
];
