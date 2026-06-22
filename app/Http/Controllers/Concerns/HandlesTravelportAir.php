<?php

namespace App\Http\Controllers\Concerns;

use App\Services\Travelport\TravelportAirCatalog;
use App\Services\Travelport\TravelportAirService;
use App\Services\Travelport\TravelportIntegrationConfig;
use App\Support\FlightResultsPaginator;
use Illuminate\Http\Request;

trait HandlesTravelportAir
{
    use BuildsFlightOperationParams;

    abstract protected function flightsRoutePrefix(): string;

    abstract protected function panelLabel(): string;

    abstract protected function ensureFlightAccess(): void;

    protected function travelportViewBase(): array
    {
        return [
            'travelportReady' => TravelportIntegrationConfig::isReadyForAir(),
            'travelportEnabled' => TravelportIntegrationConfig::isEnabled(),
            'airServiceUrl' => app(TravelportAirService::class)->airServiceUrl(),
            'hasPricingContext' => app(TravelportAirService::class)->hasStoredPricingContext(),
            'flightsRoutePrefix' => $this->flightsRoutePrefix(),
            'panelLabel' => $this->panelLabel(),
            'operationGroups' => TravelportAirCatalog::groupedForUi(),
            'airportSearchUrl' => route('api.airports.search'),
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

    public function search(Request $request, TravelportAirService $air)
    {
        $this->ensureFlightAccess();

        $data = array_merge($this->travelportViewBase(), $this->flightSearchViewExtras(), [
            'currentOperation' => TravelportAirCatalog::get('low_fare_search'),
            'operationKey' => 'low_fare_search',
            'searchInput' => $request->only(['origin', 'destination', 'departure_date', 'return_date', 'adults', 'trip_type']),
            'searchResult' => null,
        ]);

        if ($request->isMethod('post')) {
            $result = $this->runRouteSearch($request, $air);
            $input = $request->only(['origin', 'destination', 'departure_date', 'return_date', 'adults', 'trip_type']);

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
            $data['hasPricingContext'] = $air->hasStoredPricingContext();
        }

        return view('flights.search', $data);
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
        }

        return view('flights.operation', $data);
    }

    /**
     * @return array<string, mixed>
     */
    protected function runRouteSearch(Request $request, TravelportAirService $air): array
    {
        $tripType = $request->input('trip_type', 'oneway');

        $validated = $request->validate([
            'origin' => ['required', 'string', 'size:3'],
            'destination' => ['required', 'string', 'size:3'],
            'departure_date' => ['required', 'date', 'after_or_equal:today'],
            'return_date' => [$tripType === 'roundtrip' ? 'required' : 'nullable', 'date', 'after:departure_date'],
            'adults' => ['nullable', 'integer', 'min:1', 'max:9'],
            'trip_type' => ['nullable', 'in:oneway,roundtrip'],
        ]);

        $returnDate = ($tripType === 'roundtrip') ? ($validated['return_date'] ?? null) : null;

        return $air->lowFareSearch([
            'origin' => $validated['origin'],
            'destination' => $validated['destination'],
            'departure_date' => $validated['departure_date'],
            'return_date' => $returnDate,
            'adults' => (int) ($validated['adults'] ?? 1),
        ]);
    }

}
