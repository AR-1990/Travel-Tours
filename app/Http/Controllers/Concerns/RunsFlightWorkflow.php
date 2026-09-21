<?php

namespace App\Http\Controllers\Concerns;

use App\Models\FlightReservation;
use App\Services\DowntownTravel\DowntownTravelAirService;
use App\Services\SunSpring\SunSpringAirService;
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
            'gds_version' => $result['version'] ?? null,
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
        if ($reservation !== null && $reservation->isDowntownTravel()) {
            return $this->runDowntownTicketFlow($reservation);
        }

        if ($reservation !== null && $reservation->isSunSpring()) {
            return $this->runSunSpringTicketFlow($reservation);
        }

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

    /**
     * Retrieve Universal Record from Travelport and sync local reservation.
     *
     * @return array<string, mixed>
     */
    protected function runRetrieveUniversalRecordFlow(TravelportAirService $air, FlightReservation $reservation): array
    {
        if ($reservation->isDowntownTravel()) {
            return $this->runDowntownOrderRefreshFlow($reservation);
        }

        if ($reservation->isSunSpring()) {
            return $this->runSunSpringTicketInfoFlow($reservation);
        }

        $locator = trim((string) ($reservation->universal_locator ?? ''));
        if ($locator === '') {
            return [
                'ok' => false,
                'message' => 'No Universal Record locator on this reservation.',
            ];
        }

        $result = $air->execute('universal_record_retrieve', [
            'universal_locator' => $locator,
        ]);

        if (! ($result['ok'] ?? false)) {
            return $result;
        }

        $this->applyUniversalRecordToReservation($reservation, $result);

        session([
            'travelport.ur_retrieve' => [
                'reservation_id' => $reservation->id,
                'result' => $result,
                'retrieved_at' => now()->toIso8601String(),
            ],
            'public.ur_retrieve' => [
                'reservation_id' => $reservation->id,
                'result' => $result,
                'retrieved_at' => now()->toIso8601String(),
            ],
        ]);

        return $result;
    }

    /**
     * Cancel Universal Record in GDS (retrieve version first), then mark local row cancelled.
     *
     * @return array<string, mixed>
     */
    protected function runCancelReservationFlow(TravelportAirService $air, FlightReservation $reservation): array
    {
        if ($reservation->status === FlightReservation::STATUS_CANCELLED) {
            return [
                'ok' => true,
                'message' => 'Reservation is already cancelled.',
                'cancelled' => true,
            ];
        }

        if ($reservation->isDowntownTravel()) {
            return $this->runDowntownCancelFlow($reservation);
        }

        if ($reservation->isSunSpring()) {
            return $this->runSunSpringCancelFlow($reservation);
        }

        if ($reservation->status === FlightReservation::STATUS_TICKETED) {
            return [
                'ok' => false,
                'message' => 'This reservation is ticketed. Void or refund the ticket before cancelling the PNR.',
            ];
        }

        $locator = trim((string) ($reservation->universal_locator ?? ''));
        if ($locator === '') {
            return [
                'ok' => false,
                'message' => 'No Universal Record locator on this reservation.',
            ];
        }

        $version = (string) ($reservation->gds_version ?? '');
        $retrieve = $air->execute('universal_record_retrieve', [
            'universal_locator' => $locator,
        ]);

        if ($retrieve['ok'] ?? false) {
            $this->applyUniversalRecordToReservation($reservation, $retrieve);
            $reservation->refresh();
            $version = (string) ($retrieve['version'] ?? $reservation->gds_version ?? $version);
            if (! empty($retrieve['cancelled'])) {
                $reservation->update([
                    'status' => FlightReservation::STATUS_CANCELLED,
                    'cancelled_at' => $reservation->cancelled_at ?? now(),
                ]);

                return array_merge($retrieve, [
                    'ok' => true,
                    'message' => 'Universal Record is already cancelled in the GDS. Local file updated.',
                    'cancelled' => true,
                ]);
            }
        }

        $cancel = $air->execute('universal_record_cancel', [
            'universal_locator' => $locator,
            'version' => $version !== '' ? $version : '0',
        ]);

        if (! ($cancel['ok'] ?? false)) {
            return $cancel;
        }

        $reservation->update([
            'status' => FlightReservation::STATUS_CANCELLED,
            'cancelled_at' => now(),
            'gds_snapshot' => array_merge(
                is_array($reservation->gds_snapshot) ? $reservation->gds_snapshot : [],
                ['cancel' => $cancel, 'cancelled_at' => now()->toIso8601String()]
            ),
        ]);

        return array_merge($cancel, ['cancelled' => true]);
    }

    /**
     * @param  array<string, mixed>  $result
     */
    protected function applyUniversalRecordToReservation(FlightReservation $reservation, array $result): void
    {
        $updates = [
            'gds_version' => $result['version'] ?? $reservation->gds_version,
            'gds_snapshot' => [
                'retrieved_at' => now()->toIso8601String(),
                'ur_status' => $result['ur_status'] ?? null,
                'version' => $result['version'] ?? null,
                'segments' => $result['segments'] ?? [],
                'passengers' => $result['passengers'] ?? [],
                'message' => $result['message'] ?? null,
            ],
        ];

        if (! empty($result['universal_locator'])) {
            $updates['universal_locator'] = $result['universal_locator'];
        }
        if (! empty($result['air_reservation_locator'])) {
            $updates['air_reservation_locator'] = $result['air_reservation_locator'];
        }
        if (! empty($result['provider_locator'])) {
            $updates['provider_locator'] = $result['provider_locator'];
        }

        $segments = $result['segments'] ?? [];
        if (is_array($segments) && $segments !== []) {
            $updates['itinerary'] = [
                'journeys' => [[
                    'label' => 'Retrieved itinerary',
                    'segments' => $segments,
                ]],
                'segments' => $segments,
            ];
            $firstCarrier = $segments[0]['carrier'] ?? null;
            if ($firstCarrier) {
                $updates['carrier'] = $firstCarrier;
            }
        }

        if (! empty($result['cancelled'])) {
            $updates['status'] = FlightReservation::STATUS_CANCELLED;
            $updates['cancelled_at'] = $reservation->cancelled_at ?? now();
        }

        $reservation->update($updates);
    }

    protected function runSunSpringTicketFlow(FlightReservation $reservation): array
    {
        $reference = (int) ($reservation->provider_locator ?: $reservation->universal_locator);
        $result = app(SunSpringAirService::class)->issueTicket(['reference' => $reference]);

        session([
            'travelport.flight_ticket' => $result,
            'public.flight_ticket' => $result,
        ]);

        if ($result['ok'] ?? false) {
            $reservation->forceFill([
                'status' => FlightReservation::STATUS_TICKETED,
                'ticket_numbers' => $result['ticket_numbers'] ?? [],
                'ticketed_at' => now(),
                'raw_result' => array_merge((array) $reservation->raw_result, [
                    'ticket' => $result,
                    'pnr' => $result['pnr'] ?? null,
                    'pnrs' => $result['pnrs'] ?? [],
                ]),
            ])->save();
        }

        return $result;
    }

    protected function runSunSpringTicketInfoFlow(FlightReservation $reservation): array
    {
        $reference = (string) ($reservation->provider_locator ?: $reservation->universal_locator);
        if ($reference === '') {
            return ['ok' => false, 'message' => 'No SunSpring booking reference on this reservation.'];
        }

        $result = app(SunSpringAirService::class)->ticketInfo(['reference' => $reference]);
        if ($result['ok'] ?? false) {
            $reservation->forceFill([
                'gds_snapshot' => array_merge(
                    is_array($reservation->gds_snapshot) ? $reservation->gds_snapshot : [],
                    ['ticket_info' => $result, 'retrieved_at' => now()->toIso8601String()]
                ),
                'raw_result' => array_merge((array) $reservation->raw_result, ['ticket_info' => $result]),
            ])->save();
        }

        return [
            'ok' => $result['ok'] ?? false,
            'message' => $result['message'] ?? (($result['ok'] ?? false) ? 'SunSpring ticket info retrieved.' : 'Retrieve failed.'),
            'provider' => 'sunspring',
            'raw' => $result,
        ];
    }

    protected function runSunSpringCancelFlow(FlightReservation $reservation): array
    {
        $air = app(SunSpringAirService::class);
        $reference = (string) ($reservation->provider_locator ?: $reservation->universal_locator);
        if ($reference === '') {
            return ['ok' => false, 'message' => 'No SunSpring booking reference on this reservation.'];
        }

        $tickets = is_array($reservation->ticket_numbers) ? array_values($reservation->ticket_numbers) : [];
        $pnrs = $this->sunSpringPnrsFromReservation($reservation, $air);

        // If PNR was never stored locally, fetch TicketInfo and retry extract.
        if ($pnrs === [] && $reference !== '') {
            $info = $air->ticketInfo(['reference' => $reference]);
            if ($info['ok'] ?? false) {
                $payload = is_array($info['data'] ?? null) ? $info['data'] : (is_array($info) ? $info : []);
                $pnrs = $air->extractPnrsFromPayload($payload);
                $reservation->forceFill([
                    'raw_result' => array_merge((array) $reservation->raw_result, [
                        'ticket_info' => $info,
                        'pnr' => $pnrs[0] ?? null,
                        'pnrs' => $pnrs,
                    ]),
                ])->save();
            }
        }

        if ($pnrs === []) {
            return [
                'ok' => false,
                'message' => 'Cannot cancel/refund: airline PNR (voucher value) is missing. Retrieve ticket info first, then retry cancel.',
                'provider' => 'sunspring',
            ];
        }

        $result = $air->cancel([
            'reference' => $reference,
            'type' => 'General',
            'tickets' => $tickets,
            'voucher' => $pnrs,
            'pnrs' => $pnrs,
            'pnr' => $pnrs[0],
        ]);
        if (! ($result['ok'] ?? false)) {
            return $result;
        }

        $reservation->update([
            'status' => FlightReservation::STATUS_CANCELLED,
            'cancelled_at' => now(),
            'raw_result' => array_merge((array) $reservation->raw_result, [
                'cancel' => $result,
                'cancel_request_id' => $result['request_id'] ?? data_get($result, 'raw.request_id'),
                'pnr' => $pnrs[0],
                'pnrs' => $pnrs,
            ]),
        ]);

        return array_merge($result, [
            'ok' => true,
            'message' => $result['message'] ?? 'SunSpring reservation cancelled.',
            'cancelled' => true,
            'provider' => 'sunspring',
            'voucher' => $pnrs,
            'request_id' => $result['request_id'] ?? null,
        ]);
    }

    protected function runSunSpringCancelTrackingFlow(FlightReservation $reservation): array
    {
        $requestId = trim((string) data_get($reservation->raw_result, 'cancel_request_id',
            data_get($reservation->raw_result, 'cancel.request_id',
                data_get($reservation->raw_result, 'cancel.raw.request_id', '')
            )
        ));
        if ($requestId === '') {
            return [
                'ok' => false,
                'message' => 'No SunSpring cancel request_id on this file. Cancel first, then track.',
                'provider' => 'sunspring',
            ];
        }

        $result = app(SunSpringAirService::class)->cancelTracking($requestId);
        if ($result['ok'] ?? false) {
            $reservation->forceFill([
                'raw_result' => array_merge((array) $reservation->raw_result, [
                    'cancel_tracking' => $result,
                ]),
                'gds_snapshot' => array_merge(
                    is_array($reservation->gds_snapshot) ? $reservation->gds_snapshot : [],
                    ['cancel_tracking' => $result]
                ),
            ])->save();
        }

        return $result;
    }

    protected function runDowntownTicketFlow(FlightReservation $reservation): array
    {
        $dt = app(DowntownTravelAirService::class);
        $resolved = $this->resolveDowntownBookingContext($reservation, $dt);
        if (! ($resolved['ok'] ?? false)) {
            return $resolved;
        }

        $result = $dt->issueTickets([
            'booking_record_id' => $resolved['booking_record_id'],
            'email' => (string) ($reservation->passenger_email ?? ''),
            'phone' => (string) ($reservation->passenger_phone ?? ''),
            'payment_option' => 'agent_cash',
            'passengers' => [[
                'type' => 'ADT',
                'prefix' => (string) ($reservation->passenger_prefix ?? 'Mr'),
                'first' => (string) ($reservation->passenger_first ?? ''),
                'last' => (string) ($reservation->passenger_last ?? ''),
                'dob' => optional($reservation->passenger_dob)?->format('Y-m-d') ?: '1990-01-01',
                'gender' => (string) ($reservation->passenger_gender ?? 'M'),
                'nationality' => 'US',
                'email' => (string) ($reservation->passenger_email ?? ''),
                'phone' => (string) ($reservation->passenger_phone ?? ''),
            ]],
        ]);

        session([
            'travelport.flight_ticket' => $result,
            'public.flight_ticket' => $result,
        ]);

        if ($result['ok'] ?? false) {
            $reservation->forceFill([
                'status' => FlightReservation::STATUS_TICKETED,
                'ticket_numbers' => $result['ticket_numbers'] ?? [],
                'ticketed_at' => now(),
                'raw_result' => array_merge((array) $reservation->raw_result, [
                    'ticket' => $result,
                    'booking_record_id' => $resolved['booking_record_id'],
                ]),
            ])->save();
        }

        return $result;
    }

    protected function runDowntownOrderRefreshFlow(FlightReservation $reservation): array
    {
        $dt = app(DowntownTravelAirService::class);
        $orderId = trim((string) ($reservation->universal_locator ?? ''));
        if ($orderId === '') {
            return ['ok' => false, 'message' => 'No Downtown Travel order id on this reservation.', 'provider' => 'downtown_travel'];
        }

        $result = $dt->getOrder($orderId);
        if (! ($result['ok'] ?? false)) {
            return $result;
        }

        $order = is_array($result['order'] ?? null) ? $result['order'] : [];
        $updates = [
            'gds_snapshot' => [
                'provider' => 'downtown_travel',
                'order' => $order,
                'retrieved_at' => now()->toIso8601String(),
                'can_ticket' => $result['can_ticket'] ?? null,
                'can_cancel' => $result['can_cancel'] ?? null,
                'can_void' => $result['can_void'] ?? null,
                'can_refund' => $result['can_refund'] ?? null,
            ],
            'raw_result' => array_merge((array) $reservation->raw_result, [
                'order' => $order,
                'booking_record_id' => $result['booking_record_id'] ?? null,
            ]),
        ];

        if (! empty($result['airline_pnr'])) {
            $updates['air_reservation_locator'] = $result['airline_pnr'];
            $updates['provider_locator'] = $result['airline_pnr'];
        }

        $tickets = $result['ticket_numbers'] ?? [];
        if (is_array($tickets) && $tickets !== []) {
            $updates['ticket_numbers'] = $tickets;
            $updates['status'] = FlightReservation::STATUS_TICKETED;
            $updates['ticketed_at'] = $reservation->ticketed_at ?? now();
        }

        $reservation->forceFill($updates)->save();

        return [
            'ok' => true,
            'message' => 'Downtown Travel order refreshed.',
            'provider' => 'downtown_travel',
            'raw' => $result,
        ];
    }

    protected function runDowntownCancelFlow(FlightReservation $reservation): array
    {
        $dt = app(DowntownTravelAirService::class);
        $resolved = $this->resolveDowntownBookingContext($reservation, $dt);
        if (! ($resolved['ok'] ?? false)) {
            return $resolved;
        }

        $result = $dt->cancelBookingRecord((string) $resolved['booking_record_id']);
        if (! ($result['ok'] ?? false)) {
            return $result;
        }

        $reservation->update([
            'status' => FlightReservation::STATUS_CANCELLED,
            'cancelled_at' => now(),
            'raw_result' => array_merge((array) $reservation->raw_result, [
                'cancel' => $result,
                'booking_record_id' => $resolved['booking_record_id'],
            ]),
        ]);

        return array_merge($result, ['cancelled' => true]);
    }

    protected function runDowntownVoidFlow(FlightReservation $reservation): array
    {
        $dt = app(DowntownTravelAirService::class);
        $resolved = $this->resolveDowntownBookingContext($reservation, $dt);
        if (! ($resolved['ok'] ?? false)) {
            return $resolved;
        }

        $result = $dt->voidBookingRecord((string) $resolved['booking_record_id']);
        if (! ($result['ok'] ?? false)) {
            return $result;
        }

        $reservation->forceFill([
            'status' => FlightReservation::STATUS_CANCELLED,
            'cancelled_at' => now(),
            'raw_result' => array_merge((array) $reservation->raw_result, [
                'void' => $result,
                'booking_record_id' => $resolved['booking_record_id'],
            ]),
        ])->save();

        return array_merge($result, ['voided' => true]);
    }

    protected function runDowntownRefundFlow(FlightReservation $reservation): array
    {
        $dt = app(DowntownTravelAirService::class);
        $resolved = $this->resolveDowntownBookingContext($reservation, $dt);
        if (! ($resolved['ok'] ?? false)) {
            return $resolved;
        }

        $offer = $dt->createRefundOffer((string) $resolved['booking_record_id']);
        if (! ($offer['ok'] ?? false)) {
            return $offer;
        }

        $offerId = trim((string) ($offer['offer_id'] ?? ''));
        if ($offerId === '') {
            return [
                'ok' => false,
                'message' => 'Downtown Travel created a refund offer but did not return an offer id.',
                'provider' => 'downtown_travel',
                'raw' => $offer,
            ];
        }

        $result = $dt->refundBookingRecord((string) $resolved['booking_record_id'], $offerId);
        if (! ($result['ok'] ?? false)) {
            return array_merge($result, ['offer' => $offer]);
        }

        $reservation->forceFill([
            'status' => FlightReservation::STATUS_CANCELLED,
            'cancelled_at' => now(),
            'raw_result' => array_merge((array) $reservation->raw_result, [
                'refund_offer' => $offer,
                'refund' => $result,
                'booking_record_id' => $resolved['booking_record_id'],
            ]),
        ])->save();

        return array_merge($result, [
            'refunded' => true,
            'offer_id' => $offerId,
        ]);
    }

    /**
     * @return array{ok: bool, message?: string, booking_record_id?: string, provider?: string, order?: array<string, mixed>}
     */
    protected function resolveDowntownBookingContext(FlightReservation $reservation, DowntownTravelAirService $dt): array
    {
        $raw = is_array($reservation->raw_result) ? $reservation->raw_result : [];
        $recordId = $dt->resolveBookingRecordId($raw);
        if ($recordId !== '') {
            return [
                'ok' => true,
                'booking_record_id' => $recordId,
                'provider' => 'downtown_travel',
            ];
        }

        $orderId = trim((string) ($reservation->universal_locator ?? ''));
        if ($orderId === '') {
            return [
                'ok' => false,
                'message' => 'No Downtown Travel order / booking record on this reservation. Refresh order details first.',
                'provider' => 'downtown_travel',
            ];
        }

        $order = $dt->getOrder($orderId);
        if (! ($order['ok'] ?? false)) {
            return $order;
        }

        $recordId = trim((string) ($order['booking_record_id'] ?? ''));
        if ($recordId === '') {
            return [
                'ok' => false,
                'message' => 'Downtown Travel order has no booking record id yet.',
                'provider' => 'downtown_travel',
                'raw' => $order,
            ];
        }

        $reservation->forceFill([
            'raw_result' => array_merge($raw, [
                'order' => $order['order'] ?? null,
                'booking_record_id' => $recordId,
            ]),
        ])->save();

        return [
            'ok' => true,
            'booking_record_id' => $recordId,
            'provider' => 'downtown_travel',
            'order' => is_array($order['order'] ?? null) ? $order['order'] : [],
        ];
    }

    /**
     * @return list<string>
     */
    protected function sunSpringPnrsFromReservation(FlightReservation $reservation, SunSpringAirService $air): array
    {
        $raw = is_array($reservation->raw_result) ? $reservation->raw_result : [];
        $pnrs = [];

        foreach (['pnr', 'PNR'] as $key) {
            $value = trim((string) ($raw[$key] ?? ''));
            if ($value !== '') {
                $pnrs[] = $value;
            }
        }
        if (is_array($raw['pnrs'] ?? null)) {
            foreach ($raw['pnrs'] as $pnr) {
                $value = trim((string) $pnr);
                if ($value !== '') {
                    $pnrs[] = $value;
                }
            }
        }

        $pnrs = array_merge(
            $pnrs,
            $air->extractPnrsFromPayload($raw),
            $air->extractPnrsFromPayload(is_array($reservation->gds_snapshot) ? $reservation->gds_snapshot : [])
        );

        return array_values(array_unique($pnrs));
    }
}
