<?php

/**
 * Registered integrations shown under Admin → Integrations.
 *
 * Add a new slug here when you wire another provider, then implement its
 * edit/update routes and (optional) row in the `integrations` table.
 */
return [
    'catalog' => [
        'travelport' => [
            'name' => 'Travelport Universal API',
            'description' => 'SOAP Universal API: connectivity, Air shopping, availability, booking, ticketing.',
            'coming_soon' => false,
        ],
        'sunspring' => [
            'name' => 'SunSpring Airline API',
            'description' => 'REST Airline APIs: authorize token, flight search, price, book, ticket, cancel.',
            'coming_soon' => false,
        ],
        'xconnect' => [
            'name' => 'Xconnect Hotel API',
            'description' => 'Technoheaven/Rimo hotel wholesale: Availability → PreBook → Book → Detail → Cancel. (Published collection is hotels only.)',
            'coming_soon' => false,
        ],
        'downtown_travel' => [
            'name' => 'Downtown Travel Air API',
            'description' => 'REST Air API (OAuth2): Get Token → Search → Preliminary Booking → Book → Issue / Cancel / Void / Refund.',
            'coming_soon' => false,
        ],
        'downtown_travel_hotels' => [
            'name' => 'Downtown Travel Hotels API',
            'description' => 'Hotels API v2 (OAuth2): Check Availability → Offers → Validate → Create/Book Order → Cancel. Docs: dtt-hotels.readme.io',
            'coming_soon' => false,
        ],
    ],
];
