<?php

$storagePath = sys_get_temp_dir().'/hmve-laravel-storage';

putenv('LARAVEL_STORAGE_PATH='.$storagePath);
$_ENV['LARAVEL_STORAGE_PATH'] = $storagePath;
$_SERVER['LARAVEL_STORAGE_PATH'] = $storagePath;

foreach (['framework/cache/data', 'framework/sessions', 'framework/views', 'logs', 'app/data'] as $directory) {
    $path = $storagePath.DIRECTORY_SEPARATOR.$directory;

    if (! is_dir($path) && ! mkdir($path, 0755, true) && ! is_dir($path)) {
        throw new RuntimeException("Unable to create Vercel runtime directory [{$path}].");
    }
}

require __DIR__.'/../public/index.php';
