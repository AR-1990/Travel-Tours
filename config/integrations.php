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
    ],
];
