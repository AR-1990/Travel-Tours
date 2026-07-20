<?php

namespace App\Http\Controllers\Concerns;

use App\Models\FlightReservation;
use App\Services\Travelport\TravelportAirCatalog;
use App\Services\Travelport\TravelportAirService;
use App\Services\Travelport\TravelportIntegrationConfig;
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

    public function workflowPrice(Request $request, TravelportAirService $air)
    {
        $this->ensureFlightSearchPermission();

        if (! TravelportIntegrationConfig::isReadyForAir()) {
            return $this->workflowRedirectAfterPriceFail('Flight pricing is not configured.');
        }

        if (! $air->hasStoredPricingContext()) {
            return $this->workflowRedirectAfterPriceFail('Run a flight search first, then price a fare.');
        }

        $stored = $this->workflowSearchStore();
        $adults = (int) ($stored['input']['adults'] ?? 1);
        $solutionKey = (string) $request->input('solution_key', '');

        $result = $air->execute('air_price', [
            'adults' => $adults,
            'solution_key' => $solutionKey,
        ]);

        $this->saveWorkflowPrice([
            'solution_key' => $solutionKey,
            'input' => ['adults' => $adults],
            'result' => $result,
        ]);

        if (! ($result['ok'] ?? false)) {
            return redirect()
                ->route($this->flightsRoutePrefix().'.flights.price.show')
                ->with('error', $result['message'] ?? 'Pricing failed.');
        }

        // Continue the guided flow automatically — no need to open a URL by hand.
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

        if (! session('travelport.last_air_price_xml')) {
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

    public function workflowBookStore(Request $request, TravelportAirService $air)
    {
        $this->ensureFlightBookPermission();

        if (! TravelportIntegrationConfig::isReadyForAir()) {
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
        ]);

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

    public function workflowTicketIssue(TravelportAirService $air)
    {
        $this->ensureFlightBookPermission();

        if (! TravelportIntegrationConfig::isReadyForAir()) {
            return redirect()->route($this->flightsRoutePrefix().'.flights.confirmation')
                ->with('error', 'Ticketing is not configured.');
        }

        $locators = $this->bookingLocatorParams();
        if ($locators === []) {
            return redirect()->route($this->flightsRoutePrefix().'.flights.confirmation')
                ->with('error', 'No booking locator found.');
        }

        $reservationId = session('travelport.last_reservation_id') ?? session('public.last_reservation_id');
        $reservation = $reservationId ? FlightReservation::query()->find($reservationId) : null;

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
