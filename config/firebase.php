<?php

return [
    /*
    |--------------------------------------------------------------------------
    | Firebase Project
    |--------------------------------------------------------------------------
    */
    'project_id' => env('FIREBASE_PROJECT_ID'),

    /*
    |--------------------------------------------------------------------------
    | Service Account Credentials
    |--------------------------------------------------------------------------
    | Provide either a file path or inline JSON.
    */
    'credentials' => [
        'path' => env('FIREBASE_CREDENTIALS'),
        'json' => env('FIREBASE_CREDENTIALS_JSON'),
    ],

    /*
    |--------------------------------------------------------------------------
    | Optional Defaults
    |--------------------------------------------------------------------------
    */
    'database_url' => env('FIREBASE_DATABASE_URL'),
    'storage_bucket' => env('FIREBASE_STORAGE_BUCKET'),
];
