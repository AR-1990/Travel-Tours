<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Concerns\HandlesFlightWorkflow;
use App\Http\Controllers\Concerns\NormalizesFlightSearchInput;
use App\Services\Travelport\TravelportAirCatalog;
use App\Services\Travelport\TravelportAirService;
use App\Services\Travelport\TravelportIntegrationConfig;
use App\Support\AirportDirectory;
use Illuminate\Http\Request;

class PublicFlightController extends Controller
{
    use HandlesFlightWorkflow;
    use NormalizesFlightSearchInput;

    protected function flightsRoutePrefix(): string
    {
        return 'frontend';
    }

    public function flightHub()
    {
        return view('frontend.flight-hub', $this->publicFlightViewData(session('public.flight_search', [])));
    }

    public function flightSearch(Request $request, TravelportAirService $air)
    {
        $input = $this->validatedFlightSearchInput($request);
        if ($input === null) {
            return redirect()->route('home')->with('error', 'Please provide valid origin, destination, and journey date.');
        }

        $searchResult = $air->lowFareSearch($input);

        session([
            'public.flight_search' => [
                'input' => $input,
                'result' => $searchResult,
            ],
        ]);

        return redirect()
            ->route('frontend.flights.results')
            ->with($searchResult['ok'] ? 'success' : 'error', $searchResult['message'] ?? 'Search complete.');
    }

    public function flightResults()
    {
        $stored = session('public.flight_search');
        if (! is_array($stored) || ! isset($stored['result'])) {
            return redirect()->route('home')->with('error', 'Please run a flight search first.');
        }

        return view('frontend.flight-results', $this->publicFlightViewData($stored));
    }

    public function flightPrice(Request $request, TravelportAirService $air)
    {
        return $this->workflowPrice($request, $air);
    }

    public function flightPriceShow()
    {
        return $this->workflowPriceShow();
    }

    public function flightBookShow()
    {
        return $this->workflowBookShow();
    }

    public function flightBookStore(Request $request, TravelportAirService $air)
    {
        return $this->workflowBookStore($request, $air);
    }

    public function flightConfirmation()
    {
        return $this->workflowConfirmation();
    }

    public function flightTicketIssue(TravelportAirService $air)
    {
        return $this->workflowTicketIssue($air);
    }

    public function flightOperation(Request $request, string $operation, TravelportAirService $air)
    {
        if (! TravelportAirCatalog::exists($operation)) {
            abort(404);
        }

        if ($operation === 'low_fare_search') {
            return redirect()->route('home');
        }

        if ($operation === 'air_create_reservation' && $request->isMethod('get')) {
            return redirect()->route('frontend.flights.book');
        }

        if ($request->isMethod('post')) {
            if (! TravelportIntegrationConfig::isReadyForAir()) {
                return redirect()->back()->with('error', 'Flight API is not configured.');
            }

            $params = $this->flightOperationParams($request, $operation);
            $result = $air->execute($operation, $params);

            if ($operation === 'air_create_reservation' && ($result['ok'] ?? false)) {
                $this->persistFlightBooking($result, $params);

                return redirect()
                    ->route('frontend.flights.confirmation')
                    ->with('success', $result['message'] ?? 'Booking created.');
            }

            session([
                'public.flight_operation' => [
                    'operation' => $operation,
                    'input' => $params,
                    'result' => $result,
                ],
            ]);

            return redirect()
                ->route('frontend.flights.operation', ['operation' => $operation])
                ->with($result['ok'] ? 'success' : 'error', $result['message'] ?? 'Request complete.');
        }

        $stored = session('public.flight_operation');
        $search = session('public.flight_search', []);
        $price = session('public.flight_price', []);
        $defaultInput = $this->defaultFlightOperationInput($operation, is_array($search) ? $search : [], is_array($price) ? $price : []);

        return view('frontend.flight-operation', array_merge($this->publicFlightViewData(is_array($search) ? $search : []), [
            'operationKey' => $operation,
            'currentOperation' => TravelportAirCatalog::get($operation),
            'searchInput' => is_array($stored) && ($stored['operation'] ?? '') === $operation
                ? array_merge($defaultInput, $stored['input'] ?? [])
                : $defaultInput,
            'operationResult' => is_array($stored) && ($stored['operation'] ?? '') === $operation
                ? ($stored['result'] ?? null)
                : null,
            'workflowStep' => in_array($operation, ['air_ticketing', 'air_retrieve_document'], true) ? 'ticket' : 'price',
            'flightsRoutePrefix' => 'frontend',
            'hasPricingContext' => $air->hasStoredPricingContext(),
        ]));
    }

    /**
     * @param  array<string, mixed>  $searchSession
     * @return array<string, mixed>
     */
    private function publicFlightViewData(array $searchSession): array
    {
        $airportOptions = collect(AirportDirectory::popular())
            ->mapWithKeys(fn (array $row): array => [(string) $row['code'] => (string) ($row['label'] ?? $row['code'])])
            ->all();

        return [
            'flightSearchInput' => $searchSession['input'] ?? [],
            'flightSearchResult' => $searchSession['result'] ?? null,
            'airportOptions' => $airportOptions,
            'travelportReady' => TravelportIntegrationConfig::isReadyForAir(),
            'hasPricingContext' => app(TravelportAirService::class)->hasStoredPricingContext(),
            'operationGroups' => TravelportAirCatalog::groupedForUi(),
            'canBookFlights' => true,
        ];
    }
}
