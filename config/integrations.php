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
        // Example placeholder for a future integration:
        // 'payments' => [
        //     'name' => 'Payments',
        //     'description' => 'Card capture and refunds.',
        //     'coming_soon' => true,
        // ],
    ],
];
