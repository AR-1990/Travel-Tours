<?php

/**
 * Travelport Universal API (SOAP) — foundation config.
 *
 * Defaults from .env. Super admin can override most values under Admin → Integrations
 * (stored encrypted in `integrations`); merged values win over .env when the row is enabled.
 *
 * Step 1 uses SystemService Ping only. Later steps add AirService, etc.
 *
 * @see https://support.travelport.com/webhelp/uapi/Content/Getting_Started/Sending_Requests/testing_application_connectivity.htm
 */
return [
    /*
    |--------------------------------------------------------------------------
    | Region & environment
    |--------------------------------------------------------------------------
    |
    | region: emea | americas | apac
    | environment: pp (pre-production) | production
    |
    */
    'region' => env('TRAVELPORT_REGION', 'emea'),

    'environment' => env('TRAVELPORT_ENVIRONMENT', 'pp'),

    /*
    |--------------------------------------------------------------------------
    | Credentials (from Travelport)
    |--------------------------------------------------------------------------
    */
    'username' => env('TRAVELPORT_USERNAME', ''),

    'password' => env('TRAVELPORT_PASSWORD', ''),

    /*
    |--------------------------------------------------------------------------
    | Branch / PCC (used in later Air / Booking calls)
    |--------------------------------------------------------------------------
    */
    'branch' => env('TRAVELPORT_BRANCH', ''),

    'gds' => env('TRAVELPORT_GDS', ''),

    'target_branch' => env('TRAVELPORT_TARGET_BRANCH', ''),

    'origin_application' => env('TRAVELPORT_ORIGIN_APPLICATION', 'UAPI'),

    /*
    |--------------------------------------------------------------------------
    | SOAP schema major version (system_v* + common_v* for Ping; air_v* later)
    |--------------------------------------------------------------------------
    |
    | Ping uses System.xsd + Common.xsd — not universal_v*. If Ping faults, try 48–52.
    |
    */
    'schema_major_version' => (int) env('TRAVELPORT_SCHEMA_MAJOR_VERSION', 52),

    'timeout' => (int) env('TRAVELPORT_TIMEOUT', 60),

    /*
    |--------------------------------------------------------------------------
    | Optional: override full base host (no trailing slash)
    |--------------------------------------------------------------------------
    |
    | Example: https://emea.universal-api.pp.travelport.com
    | Leave null to build from region + environment.
    |
    */
    'base_url_override' => env('TRAVELPORT_BASE_URL'),
];
