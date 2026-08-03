<?php

/**
 * SunSpring Airline API (REST) — foundation config.
 *
 * Defaults from .env. Super admin can override under Admin → Integrations
 * (stored encrypted in `integrations`); merged values win over .env when the row is enabled.
 *
 * Docs / sandbox: https://sandbox.sunspring.ae  (Swagger: Files/swagger.json)
 */
return [
    /*
    |--------------------------------------------------------------------------
    | Environment
    |--------------------------------------------------------------------------
    |
    | sandbox | production
    |
    */
    'environment' => env('SUNSPRING_ENVIRONMENT', 'sandbox'),

    /*
    |--------------------------------------------------------------------------
    | Credentials
    |--------------------------------------------------------------------------
    */
    'username' => env('SUNSPRING_USERNAME', ''),

    'password' => env('SUNSPRING_PASSWORD', ''),

    /*
    |--------------------------------------------------------------------------
    | Optional agency / office identifiers (if SunSpring assigned them)
    |--------------------------------------------------------------------------
    */
    'agency_code' => env('SUNSPRING_AGENCY_CODE', ''),

    'office_id' => env('SUNSPRING_OFFICE_ID', ''),

    /*
    |--------------------------------------------------------------------------
    | HTTP
    |--------------------------------------------------------------------------
    */
    'timeout' => (int) env('SUNSPRING_TIMEOUT', 60),

    /*
    |--------------------------------------------------------------------------
    | Optional: override full API host (no trailing slash)
    |--------------------------------------------------------------------------
    |
    | Defaults:
    |   sandbox    → https://sandbox.sunspring.ae
    |   production → https://api.sunspring.ae
    |
    */
    'base_url_override' => env('SUNSPRING_BASE_URL'),
];
