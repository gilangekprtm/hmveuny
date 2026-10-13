<?php

$storagePath = sys_get_temp_dir().'/hmve-laravel-storage';
$bootstrapCachePath = $storagePath.'/bootstrap-cache';

putenv('LARAVEL_STORAGE_PATH='.$storagePath);
$_ENV['LARAVEL_STORAGE_PATH'] = $storagePath;
$_SERVER['LARAVEL_STORAGE_PATH'] = $storagePath;

foreach ([
    'framework/cache/data',
    'framework/sessions',
    'framework/views',
    'logs',
    'app/data',
    'bootstrap-cache',
] as $directory) {
    $path = $storagePath.DIRECTORY_SEPARATOR.$directory;

    if (! is_dir($path) && ! mkdir($path, 0755, true) && ! is_dir($path)) {
        throw new RuntimeException("Unable to create Vercel runtime directory [{$path}].");
    }
}

foreach ([
    'APP_PACKAGES_CACHE' => $bootstrapCachePath.'/packages.php',
    'APP_SERVICES_CACHE' => $bootstrapCachePath.'/services.php',
] as $key => $path) {
    putenv("{$key}={$path}");
    $_ENV[$key] = $path;
    $_SERVER[$key] = $path;
}

require __DIR__.'/../public/index.php';
