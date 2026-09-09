<?php

/**
 * Downtown Travel Air API (REST / OAuth2).
 *
 * Docs: https://documenter.getpostman.com/view/28465574/2s9Xy2PrwH
 *
 * Defaults from .env. Super admin can override under Admin → Integrations
 * (stored encrypted in `integrations`); merged values win over .env when the row is enabled.
 *
 * Auth: POST {sso}/oauth/token with Basic(client_id:client_secret)
 * and grant_type=password + username + password → access_token / refresh_token.
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
    'environment' => env('DOWNTOWN_TRAVEL_ENVIRONMENT', 'sandbox'),

    /*
    |--------------------------------------------------------------------------
    | OAuth client (Basic auth for token endpoint)
    |--------------------------------------------------------------------------
    */
    'client_id' => env('DOWNTOWN_TRAVEL_CLIENT_ID', ''),

    'client_secret' => env('DOWNTOWN_TRAVEL_CLIENT_SECRET', ''),

    /*
    |--------------------------------------------------------------------------
    | Resource-owner credentials (password grant)
    |--------------------------------------------------------------------------
    */
    'username' => env('DOWNTOWN_TRAVEL_USERNAME', ''),

    'password' => env('DOWNTOWN_TRAVEL_PASSWORD', ''),

    /*
    |--------------------------------------------------------------------------
    | HTTP
    |--------------------------------------------------------------------------
    */
    'timeout' => (int) env('DOWNTOWN_TRAVEL_TIMEOUT', 60),

    /*
    |--------------------------------------------------------------------------
    | Optional host overrides (no trailing slash)
    |--------------------------------------------------------------------------
    |
    | Defaults (sandbox):
    |   SSO  → https://sso.sandbox.thebestagent.pro
    |   Air  → https://air.sandbox.thebestagent.pro
    |
    | Production defaults omit ".sandbox" — override if Downtown gives different hosts.
    |
    */
    'sso_base_url_override' => env('DOWNTOWN_TRAVEL_SSO_BASE_URL'),

    'air_base_url_override' => env('DOWNTOWN_TRAVEL_AIR_BASE_URL'),
];
