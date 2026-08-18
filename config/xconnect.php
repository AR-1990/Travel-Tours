<?php

/**
 * Xconnect hotel API (Technoheaven / Rimo Global wholesaler stack).
 *
 * Auth: API Token in JSON body + whitelisted client IP.
 * Docs: https://documenter.getpostman.com/view/11578141/TVKBYJAE
 *
 * Note: The published Xconnect collection covers hotels only (not flights).
 * Defaults from .env; Admin → Integrations can override when enabled.
 */
return [
    'environment' => env('XCONNECT_ENVIRONMENT', 'sandbox'),

    /**
     * Access token provided by Technoheaven / Rimo during development.
     */
    'token' => env('XCONNECT_TOKEN', ''),

    /**
     * Required full API host (no trailing slash), e.g. https://api.example.com
     * There is no public default host in the Postman collection (uses {{baseurl}}).
     */
    'base_url' => env('XCONNECT_BASE_URL', ''),

    'timeout' => (int) env('XCONNECT_TIMEOUT', 90),

    'default_currency' => env('XCONNECT_CURRENCY', 'USD'),

    'default_nationality' => env('XCONNECT_NATIONALITY', 'india'),

    /**
     * Optional: override full API host (wins over base_url when set).
     */
    'base_url_override' => env('XCONNECT_BASE_URL_OVERRIDE', ''),
];
