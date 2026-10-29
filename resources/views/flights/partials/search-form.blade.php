@php
    $origin = old('origin', $searchInput['origin'] ?? 'LHR');
    $destination = old('destination', $searchInput['destination'] ?? 'JFK');
    $departure = old('departure_date', $searchInput['departure_date'] ?? now()->addDays(14)->format('Y-m-d'));
    $returnDate = old('return_date', $searchInput['return_date'] ?? '');
    $adults = (int) old('adults', $searchInput['adults'] ?? 1);
    $children = (int) old('children', $searchInput['children'] ?? 0);
    $infants = (int) old('infants', $searchInput['infants'] ?? 0);
    $tripType = old('trip_type', $searchInput['trip_type'] ?? ($returnDate !== '' ? 'roundtrip' : 'oneway'));
    $tripType = in_array($tripType, ['oneway', 'roundtrip', 'multicity'], true) ? $tripType : 'oneway';
    $airportSearchUrl = $airportSearchUrl ?? route('api.airports.search');
    $isMulti = $tripType === 'multicity';

    $defaultLegs = [
        [
            'origin' => 'LHR',
            'destination' => 'CDG',
            'departure_date' => now()->addDays(14)->format('Y-m-d'),
        ],
        [
            'origin' => 'CDG',
            'destination' => 'JFK',
            'departure_date' => now()->addDays(18)->format('Y-m-d'),
        ],
    ];
    $legs = old('legs', $searchInput['legs'] ?? $defaultLegs);
    if (! is_array($legs) || count($legs) < 2) {
        $legs = $defaultLegs;
    }
    $legs = array_values(array_slice($legs, 0, 6));

    $sunspringAirportCodes = \App\Support\SunSpringAirports::CODES;
    $canChooseFlightProvider = (bool) ($canChooseFlightProvider ?? false);
    $ssDefaultOrigin = \App\Support\AirportDirectory::find(\App\Support\SunSpringAirports::defaultOrigin());
    $ssDefaultDest = \App\Support\AirportDirectory::find(\App\Support\SunSpringAirports::defaultDestination());
    $ssMid = \App\Support\AirportDirectory::find('SYZ');
    $tpDefaultOrigin = \App\Support\AirportDirectory::find('LHR');
    $tpDefaultDest = \App\Support\AirportDirectory::find('JFK');
    $tpMid = \App\Support\AirportDirectory::find('CDG');
@endphp
<div class="flight-search-card">
    <form method="POST" action="{{ route($flightsRoutePrefix . '.flights.search') }}" id="flightSearchForm"
        data-ss-origin-code="{{ \App\Support\SunSpringAirports::defaultOrigin() }}"
        data-ss-origin-label="{{ $ssDefaultOrigin['label'] ?? 'THR' }}"
        data-ss-dest-code="{{ \App\Support\SunSpringAirports::defaultDestination() }}"
        data-ss-dest-label="{{ $ssDefaultDest['label'] ?? 'MHD' }}"
        data-ss-mid-label="{{ $ssMid['label'] ?? 'SYZ' }}"
        data-ss-codes='@json($sunspringAirportCodes)'
        data-tp-origin-code="LHR"
        data-tp-origin-label="{{ $tpDefaultOrigin['label'] ?? 'LHR' }}"
        data-tp-dest-code="JFK"
        data-tp-dest-label="{{ $tpDefaultDest['label'] ?? 'JFK' }}"
        data-tp-mid-label="{{ $tpMid['label'] ?? 'CDG' }}">
        @csrf
        <div class="trip-type-tabs" role="group" aria-label="Trip type">
            <label>
                <input type="radio" name="trip_type" value="oneway" @checked($tripType === 'oneway')>
                <span>One Way</span>
            </label>
            <label>
                <input type="radio" name="trip_type" value="roundtrip" @checked($tripType === 'roundtrip')>
                <span>Round Trip</span>
            </label>
            <label>
                <input type="radio" name="trip_type" value="multicity" @checked($tripType === 'multicity')>
                <span>Multi Destination</span>
            </label>
        </div>

        @php
            $provider = old('provider', $searchInput['provider'] ?? ($flightProvider ?? ($canChooseFlightProvider ? 'all' : 'all')));
            if (! $canChooseFlightProvider) {
                $provider = 'all';
            }
            $providers = $flightProviders ?? \App\Support\FlightProvider::optionsWithAll();
        @endphp
        @if($canChooseFlightProvider)
            <div class="mb-3">
                <div class="provider-select-label">Search via</div>
                <div class="trip-type-tabs" role="group" aria-label="Flight provider">
                    @foreach($providers as $option)
                        <label>
                            <input type="radio" name="provider" value="{{ $option['id'] }}" @checked($provider === $option['id']) @disabled(empty($option['ready']))>
                            <span>{{ $option['label'] }}@if(empty($option['ready'])) (off)@endif</span>
                        </label>
                    @endforeach
                </div>
                <p class="small text-muted mb-0 mt-1">All searches every configured API. Single providers are for diagnostics.</p>
            </div>
        @else
            <input type="hidden" name="provider" value="all">
        @endif

        <p class="small text-muted mb-3">
            <i class="fas fa-info-circle me-1"></i>
            Type a <strong>city</strong> or <strong>airport name</strong> — same picker as the public site ({{ number_format(\App\Support\AirportDirectory::count()) }} airports).
        </p>
        <p class="small text-muted mb-3" data-provider-airport-help hidden>
            <i class="fas fa-info-circle me-1"></i>
            SunSpring shows <strong>Sepehran network airports only</strong> (e.g. THR, MHD, SYZ). Worldwide airports stay available on Travelport.
        </p>

        <div id="simpleRouteFields" style="{{ $isMulti ? 'display: none;' : '' }}">
            <div class="row g-3 align-items-end">
                <div class="col-lg-4 col-md-5">
                    @include('flights.partials.airport-picker', [
                        'name' => 'origin',
                        'id' => 'origin',
                        'value' => $origin,
                        'label' => 'From',
                        'placeholder' => 'City or airport',
                        'searchUrl' => $airportSearchUrl,
                        'icon' => 'fa-plane-departure',
                    ])
                </div>
                <div class="col-12 d-md-none d-grid">
                    <button type="button" class="btn btn-outline-secondary btn-sm" id="swapAirportsMobile"><i class="fas fa-exchange-alt me-1"></i> Swap</button>
                </div>
                <div class="col-lg-1 col-md-2 d-none d-md-flex justify-content-center">
                    <button type="button" class="flight-swap-btn" id="swapAirports" title="Swap" aria-label="Swap from and to">
                        <i class="fas fa-exchange-alt"></i>
                    </button>
                </div>
                <div class="col-lg-4 col-md-5">
                    @include('flights.partials.airport-picker', [
                        'name' => 'destination',
                        'id' => 'destination',
                        'value' => $destination,
                        'label' => 'To',
                        'placeholder' => 'City or airport',
                        'searchUrl' => $airportSearchUrl,
                        'icon' => 'fa-plane-arrival',
                    ])
                </div>
                <div class="col-md-6 col-lg-3">
                    <label class="flight-field-label" for="departure_date">Journey Date</label>
                    <input type="date" name="departure_date" id="departure_date" class="form-control" value="{{ $departure }}" @if(!$isMulti) required @endif>
                </div>
                <div class="col-md-6 col-lg-3" id="returnDateWrap" style="{{ $tripType === 'roundtrip' ? '' : 'display: none;' }}">
                    <label class="flight-field-label" for="return_date">Return Date</label>
                    <input type="date" name="return_date" id="return_date" class="form-control" value="{{ $returnDate }}">
                </div>
            </div>
        </div>

        <div id="multiCityFields" style="{{ $isMulti ? '' : 'display: none;' }}">
            <div id="multiCityLegs">
                @foreach($legs as $index => $leg)
                    @include('flights.partials.multicity-leg', [
                        'index' => $index,
                        'leg' => $leg,
                        'airportSearchUrl' => $airportSearchUrl,
                        'canRemove' => $index > 1,
                    ])
                @endforeach
            </div>
            <div class="mt-2 mb-1">
                <button type="button" class="btn btn-outline-primary btn-sm" id="addMultiCityLeg">
                    <i class="fas fa-plus me-1"></i> Add another flight
                </button>
                <span class="small text-muted ms-2">Up to 6 flights</span>
            </div>
        </div>

        <div class="row g-3 align-items-end mt-1" data-flight-pax-selects="1" data-max-passengers="{{ \App\Support\FlightPassengerLimits::MAX_TOTAL }}">
            <div class="col-md-6 col-lg-2">
                <label class="flight-field-label" for="adults">Adults (12+)</label>
                <select name="adults" id="adults" class="form-select" data-pax-role="adult">
                    @for ($i = 1; $i <= 9; $i++)
                        <option value="{{ $i }}" @selected($adults === $i)>{{ $i }}</option>
                    @endfor
                </select>
            </div>
            <div class="col-md-6 col-lg-2">
                <label class="flight-field-label" for="children">Children (2–12)</label>
                <select name="children" id="children" class="form-select" data-pax-role="child">
                    @for ($i = 0; $i <= 8; $i++)
                        <option value="{{ $i }}" @selected($children === $i)>{{ $i }}</option>
                    @endfor
                </select>
            </div>
            <div class="col-md-6 col-lg-2">
                <label class="flight-field-label" for="infants">Infants (under 2)</label>
                <select name="infants" id="infants" class="form-select" data-pax-role="infant">
                    @for ($i = 0; $i <= 8; $i++)
                        <option value="{{ $i }}" @selected($infants === $i)>{{ $i }}</option>
                    @endfor
                </select>
                <div class="form-text">Max {{ \App\Support\FlightPassengerLimits::MAX_TOTAL }} passengers total.</div>
            </div>
            <div class="col-md-6 col-lg-2 d-grid">
                <button type="submit" class="btn btn-primary btn-lg" @disabled(!($anyProviderReady ?? $travelportReady ?? false))>
                    <i class="fas fa-search me-2"></i>Search Now
                </button>
            </div>
        </div>
    </form>
</div>

<template id="multiCityLegTemplate">
    @include('flights.partials.multicity-leg', [
        'index' => '__INDEX__',
        'leg' => [
            'origin' => '',
            'destination' => '',
            'departure_date' => now()->addDays(21)->format('Y-m-d'),
        ],
        'airportSearchUrl' => $airportSearchUrl,
        'canRemove' => true,
    ])
</template>
