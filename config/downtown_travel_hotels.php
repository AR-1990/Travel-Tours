<?php

/**
 * Downtown Travel Hotels API v2 (REST / OAuth2).
 *
 * Docs: https://dtt-hotels.readme.io/reference/workflow
 *
 * Defaults from .env. Super admin can override under Admin → Integrations
 * (stored encrypted in `integrations`); merged values win over .env when the row is enabled.
 *
 * Auth: POST {sso}/oauth/token with Basic(client_id:client_secret)
 * and grant_type=password + username + password → access_token / refresh_token.
 *
 * Core flow: Check Availability → (Property Details) → Get Offers → Validate Offer
 * → Create Order → Book Order → Get Order Details / Cancel.
 */
return [
    'environment' => env('DOWNTOWN_TRAVEL_HOTELS_ENVIRONMENT', 'sandbox'),

    'client_id' => env('DOWNTOWN_TRAVEL_HOTELS_CLIENT_ID', ''),

    'client_secret' => env('DOWNTOWN_TRAVEL_HOTELS_CLIENT_SECRET', ''),

    'username' => env('DOWNTOWN_TRAVEL_HOTELS_USERNAME', ''),

    'password' => env('DOWNTOWN_TRAVEL_HOTELS_PASSWORD', ''),

    'timeout' => (int) env('DOWNTOWN_TRAVEL_HOTELS_TIMEOUT', 90),

    /*
    | Defaults (sandbox):
    |   SSO    → https://sso.sandbox.thebestagent.pro
    |   Hotels → https://hotels.sandbox.thebestagent.pro
    */
    'sso_base_url_override' => env('DOWNTOWN_TRAVEL_HOTELS_SSO_BASE_URL'),

    'hotels_base_url_override' => env('DOWNTOWN_TRAVEL_HOTELS_BASE_URL'),
];
