<?php

return [
    'class_namespace' => 'App\\Livewire',
    'view_path' => resource_path('views/livewire'),
    'layout' => 'layouts.app',
    'asset_url' => null,
    'app_url' => null,
    'middleware_group' => 'web',
    'temporary_file_upload' => [
        'disk' => null,
        'rules' => 'file|mimes:png,gif,jpg,jpeg,svg,webp|max:10240',
        'chunks' => 'livewire-uploads',
        'chunk_size' => 1024 * 1024 * 4,
        'max_upload_time' => 60 * 60 * 24,
    ],
    'manifest_path' => public_path('livewire-manifest.json'),
    'back_button_cache' => true,
];
