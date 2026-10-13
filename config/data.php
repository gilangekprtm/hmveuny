<?php

return [
    'driver' => env('JSON_DATA_DRIVER', 'local'),
    'path' => env('JSON_DATA_PATH', storage_path('app/data')),
];
