<?php

namespace App\Http\Controllers\Concerns;

use App\Models\FlightReservation;
use App\Services\Travelport\TravelportAirService;
use App\Support\FlightDisplay;
use Illuminate\Support\Facades\Auth;

trait RunsFlightWorkflow
{
    /**
     * @param  array<string, mixed>  $result
     * @param  array<string, mixed>  $input
     */
    protected function persistFlightBooking(array $result, array $input): FlightReservation
    {
        $search = session('travelport.flight_search.input', session('public.flight_search.input', []));
        $priceStore = session('travelport.flight_price', session('public.flight_price', []));
        $priceResult = is_array($priceStore) ? ($priceStore['result'] ?? null) : null;
        $solution = is_array($priceResult) ? ($priceResult['solutions'][0] ?? null) : null;

        $passenger = [
            'prefix' => (string) ($input['passenger_prefix'] ?? ($input['passengers'][0]['prefix'] ?? 'Mr')),
            'first' => (string) ($input['passenger_first'] ?? ($input['passengers'][0]['first'] ?? '')),
            'last' => (string) ($input['passenger_last'] ?? ($input['passengers'][0]['last'] ?? '')),
            'email' => (string) ($input['passenger_email'] ?? ($input['passengers'][0]['email'] ?? '')),
            'phone' => (string) ($input['passenger_phone'] ?? ($input['passengers'][0]['phone'] ?? '')),
            'dob' => (string) ($input['passenger_dob'] ?? ($input['passengers'][0]['dob'] ?? '')),
            'gender' => (string) ($input['passenger_gender'] ?? ($input['passengers'][0]['gender'] ?? 'M')),
        ];

        $carrier = is_array($solution) ? ($solution['plating_carrier'] ?? ($solution['segments'][0]['carrier'] ?? null)) : null;
        $journeys = is_array($solution) ? FlightDisplay::solutionJourneys($solution) : [];

        $user = Auth::user();
        $channel = 'public';
        if (method_exists($this, 'flightsRoutePrefix')) {
            $channel = match ($this->flightsRoutePrefix()) {
                'admin' => 'admin',
                'agent' => 'agent',
                'subagent' => 'subagent',
                default => 'public',
            };
        }

        $reservation = FlightReservation::query()->create([
            'tenant_id' => $user?->tenant_id,
            'user_id' => $user?->id,
            'channel' => $channel,
            'status' => FlightReservation::STATUS_RESERVED,
            'universal_locator' => $result['universal_locator'] ?? null,
            'air_reservation_locator' => $result['air_reservation_locator'] ?? ($result['provider_locator'] ?? null),
            'provider_locator' => $result['provider_locator'] ?? null,
            'origin' => strtoupper((string) ($search['origin'] ?? '')),
            'destination' => strtoupper((string) ($search['destination'] ?? '')),
            'departure_date' => $search['departure_date'] ?? null,
            'return_date' => $search['return_date'] ?? null,
            'adults' => (int) ($search['adults'] ?? 1),
            'carrier' => $carrier,
            'passenger_prefix' => $passenger['prefix'],
            'passenger_first' => $passenger['first'],
            'passenger_last' => $passenger['last'],
            'passenger_email' => $passenger['email'],
            'passenger_phone' => $passenger['phone'],
            'passenger_dob' => $passenger['dob'] !== '' ? $passenger['dob'] : null,
            'passenger_gender' => $passenger['gender'],
            'total_price' => is_array($solution) ? ($solution['total_price'] ?? null) : null,
            'base_price' => is_array($solution) ? ($solution['base_price'] ?? null) : null,
            'taxes' => is_array($solution) ? ($solution['taxes'] ?? null) : null,
            'fare_basis' => is_array($solution) ? ($solution['fare_basis'] ?? null) : null,
            'itinerary' => [
                'journeys' => array_map(static fn (array $j): array => [
                    'label' => $j['label'] ?? null,
                    'segments' => $j['segments'] ?? [],
                ], $journeys),
                'segments' => is_array($solution) ? ($solution['segments'] ?? []) : [],
            ],
            'price_snapshot' => is_array($priceResult) ? $priceResult : null,
            'raw_result' => $result,
            'booked_at' => now(),
        ]);

        $payload = [
            'id' => $reservation->id,
            'result' => $result,
            'input' => $input,
            'universal_locator' => $reservation->universal_locator,
            'air_reservation_locator' => $reservation->air_reservation_locator,
            'booked_at' => optional($reservation->booked_at)?->toIso8601String(),
        ];

        session([
            'travelport.last_booking' => [
                'universal_locator' => $payload['universal_locator'],
                'air_reservation_locator' => $payload['air_reservation_locator'],
                'reservation_id' => $reservation->id,
            ],
            'travelport.flight_booking' => $payload,
            'public.flight_booking' => $payload,
            'travelport.last_reservation_id' => $reservation->id,
            'public.last_reservation_id' => $reservation->id,
        ]);

        return $reservation;
    }

    /**
     * @return array<string, mixed>|null
     */
    protected function bookingSession(): ?array
    {
        $stored = session('travelport.flight_booking') ?? session('public.flight_booking');

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
    protected function runIssueTicketFlow(TravelportAirService $air, array $locators, ?FlightReservation $reservation = null): array
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
            'travelport.flight_ticket' => [
                'result' => $merged,
                'ticket_numbers' => $merged['ticket_numbers'],
                'ticketed_at' => now()->toIso8601String(),
            ],
            'public.flight_ticket' => [
                'result' => $merged,
                'ticket_numbers' => $merged['ticket_numbers'],
                'ticketed_at' => now()->toIso8601String(),
            ],
        ]);

        if ($reservation !== null && ! empty($merged['ticket_numbers'])) {
            $reservation->update([
                'status' => FlightReservation::STATUS_TICKETED,
                'ticket_numbers' => $merged['ticket_numbers'],
                'ticketed_at' => now(),
            ]);
        } elseif ($reservation !== null) {
            // Host accepted ticketing call but no numbers yet — keep reserved.
            $reservation->update([
                'ticket_numbers' => $merged['ticket_numbers'] ?? [],
            ]);
        }

        return $merged;
    }
}
