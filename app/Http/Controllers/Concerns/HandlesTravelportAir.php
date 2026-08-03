<?php

namespace App\Http\Controllers\Concerns;

use App\Services\Travelport\TravelportAirCatalog;
use App\Services\Travelport\TravelportAirService;
use App\Services\Travelport\TravelportIntegrationConfig;
use App\Services\SunSpring\SunSpringAirService;
use App\Services\SunSpring\SunSpringIntegrationConfig;
use App\Support\FlightProvider;
use App\Support\FlightResultsPaginator;
use Illuminate\Http\Request;

trait HandlesTravelportAir
{
    use HandlesFlightWorkflow;
    use ManagesFlightReservations;
    use NormalizesFlightSearchInput;

    abstract protected function flightsRoutePrefix(): string;

    abstract protected function panelLabel(): string;

    abstract protected function ensureFlightAccess(): void;

    protected function travelportViewBase(): array
    {
        return [
            'travelportReady' => TravelportIntegrationConfig::isReadyForAir(),
            'travelportEnabled' => TravelportIntegrationConfig::isEnabled(),
            'airServiceUrl' => app(TravelportAirService::class)->airServiceUrl(),
            'hasPricingContext' => app(TravelportAirService::class)->hasStoredPricingContext()
                || app(SunSpringAirService::class)->hasStoredPricingContext(),
            'flightsRoutePrefix' => $this->flightsRoutePrefix(),
            'panelLabel' => $this->panelLabel(),
            'operationGroups' => TravelportAirCatalog::groupedForUi(),
            'airportSearchUrl' => route('api.airports.search'),
            'canBookFlights' => $this->userCanBookFlights(),
            'showDevPanel' => auth()->user()?->user_type === 'super_admin',
            'flightProvider' => FlightProvider::current(),
            'flightProviders' => FlightProvider::options(),
            'sunspringReady' => SunSpringIntegrationConfig::isReadyForAir(),
            'anyProviderReady' => TravelportIntegrationConfig::isReadyForAir() || SunSpringIntegrationConfig::isReadyForAir(),
            'providerReady' => FlightProvider::isReady(),
        ];
    }

    protected function flightSearchViewExtras(): array
    {
        $user = auth()->user();

        return [
            'showDevPanel' => $user && $user->user_type === 'super_admin',
        ];
    }

    public function hub()
    {
        $this->ensureFlightAccess();

        return view('flights.hub', $this->travelportViewBase());
    }

    public function index()
    {
        return $this->hub();
    }

    public function search(Request $request, TravelportAirService $air, SunSpringAirService $sunspring)
    {
        $this->ensureFlightAccess();
        $this->ensureFlightSearchPermission();

        if ($request->filled('provider')) {
            FlightProvider::set((string) $request->input('provider'));
        }

        $data = array_merge($this->travelportViewBase(), $this->flightSearchViewExtras(), [
            'currentOperation' => TravelportAirCatalog::get('low_fare_search'),
            'operationKey' => 'low_fare_search',
            'searchInput' => $request->only(['origin', 'destination', 'departure_date', 'return_date', 'adults', 'trip_type', 'provider', 'legs']),
            'searchResult' => null,
        ]);

        if ($request->isMethod('post')) {
            $result = $this->runRouteSearch($request, $air, $sunspring);
            $input = $this->validatedFlightSearchInput($request)
                ?? $request->only(['origin', 'destination', 'departure_date', 'return_date', 'adults', 'trip_type', 'legs']);
            $input['provider'] = FlightProvider::current();

            session([
                'travelport.flight_search' => [
                    'result' => $result,
                    'input' => $input,
                ],
            ]);

            return redirect()
                ->route($this->flightsRoutePrefix().'.flights.search', ['page' => 1])
                ->with($result['ok'] ? 'success' : 'error', $result['message']);
        }

        $stored = session('travelport.flight_search');
        if (is_array($stored) && isset($stored['result'])) {
            $data['searchResult'] = FlightResultsPaginator::apply($stored['result'], $request);
            $data['searchInput'] = $stored['input'] ?? [];
            $data['hasPricingContext'] = FlightProvider::isSunSpring()
                ? $sunspring->hasStoredPricingContext()
                : $air->hasStoredPricingContext();
            $data['canBookFlights'] = $this->userCanBookFlights();
        }

        return view('flights.search', $data);
    }

    public function price(Request $request, TravelportAirService $air, SunSpringAirService $sunspring)
    {
        $this->ensureFlightAccess();

        return $this->workflowPrice($request, $air, $sunspring);
    }

    public function priceShow()
    {
        $this->ensureFlightAccess();

        return $this->workflowPriceShow();
    }

    public function bookShow()
    {
        $this->ensureFlightAccess();

        return $this->workflowBookShow();
    }

    public function bookStore(Request $request, TravelportAirService $air, SunSpringAirService $sunspring)
    {
        $this->ensureFlightAccess();

        return $this->workflowBookStore($request, $air, $sunspring);
    }

    public function confirmation()
    {
        $this->ensureFlightAccess();

        return $this->workflowConfirmation();
    }

    public function ticketIssue(TravelportAirService $air, SunSpringAirService $sunspring)
    {
        $this->ensureFlightAccess();

        return $this->workflowTicketIssue($air, $sunspring);
    }

    public function operation(Request $request, string $operation, TravelportAirService $air)
    {
        $this->ensureFlightAccess();

        if (! TravelportAirCatalog::exists($operation)) {
            abort(404);
        }

        if ($operation === 'low_fare_search') {
            return redirect()->route($this->flightsRoutePrefix().'.flights.search');
        }

        if ($operation === 'air_create_reservation' && $request->isMethod('get')) {
            return redirect()->route($this->flightsRoutePrefix().'.flights.book');
        }

        if ($request->isMethod('post') && $operation === 'air_create_reservation') {
            $this->ensureFlightBookPermission();
        } elseif ($request->isMethod('post')) {
            $this->ensureFlightSearchPermission();
        }

        $meta = TravelportAirCatalog::get($operation);
        $data = array_merge($this->travelportViewBase(), $this->flightSearchViewExtras(), [
            'currentOperation' => $meta,
            'operationKey' => $operation,
            'searchInput' => $request->all(),
            'searchResult' => null,
        ]);

        if ($request->isMethod('post')) {
            $result = $air->execute($operation, $this->flightOperationParams($request, $operation));
            $input = $this->flightOperationParams($request, $operation);

            if ($operation === 'air_create_reservation' && ($result['ok'] ?? false)) {
                $reservation = $this->persistFlightBooking($result, $input);

                return redirect()
                    ->route($this->flightsRoutePrefix().'.flights.reservations.show', $reservation)
                    ->with('success', $result['message'] ?? 'Booking created.');
            }

            session([
                'travelport.flight_operation' => [
                    'operation' => $operation,
                    'result' => $result,
                    'input' => $input,
                ],
            ]);

            return redirect()
                ->route($this->flightsRoutePrefix().'.flights.operation', ['operation' => $operation, 'page' => 1])
                ->with($result['ok'] ? 'success' : 'error', $result['message']);
        }

        $stored = session('travelport.flight_operation');
        if (is_array($stored) && ($stored['operation'] ?? '') === $operation && isset($stored['result'])) {
            $data['searchResult'] = FlightResultsPaginator::apply($stored['result'], $request);
            $data['searchInput'] = array_merge($request->all(), $stored['input'] ?? []);
            $data['hasPricingContext'] = $air->hasStoredPricingContext();
            $data['canBookFlights'] = $this->userCanBookFlights();
        }

        return view('flights.operation', $data);
    }

    /**
     * @return array<string, mixed>
     */
    protected function runRouteSearch(Request $request, TravelportAirService $air, ?SunSpringAirService $sunspring = null): array
    {
        if ($request->filled('provider')) {
            FlightProvider::set((string) $request->input('provider'));
        }

        $tripType = $this->normalizeTripType((string) $request->input('trip_type', 'oneway'));
        $searchParams = null;

        if ($tripType === 'multicity') {
            $validated = $request->validate([
                'legs' => ['required', 'array', 'min:2', 'max:6'],
                'legs.*.origin' => ['required', 'string', 'size:3'],
                'legs.*.destination' => ['required', 'string', 'size:3'],
                'legs.*.departure_date' => ['required', 'date', 'after_or_equal:today'],
                'adults' => ['nullable', 'integer', 'min:1', 'max:9'],
                'trip_type' => ['nullable', 'in:oneway,roundtrip,multicity'],
                'provider' => ['nullable', 'in:travelport,sunspring'],
            ]);

            $legs = [];
            foreach ($validated['legs'] as $leg) {
                $origin = strtoupper((string) $leg['origin']);
                $destination = strtoupper((string) $leg['destination']);
                if ($origin === $destination) {
                    continue;
                }
                $legs[] = [
                    'origin' => $origin,
                    'destination' => $destination,
                    'departure_date' => $leg['departure_date'],
                ];
            }

            if (count($legs) < 2) {
                throw \Illuminate\Validation\ValidationException::withMessages([
                    'legs' => 'Multi-city search needs at least two different flight legs.',
                ]);
            }

            usort($legs, static fn (array $a, array $b): int => strcmp($a['departure_date'], $b['departure_date']));
            $legs = array_values(array_slice($legs, 0, 6));
            $first = $legs[0];
            $last = $legs[array_key_last($legs)];

            $searchParams = [
                'origin' => $first['origin'],
                'destination' => $last['destination'],
                'departure_date' => $first['departure_date'],
                'return_date' => null,
                'adults' => (int) ($validated['adults'] ?? 1),
                'trip_type' => 'multicity',
                'legs' => $legs,
            ];
        } else {
            $validated = $request->validate([
                'origin' => ['required', 'string', 'size:3'],
                'destination' => ['required', 'string', 'size:3'],
                'departure_date' => ['required', 'date', 'after_or_equal:today'],
                'return_date' => [$tripType === 'roundtrip' ? 'required' : 'nullable', 'date', 'after:departure_date'],
                'adults' => ['nullable', 'integer', 'min:1', 'max:9'],
                'trip_type' => ['nullable', 'in:oneway,roundtrip,multicity'],
                'provider' => ['nullable', 'in:travelport,sunspring'],
            ]);

            $searchParams = [
                'origin' => $validated['origin'],
                'destination' => $validated['destination'],
                'departure_date' => $validated['departure_date'],
                'return_date' => ($tripType === 'roundtrip') ? ($validated['return_date'] ?? null) : null,
                'adults' => (int) ($validated['adults'] ?? 1),
                'trip_type' => $tripType,
            ];
        }

        if (FlightProvider::isSunSpring()) {
            $sunspring ??= app(SunSpringAirService::class);

            return $sunspring->lowFareSearch($searchParams);
        }

        return $air->lowFareSearch($searchParams);
    }

}
