<?php

namespace App\Http\Controllers\Concerns;

use App\Models\FlightReservation;
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
        $adults = (int) ($stored['input']['adults'] ?? 1);
        $solutionKey = (string) $request->input('solution_key', '');

        if (FlightProvider::isSunSpring()) {
            $sunspring ??= app(SunSpringAirService::class);
            if (! $sunspring->isReady()) {
                return $this->workflowRedirectAfterPriceFail('Flight pricing is not configured for the selected provider.');
            }
            if (! $sunspring->hasStoredPricingContext()) {
                return $this->workflowRedirectAfterPriceFail('Run a SunSpring flight search first, then price a fare.');
            }
            $result = $sunspring->airPrice([
                'adults' => $adults,
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
                'solution_key' => $solutionKey,
            ]);
        }

        $this->saveWorkflowPrice([
            'solution_key' => $solutionKey,
            'input' => ['adults' => $adults],
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

        if (! FlightProvider::isSunSpring() && ! session('travelport.last_air_price_xml')) {
            return redirect()->to($this->workflowSearchUrl())
                ->with('error', 'Pricing session expired. Search and price again.');
        }

        if (FlightProvider::isSunSpring() && ! session('sunspring.last_price')) {
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
            'bookInput' => $defaults,
            'workflowStep' => 'book',
        ]));
    }

    public function workflowBookStore(Request $request, TravelportAirService $air, ?SunSpringAirService $sunspring = null)
    {
        $this->ensureFlightBookPermission();

        if (FlightProvider::isSunSpring()) {
            $sunspring ??= app(SunSpringAirService::class);
            if (! $sunspring->isReady()) {
                return redirect()->route($this->flightsRoutePrefix().'.flights.book')
                    ->with('error', 'Flight booking is not configured.');
            }
        } elseif (! TravelportIntegrationConfig::isReadyForAir()) {
            return redirect()->route($this->flightsRoutePrefix().'.flights.book')
                ->with('error', 'Flight booking is not configured.');
        }

        $request->validate([
            'passenger_first' => ['required', 'string', 'max:80'],
            'passenger_last' => ['required', 'string', 'max:80'],
            'passenger_email' => ['required', 'email', 'max:120'],
            'passenger_phone' => ['required', 'string', 'max:30'],
            'passenger_dob' => ['required', 'date', 'before:today'],
            'passenger_gender' => ['required', 'in:M,F'],
            'passenger_prefix' => ['nullable', 'string', 'max:10'],
            'form_of_payment' => ['nullable', 'string', 'max:20'],
            'national_id' => ['nullable', 'string', 'max:32'],
            'nationality' => ['nullable', 'string', 'max:8'],
            'country_code' => ['nullable', 'string', 'max:8'],
        ]);

        if (FlightProvider::isSunSpring()) {
            $sunspring ??= app(SunSpringAirService::class);
            $params = [
                'country_code' => (string) $request->input('country_code', '+96'),
                'passengers' => [[
                    'prefix' => (string) $request->input('passenger_prefix', 'Mr'),
                    'first' => (string) $request->input('passenger_first'),
                    'last' => (string) $request->input('passenger_last'),
                    'email' => (string) $request->input('passenger_email'),
                    'phone' => (string) $request->input('passenger_phone'),
                    'dob' => (string) $request->input('passenger_dob'),
                    'gender' => (string) $request->input('passenger_gender'),
                    'type' => 'ADT',
                    'national_id' => (string) $request->input('national_id', '0000000000'),
                    'nationality' => (string) $request->input('nationality', 'USA'),
                ]],
                'passenger_prefix' => (string) $request->input('passenger_prefix', 'Mr'),
                'passenger_first' => (string) $request->input('passenger_first'),
                'passenger_last' => (string) $request->input('passenger_last'),
                'passenger_email' => (string) $request->input('passenger_email'),
                'passenger_phone' => (string) $request->input('passenger_phone'),
                'passenger_dob' => (string) $request->input('passenger_dob'),
                'passenger_gender' => (string) $request->input('passenger_gender'),
            ];

            $result = $sunspring->book($params);
            if (! ($result['ok'] ?? false)) {
                return redirect()
                    ->route($this->flightsRoutePrefix().'.flights.book')
                    ->withInput()
                    ->with('error', $result['message'] ?? 'Booking failed.');
            }

            $reservation = $this->persistFlightBooking($result, $params);

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
                    'raw_result' => array_merge((array) $reservation->raw_result, ['ticket' => $result]),
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
