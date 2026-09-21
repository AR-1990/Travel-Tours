<?php

namespace App\Http\Controllers\Concerns;

use App\Models\FlightReservation;
use App\Services\DowntownTravel\DowntownTravelAirService;
use App\Services\DowntownTravel\DowntownTravelIntegrationConfig;
use App\Services\SunSpring\SunSpringAirService;
use App\Services\Travelport\TravelportAirCatalog;
use App\Services\Travelport\TravelportAirService;
use App\Services\Travelport\TravelportIntegrationConfig;
use App\Support\FlightProvider;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

trait HandlesFlightWorkflow
{
    use BuildsFlightOperationParams;
    use RunsFlightWorkflow;

    abstract protected function flightsRoutePrefix(): string;

    protected function workflowIsPublic(): bool
    {
        return $this->flightsRoutePrefix() === 'frontend';
    }

    protected function ensureFlightSearchPermission(): void
    {
        if ($this->workflowIsPublic()) {
            return;
        }

        $user = Auth::user();
        if (! $user) {
            abort(403);
        }

        if (in_array($user->user_type, ['super_admin', 'tenant_admin'], true)) {
            return;
        }

        if (! $user->hasPermission('flights.search')) {
            abort(403, 'You do not have permission to search flights.');
        }
    }

    protected function ensureFlightBookPermission(): void
    {
        if ($this->workflowIsPublic()) {
            return;
        }

        $user = Auth::user();
        if (! $user) {
            abort(403);
        }

        if (in_array($user->user_type, ['super_admin', 'tenant_admin'], true)) {
            return;
        }

        if (! $user->hasPermission('flights.book')) {
            abort(403, 'You do not have permission to book flights.');
        }
    }

    protected function userCanBookFlights(): bool
    {
        if ($this->workflowIsPublic()) {
            return true;
        }

        $user = Auth::user();
        if (! $user) {
            return false;
        }

        if (in_array($user->user_type, ['super_admin', 'tenant_admin'], true)) {
            return true;
        }

        return $user->hasPermission('flights.book');
    }

    /**
     * @return array<string, mixed>|null
     */
    protected function workflowSearchStore(): ?array
    {
        $key = $this->workflowIsPublic() ? 'public.flight_search' : 'travelport.flight_search';
        $stored = session($key);

        return is_array($stored) ? $stored : null;
    }

    /**
     * @return array<string, mixed>|null
     */
    protected function workflowPriceStore(): ?array
    {
        $key = $this->workflowIsPublic() ? 'public.flight_price' : 'travelport.flight_price';
        $stored = session($key);

        return is_array($stored) ? $stored : null;
    }

    /**
     * @param  array<string, mixed>  $payload
     */
    protected function saveWorkflowPrice(array $payload): void
    {
        if ($this->workflowIsPublic()) {
            session(['public.flight_price' => $payload]);
        } else {
            session(['travelport.flight_price' => $payload]);
        }
    }

    public function workflowPrice(Request $request, TravelportAirService $air, ?SunSpringAirService $sunspring = null)
    {
        $this->ensureFlightSearchPermission();

        $stored = $this->workflowSearchStore();
        $searchInput = is_array($stored['input'] ?? null) ? $stored['input'] : [];
        $adults = (int) ($searchInput['adults'] ?? 1);
        $children = (int) ($searchInput['children'] ?? 0);
        $infants = (int) ($searchInput['infants'] ?? 0);
        $solutionKey = (string) $request->input('solution_key', '');
        $this->syncProviderForSolution($request, $stored, $solutionKey);

        if (FlightProvider::isDowntownTravel()) {
            $downtown = app(DowntownTravelAirService::class);
            if (! $downtown->isReady()) {
                return $this->workflowRedirectAfterPriceFail('Flight pricing is not configured for the selected provider.');
            }
            if (! $downtown->hasStoredPricingContext()) {
                return $this->workflowRedirectAfterPriceFail('Run a Downtown Travel flight search first, then price a fare.');
            }
            $result = $downtown->airPrice([
                'adults' => $adults,
                'children' => $children,
                'infants' => $infants,
                'solution_key' => $solutionKey,
            ]);
        } elseif (FlightProvider::isSunSpring()) {
            $sunspring ??= app(SunSpringAirService::class);
            if (! $sunspring->isReady()) {
                return $this->workflowRedirectAfterPriceFail('Flight pricing is not configured for the selected provider.');
            }
            if (! $sunspring->hasStoredPricingContext()) {
                return $this->workflowRedirectAfterPriceFail('Run a SunSpring flight search first, then price a fare.');
            }
            $result = $sunspring->airPrice([
                'adults' => $adults,
                'children' => $children,
                'infants' => $infants,
                'solution_key' => $solutionKey,
            ]);
        } else {
            if (! TravelportIntegrationConfig::isReadyForAir()) {
                return $this->workflowRedirectAfterPriceFail('Flight pricing is not configured for the selected provider.');
            }
            if (! $air->hasStoredPricingContext()) {
                return $this->workflowRedirectAfterPriceFail('Run a flight search first, then price a fare.');
            }
            $result = $air->execute('air_price', [
                'adults' => $adults,
                'children' => $children,
                'infants' => $infants,
                'solution_key' => $solutionKey,
            ]);
        }

        $this->saveWorkflowPrice([
            'solution_key' => $solutionKey,
            'input' => [
                'adults' => $adults,
                'children' => $children,
                'infants' => $infants,
            ],
            'result' => $result,
            'provider' => FlightProvider::current(),
        ]);

        if (! ($result['ok'] ?? false)) {
            return redirect()
                ->route($this->flightsRoutePrefix().'.flights.price.show')
                ->with('error', $result['message'] ?? 'Pricing failed.');
        }

        if ($this->userCanBookFlights()) {
            return redirect()
                ->route($this->flightsRoutePrefix().'.flights.book')
                ->with('success', $result['message'] ?? 'Fare confirmed. Enter passenger details to complete the booking.');
        }

        return redirect()
            ->route($this->flightsRoutePrefix().'.flights.price.show')
            ->with('success', $result['message'] ?? 'Price complete.');
    }

    public function workflowPriceShow()
    {
        $this->ensureFlightSearchPermission();

        $stored = $this->workflowPriceStore();
        if (! is_array($stored) || ! isset($stored['result'])) {
            return redirect()->to($this->workflowSearchUrl())->with('error', 'Please price a flight first.');
        }

        return view($this->workflowView('price'), array_merge($this->workflowViewBase(), [
            'flightPriceResult' => $stored['result'],
            'flightPriceInput' => $stored['input'] ?? [],
            'workflowStep' => 'price',
            'canBookFlights' => $this->userCanBookFlights(),
        ]));
    }

    public function workflowBookShow()
    {
        $this->ensureFlightBookPermission();

        $price = $this->workflowPriceStore();
        if (! is_array($price) || empty($price['result']['ok'])) {
            return redirect()->route($this->flightsRoutePrefix().'.flights.price.show')
                ->with('error', 'Please confirm a fare before booking.');
        }

        $bookProvider = $this->resolveWorkflowBookProvider(null, $price);
        FlightProvider::set($bookProvider);

        if ($bookProvider === FlightProvider::DOWNTOWN_TRAVEL && ! session('downtown_travel.last_price')) {
            return redirect()->to($this->workflowSearchUrl())
                ->with('error', 'Pricing session expired. Search and price again.');
        }

        if ($bookProvider === FlightProvider::SUNSPRING && ! session('sunspring.last_price')) {
            return redirect()->to($this->workflowSearchUrl())
                ->with('error', 'Pricing session expired. Search and price again.');
        }

        if ($bookProvider === FlightProvider::TRAVELPORT && ! session('travelport.last_air_price_xml')) {
            return redirect()->to($this->workflowSearchUrl())
                ->with('error', 'Pricing session expired. Search and price again.');
        }

        $search = $this->workflowSearchStore() ?? [];
        $defaults = $this->defaultFlightOperationInput(
            'air_create_reservation',
            $search,
            is_array($price) ? $price : []
        );

        return view($this->workflowView('book'), array_merge($this->workflowViewBase(), [
            'flightPriceResult' => $price['result'],
            'flightProvider' => $bookProvider,
            'providerReady' => FlightProvider::isReady(),
            'bookInput' => $defaults,
            'passengerSlots' => $this->passengerSlotsFromSearch(is_array($search['input'] ?? null) ? $search['input'] : []),
            'workflowStep' => 'book',
        ]));
    }

    public function workflowBookStore(Request $request, TravelportAirService $air, ?SunSpringAirService $sunspring = null)
    {
        $this->ensureFlightBookPermission();

        $price = $this->workflowPriceStore();
        $bookProvider = $this->resolveWorkflowBookProvider($request, is_array($price) ? $price : null);
        FlightProvider::set($bookProvider);

        if ($bookProvider === FlightProvider::DOWNTOWN_TRAVEL) {
            if (! app(DowntownTravelAirService::class)->isReady()) {
                return redirect()->route($this->flightsRoutePrefix().'.flights.book')
                    ->with('error', 'Flight booking is not configured.');
            }
        } elseif ($bookProvider === FlightProvider::SUNSPRING) {
            $sunspring ??= app(SunSpringAirService::class);
            if (! $sunspring->isReady()) {
                return redirect()->route($this->flightsRoutePrefix().'.flights.book')
                    ->with('error', 'Flight booking is not configured.');
            }
        } elseif (! TravelportIntegrationConfig::isReadyForAir()) {
            return redirect()->route($this->flightsRoutePrefix().'.flights.book')
                ->with('error', 'Flight booking is not configured.');
        }

        $search = $this->workflowSearchStore() ?? [];
        $searchInput = is_array($search['input'] ?? null) ? $search['input'] : [];
        $slots = $this->passengerSlotsFromSearch($searchInput);
        $expected = max(1, count($slots));

        $request->validate(FlightProvider::bookValidationRules($bookProvider, $expected));

        if (FlightProvider::usesPassengerArray($bookProvider)) {
            $defaultNationality = FlightProvider::defaultNationality($bookProvider);
            $passengers = [];
            foreach (array_values($request->input('passengers', [])) as $i => $row) {
                if (! is_array($row)) {
                    continue;
                }
                $expectedType = (string) ($slots[$i]['type'] ?? 'ADT');
                $passengers[] = [
                    'type' => $expectedType,
                    'prefix' => (string) ($row['prefix'] ?? ($expectedType === 'CHD' ? 'Miss' : ($expectedType === 'INF' ? 'Mstr' : 'Mr'))),
                    'first' => (string) ($row['first'] ?? ''),
                    'last' => (string) ($row['last'] ?? ''),
                    'email' => (string) ($row['email'] ?? $request->input('passengers.0.email', '')),
                    'phone' => (string) ($row['phone'] ?? $request->input('passengers.0.phone', '')),
                    'dob' => (string) ($row['dob'] ?? ''),
                    'gender' => (string) ($row['gender'] ?? 'M'),
                    'national_id' => (string) ($row['national_id'] ?? ''),
                    'nationality' => (string) ($row['nationality'] ?? $defaultNationality),
                    'passport_number' => (string) ($row['passport_number'] ?? ''),
                    'passport_expire' => (string) ($row['passport_expire'] ?? ''),
                    'accompanied' => '',
                ];
            }

            if ($passengers === []) {
                return redirect()
                    ->route($this->flightsRoutePrefix().'.flights.book')
                    ->withInput()
                    ->with('error', 'At least one passenger is required.');
            }

            $lead = $passengers[0];
            $params = [
                'country_code' => (string) $request->input('country_code', FlightProvider::defaultCountryCode($bookProvider)),
                'passengers' => $passengers,
                'passenger_prefix' => (string) ($lead['prefix'] ?? 'Mr'),
                'passenger_first' => (string) ($lead['first'] ?? ''),
                'passenger_last' => (string) ($lead['last'] ?? ''),
                'passenger_email' => (string) ($lead['email'] ?? ''),
                'passenger_phone' => (string) ($lead['phone'] ?? ''),
                'passenger_dob' => (string) ($lead['dob'] ?? ''),
                'passenger_gender' => (string) ($lead['gender'] ?? 'M'),
                'email' => (string) ($lead['email'] ?? ''),
                'phone' => (string) ($lead['phone'] ?? ''),
            ];

            $result = $bookProvider === FlightProvider::DOWNTOWN_TRAVEL
                ? app(DowntownTravelAirService::class)->book($params)
                : ($sunspring ??= app(SunSpringAirService::class))->book($params);

            if (! ($result['ok'] ?? false)) {
                return redirect()
                    ->route($this->flightsRoutePrefix().'.flights.book')
                    ->withInput()
                    ->with('error', $result['message'] ?? 'Booking failed.');
            }

            try {
                $reservation = $this->persistFlightBooking($result, $params);
            } catch (\Throwable $e) {
                report($e);
                $locator = (string) ($result['provider_locator']
                    ?? $result['air_reservation_locator']
                    ?? $result['universal_locator']
                    ?? '');

                return redirect()
                    ->route($this->flightsRoutePrefix().'.flights.book')
                    ->withInput()
                    ->with(
                        'error',
                        'The fare was booked with the airline'
                        .($locator !== '' ? ' (ref '.$locator.')' : '')
                        .', but saving the reservation locally failed. Contact support with this reference.'
                    );
            }

            return redirect()
                ->route($this->flightsRoutePrefix().'.flights.reservations.show', $reservation)
                ->with('success', $result['message'] ?? 'Booking created. Your reservation details are below.');
        }

        $params = $this->flightOperationParams($request, 'air_create_reservation');
        $result = $air->execute('air_create_reservation', $params);

        if (! ($result['ok'] ?? false)) {
            return redirect()
                ->route($this->flightsRoutePrefix().'.flights.book')
                ->withInput()
                ->with('error', $result['message'] ?? 'Booking failed.')
                ->with('travelport_last_error_reason', $result['technical_message'] ?? null)
                ->with('travelport_last_error_excerpt', $result['response_excerpt'] ?? null);
        }

        $reservation = $this->persistFlightBooking($result, $params);

        return redirect()
            ->route($this->flightsRoutePrefix().'.flights.reservations.show', $reservation)
            ->with('success', $result['message'] ?? 'Booking created. Your reservation details are below.');
    }

    public function workflowConfirmation()
    {
        $reservationId = session('travelport.last_reservation_id') ?? session('public.last_reservation_id');
        if ($reservationId) {
            return redirect()->route($this->flightsRoutePrefix().'.flights.reservations.show', ['id' => $reservationId]);
        }

        $booking = $this->bookingSession();
        if ($booking === null) {
            if (! $this->workflowIsPublic()) {
                return redirect()->route($this->flightsRoutePrefix().'.flights.reservations.index')
                    ->with('error', 'No booking in this session. Open a reservation from the list.');
            }

            return redirect()->to($this->workflowSearchUrl())->with('error', 'No booking in this session.');
        }

        if (! empty($booking['id'])) {
            return redirect()->route($this->flightsRoutePrefix().'.flights.reservations.show', ['id' => $booking['id']]);
        }

        $search = $this->workflowSearchStore() ?? [];
        $price = $this->workflowPriceStore();
        $ticket = session('travelport.flight_ticket') ?? session('public.flight_ticket');

        return view($this->workflowView('confirmation'), array_merge($this->workflowViewBase(), [
            'flightBooking' => $booking,
            'flightPriceResult' => is_array($price) ? ($price['result'] ?? null) : null,
            'flightTicket' => is_array($ticket) ? $ticket : null,
            'workflowStep' => is_array($ticket) && ! empty($ticket['ticket_numbers']) ? 'done' : 'ticket',
            'canBookFlights' => $this->userCanBookFlights(),
        ]));
    }

    public function workflowTicketIssue(TravelportAirService $air, ?SunSpringAirService $sunspring = null)
    {
        $this->ensureFlightBookPermission();

        if (FlightProvider::isSunSpring()) {
            $sunspring ??= app(SunSpringAirService::class);
            if (! $sunspring->isReady()) {
                return redirect()->route($this->flightsRoutePrefix().'.flights.confirmation')
                    ->with('error', 'Ticketing is not configured.');
            }
        } elseif (FlightProvider::isDowntownTravel()) {
            return redirect()->route($this->flightsRoutePrefix().'.flights.confirmation')
                ->with('success', 'Downtown Travel bookings are confirmed at purchase — no separate ticketing step.');
        } elseif (! TravelportIntegrationConfig::isReadyForAir()) {
            return redirect()->route($this->flightsRoutePrefix().'.flights.confirmation')
                ->with('error', 'Ticketing is not configured.');
        }

        $reservationId = session('travelport.last_reservation_id') ?? session('public.last_reservation_id');
        $reservation = $reservationId ? FlightReservation::query()->find($reservationId) : null;

        if (FlightProvider::isSunSpring()) {
            $sunspring ??= app(SunSpringAirService::class);
            $reference = (int) (
                $reservation?->provider_locator
                ?: $reservation?->universal_locator
                ?: data_get(session('sunspring.last_booking'), 'reference_id', 0)
            );
            $result = $sunspring->issueTicket(['reference' => $reference]);

            if (($result['ok'] ?? false) && $reservation) {
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

            session([
                'travelport.flight_ticket' => $result,
                'public.flight_ticket' => $result,
            ]);

            if ($reservation) {
                return redirect()
                    ->route($this->flightsRoutePrefix().'.flights.reservations.show', $reservation)
                    ->with(($result['ok'] ?? false) ? 'success' : 'error', $result['message'] ?? 'Ticketing complete.');
            }

            return redirect()
                ->route($this->flightsRoutePrefix().'.flights.confirmation')
                ->with(($result['ok'] ?? false) ? 'success' : 'error', $result['message'] ?? 'Ticketing complete.');
        }

        $locators = $this->bookingLocatorParams();
        if ($locators === []) {
            return redirect()->route($this->flightsRoutePrefix().'.flights.confirmation')
                ->with('error', 'No booking locator found.');
        }

        $result = $this->runIssueTicketFlow($air, $locators, $reservation);

        if ($reservation) {
            return redirect()
                ->route($this->flightsRoutePrefix().'.flights.reservations.show', $reservation)
                ->with(($result['ok'] ?? false) ? 'success' : 'error', $result['message'] ?? 'Ticketing complete.');
        }

        return redirect()
            ->route($this->flightsRoutePrefix().'.flights.confirmation')
            ->with(($result['ok'] ?? false) ? 'success' : 'error', $result['message'] ?? 'Ticketing complete.');
    }

    protected function workflowSearchUrl(): string
    {
        if ($this->workflowIsPublic()) {
            return route('frontend.flights.results');
        }

        return route($this->flightsRoutePrefix().'.flights.search');
    }

    protected function workflowView(string $page): string
    {
        if ($this->workflowIsPublic()) {
            return 'frontend.flight-'.$page;
        }

        return 'flights.workflow.'.$page;
    }

    /**
     * Lock book form + validation to the priced fare's provider (not a stale session toggle).
     *
     * @param  array<string, mixed>|null  $price
     */
    protected function resolveWorkflowBookProvider(?Request $request = null, ?array $price = null): string
    {
        $fromRequest = strtolower(trim((string) ($request?->input('provider', '') ?? '')));
        if (in_array($fromRequest, FlightProvider::all(), true)) {
            return $fromRequest;
        }

        $price ??= $this->workflowPriceStore();
        if (is_array($price)) {
            $fromPrice = strtolower((string) ($price['provider'] ?? ''));
            if (in_array($fromPrice, FlightProvider::all(), true)) {
                return $fromPrice;
            }

            $fromResult = FlightProvider::fromResult(is_array($price['result'] ?? null) ? $price['result'] : null);
            if (in_array($fromResult, FlightProvider::all(), true)) {
                // fromResult falls back to current(); only trust when the result itself stamped a provider.
                $stamped = strtolower((string) data_get($price, 'result.provider', ''));
                if (in_array($stamped, FlightProvider::all(), true)) {
                    return $stamped;
                }
                $solutionProvider = strtolower((string) data_get($price, 'result.solutions.0.provider', ''));
                if (in_array($solutionProvider, FlightProvider::all(), true)) {
                    return $solutionProvider;
                }
            }
        }

        if (session('downtown_travel.last_price')) {
            return FlightProvider::DOWNTOWN_TRAVEL;
        }
        if (session('sunspring.last_price')) {
            return FlightProvider::SUNSPRING;
        }
        if (session('travelport.last_air_price_xml')) {
            return FlightProvider::TRAVELPORT;
        }

        return FlightProvider::current();
    }

    /**
     * Price/book must hit the same API that produced the selected fare.
     *
     * @param  array<string, mixed>|null  $stored
     */
    protected function syncProviderForSolution(Request $request, ?array $stored, string $solutionKey): void
    {
        $fromRequest = strtolower(trim((string) $request->input('provider', '')));
        if (in_array($fromRequest, FlightProvider::all(), true)) {
            FlightProvider::set($fromRequest);

            return;
        }

        foreach (($stored['result']['solutions'] ?? []) as $solution) {
            if (! is_array($solution)) {
                continue;
            }
            if ((string) ($solution['key'] ?? '') !== $solutionKey) {
                continue;
            }
            $fromSolution = strtolower((string) ($solution['provider'] ?? ''));
            if (in_array($fromSolution, FlightProvider::all(), true)) {
                FlightProvider::set($fromSolution);
            }

            return;
        }
    }

    /**
     * @param  array<string, mixed>  $searchInput
     * @return list<array{type: string, label: string, prefix: string, gender: string}>
     */
    protected function passengerSlotsFromSearch(array $searchInput): array
    {
        $adults = max(1, min(9, (int) ($searchInput['adults'] ?? 1)));
        $children = max(0, min(8, (int) ($searchInput['children'] ?? 0)));
        $infants = max(0, min(8, (int) ($searchInput['infants'] ?? 0)));

        $slots = [];
        for ($i = 1; $i <= $adults; $i++) {
            $slots[] = [
                'type' => 'ADT',
                'label' => 'Adult '.$i,
                'prefix' => 'Mr',
                'gender' => 'M',
            ];
        }
        for ($i = 1; $i <= $children; $i++) {
            $slots[] = [
                'type' => 'CHD',
                'label' => 'Child '.$i,
                'prefix' => 'Miss',
                'gender' => 'F',
            ];
        }
        for ($i = 1; $i <= $infants; $i++) {
            $slots[] = [
                'type' => 'INF',
                'label' => 'Infant '.$i,
                'prefix' => 'Mstr',
                'gender' => 'M',
            ];
        }

        return $slots;
    }

    /**
     * @return \Illuminate\Http\RedirectResponse
     */
    protected function workflowRedirectAfterPriceFail(string $message)
    {
        return redirect()->to($this->workflowSearchUrl())->with('error', $message);
    }

    /**
     * @return array<string, mixed>
     */
    protected function workflowViewBase(): array
    {
        if ($this->workflowIsPublic()) {
            return method_exists($this, 'publicFlightViewData')
                ? $this->publicFlightViewData($this->workflowSearchStore() ?? [])
                : [];
        }

        if (method_exists($this, 'travelportViewBase')) {
            $search = $this->workflowSearchStore();

            return array_merge($this->travelportViewBase(), [
                'searchInput' => $search['input'] ?? [],
                'searchResult' => $search['result'] ?? null,
                'canBookFlights' => $this->userCanBookFlights(),
            ]);
        }

        return [
            'flightsRoutePrefix' => $this->flightsRoutePrefix(),
            'canBookFlights' => $this->userCanBookFlights(),
        ];
    }
}
