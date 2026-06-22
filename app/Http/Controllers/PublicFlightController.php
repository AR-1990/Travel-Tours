<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Concerns\BuildsFlightOperationParams;
use App\Http\Controllers\Concerns\NormalizesFlightSearchInput;
use App\Http\Controllers\Concerns\RunsFlightWorkflow;
use App\Services\Travelport\TravelportAirCatalog;
use App\Services\Travelport\TravelportAirService;
use App\Services\Travelport\TravelportIntegrationConfig;
use App\Support\AirportDirectory;
use Illuminate\Http\Request;

class PublicFlightController extends Controller
{
    use BuildsFlightOperationParams;
    use NormalizesFlightSearchInput;
    use RunsFlightWorkflow;

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
        if (! TravelportIntegrationConfig::isReadyForAir()) {
            return redirect()->route('frontend.flights.results')->with('error', 'Flight pricing is not configured.');
        }

        if (! $air->hasStoredPricingContext()) {
            return redirect()->route('frontend.flights.results')->with('error', 'Run a flight search first, then price a fare.');
        }

        $stored = session('public.flight_search');
        $adults = (int) ($stored['input']['adults'] ?? 1);
        $solutionKey = (string) $request->input('solution_key', '');

        $result = $air->execute('air_price', [
            'adults' => $adults,
            'solution_key' => $solutionKey,
        ]);

        session([
            'public.flight_price' => [
                'solution_key' => $solutionKey,
                'input' => ['adults' => $adults],
                'result' => $result,
            ],
        ]);

        return redirect()
            ->route('frontend.flights.price.show')
            ->with($result['ok'] ? 'success' : 'error', $result['message'] ?? 'Price complete.');
    }

    public function flightPriceShow()
    {
        $stored = session('public.flight_price');
        if (! is_array($stored) || ! isset($stored['result'])) {
            return redirect()->route('frontend.flights.results')->with('error', 'Please price a flight first.');
        }

        $search = session('public.flight_search', []);

        return view('frontend.flight-price', array_merge($this->publicFlightViewData(is_array($search) ? $search : []), [
            'flightPriceResult' => $stored['result'],
            'flightPriceInput' => $stored['input'] ?? [],
            'pricedSolutionKey' => $stored['solution_key'] ?? '',
            'workflowStep' => 'price',
            'operationGroups' => TravelportAirCatalog::groupedForUi(),
        ]));
    }

    public function flightBookShow()
    {
        $price = session('public.flight_price');
        if (! is_array($price) || empty($price['result']['ok'])) {
            return redirect()->route('frontend.flights.price.show')->with('error', 'Please confirm a fare before booking.');
        }

        if (! session('travelport.last_air_price_xml')) {
            return redirect()->route('frontend.flights.results')->with('error', 'Pricing session expired. Search and price again.');
        }

        $search = session('public.flight_search', []);
        $defaults = $this->defaultFlightOperationInput('air_create_reservation', is_array($search) ? $search : [], $price);

        return view('frontend.flight-book', array_merge($this->publicFlightViewData(is_array($search) ? $search : []), [
            'flightPriceResult' => $price['result'],
            'bookInput' => $defaults,
            'workflowStep' => 'book',
        ]));
    }

    public function flightBookStore(Request $request, TravelportAirService $air)
    {
        if (! TravelportIntegrationConfig::isReadyForAir()) {
            return redirect()->route('frontend.flights.book')->with('error', 'Flight booking is not configured.');
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
                ->route('frontend.flights.book')
                ->withInput()
                ->with('error', $result['message'] ?? 'Booking failed.');
        }

        $this->persistFlightBooking($result, $params);

        return redirect()
            ->route('frontend.flights.confirmation')
            ->with('success', $result['message'] ?? 'Booking created.');
    }

    public function flightConfirmation()
    {
        $booking = $this->bookingSession();
        if ($booking === null) {
            return redirect()->route('home')->with('error', 'No booking in this session. Start a new search.');
        }

        $search = session('public.flight_search', []);
        $price = session('public.flight_price', []);
        $ticket = session('public.flight_ticket');

        return view('frontend.flight-confirmation', array_merge($this->publicFlightViewData(is_array($search) ? $search : []), [
            'flightBooking' => $booking,
            'flightPriceResult' => is_array($price) ? ($price['result'] ?? null) : null,
            'flightTicket' => is_array($ticket) ? $ticket : null,
            'workflowStep' => is_array($ticket) && ! empty($ticket['ticket_numbers']) ? 'done' : 'ticket',
        ]));
    }

    public function flightTicketIssue(TravelportAirService $air)
    {
        if (! TravelportIntegrationConfig::isReadyForAir()) {
            return redirect()->route('frontend.flights.confirmation')->with('error', 'Ticketing is not configured.');
        }

        $locators = $this->bookingLocatorParams();
        if ($locators === []) {
            return redirect()->route('frontend.flights.confirmation')->with('error', 'No booking locator found.');
        }

        $result = $this->runIssueTicketFlow($air, $locators);

        return redirect()
            ->route('frontend.flights.confirmation')
            ->with(($result['ok'] ?? false) ? 'success' : 'error', $result['message'] ?? 'Ticketing complete.');
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
        ];
    }
}
