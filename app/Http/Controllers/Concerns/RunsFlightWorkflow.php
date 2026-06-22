<?php

namespace App\Http\Controllers\Concerns;

use App\Services\Travelport\TravelportAirService;

trait RunsFlightWorkflow
{
    /**
     * @param  array<string, mixed>  $result
     * @param  array<string, mixed>  $input
     */
    protected function persistFlightBooking(array $result, array $input): void
    {
        $payload = [
            'result' => $result,
            'input' => $input,
            'universal_locator' => $result['universal_locator'] ?? null,
            'air_reservation_locator' => $result['air_reservation_locator'] ?? null,
            'booked_at' => now()->toIso8601String(),
        ];

        session([
            'travelport.last_booking' => [
                'universal_locator' => $payload['universal_locator'],
                'air_reservation_locator' => $payload['air_reservation_locator'],
            ],
            'public.flight_booking' => $payload,
        ]);
    }

    /**
     * @return array<string, mixed>|null
     */
    protected function bookingSession(): ?array
    {
        $stored = session('public.flight_booking');

        return is_array($stored) ? $stored : null;
    }

    /**
     * @return array<string, string>
     */
    protected function bookingLocatorParams(?array $booking = null): array
    {
        $booking ??= $this->bookingSession();
        if (! is_array($booking)) {
            return [];
        }

        $params = [];
        if (! empty($booking['universal_locator'])) {
            $params['universal_locator'] = (string) $booking['universal_locator'];
        }
        if (! empty($booking['air_reservation_locator'])) {
            $params['air_reservation_locator'] = (string) $booking['air_reservation_locator'];
        }

        $legacy = session('travelport.last_booking', []);
        if ($params === [] && is_array($legacy)) {
            if (! empty($legacy['universal_locator'])) {
                $params['universal_locator'] = (string) $legacy['universal_locator'];
            }
            if (! empty($legacy['air_reservation_locator'])) {
                $params['air_reservation_locator'] = (string) $legacy['air_reservation_locator'];
            }
        }

        return $params;
    }

    /**
     * @param  array<string, string>  $locators
     * @return array<string, mixed>
     */
    protected function runIssueTicketFlow(TravelportAirService $air, array $locators): array
    {
        $ticketResult = $air->execute('air_ticketing', $locators);
        if (! ($ticketResult['ok'] ?? false)) {
            return $ticketResult;
        }

        $docResult = $air->execute('air_retrieve_document', $locators);

        $merged = array_merge($ticketResult, [
            'document_ok' => $docResult['ok'] ?? false,
            'document_message' => $docResult['message'] ?? null,
            'ticket_numbers' => $docResult['ticket_numbers'] ?? [],
        ]);

        session([
            'public.flight_ticket' => [
                'result' => $merged,
                'ticket_numbers' => $merged['ticket_numbers'],
                'ticketed_at' => now()->toIso8601String(),
            ],
        ]);

        return $merged;
    }
}
