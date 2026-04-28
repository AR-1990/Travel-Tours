<?php

return [
    /*
    |--------------------------------------------------------------------------
    | Firebase Credentials
    |--------------------------------------------------------------------------
    */
    'credentials' => [
        'file' => env('FIREBASE_CREDENTIALS')
            ? (str_starts_with(env('FIREBASE_CREDENTIALS'), '/')
                ? env('FIREBASE_CREDENTIALS')
                : storage_path('app/' . env('FIREBASE_CREDENTIALS')))
            : storage_path('app/firebase-credentials.json'),
    ],

    /*
    |--------------------------------------------------------------------------
    | Firebase Database URL
    |--------------------------------------------------------------------------
    */
    'database' => [
        'url' => env('FIREBASE_DATABASE_URL'),
    ],

    /*
    |--------------------------------------------------------------------------
    | Firebase Project ID
    |--------------------------------------------------------------------------
    */
    'project_id' => env('FIREBASE_PROJECT_ID'),
];
