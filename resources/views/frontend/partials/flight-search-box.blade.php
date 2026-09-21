@php
    $homeFlightInput = $flightSearchInput ?? [];
    $originCode = strtoupper((string) ($homeFlightInput['origin'] ?? 'JFK'));
    $destCode = strtoupper((string) ($homeFlightInput['destination'] ?? 'LAX'));
    $originAirport = \App\Support\AirportDirectory::find($originCode);
    $destAirport = \App\Support\AirportDirectory::find($destCode);
    $searchSubmitLabel = $searchSubmitLabel ?? 'Search Flights';
    $tripTypeRaw = \Illuminate\Support\Str::of((string) ($homeFlightInput['trip_type'] ?? 'oneway'))->lower()->toString();
    $isRound = in_array($tripTypeRaw, ['roundtrip', 'round-way', 'round_way'], true);
    $isMulti = in_array($tripTypeRaw, ['multicity', 'multi-city', 'multi_city', 'multi'], true);
    $airportSearchUrl = route('api.airports.search');

    $defaultMultiLegs = [
        [
            'origin' => $originCode,
            'destination' => 'ORD',
            'departure_date' => $homeFlightInput['departure_date'] ?? now()->addDays(14)->format('Y-m-d'),
        ],
        [
            'origin' => 'ORD',
            'destination' => $destCode,
            'departure_date' => isset($homeFlightInput['departure_date'])
                ? \Carbon\Carbon::parse($homeFlightInput['departure_date'])->addDays(3)->format('Y-m-d')
                : now()->addDays(17)->format('Y-m-d'),
        ],
    ];
    $multiLegs = $homeFlightInput['legs'] ?? $defaultMultiLegs;
    if (! is_array($multiLegs) || count($multiLegs) < 2) {
        $multiLegs = $defaultMultiLegs;
    }
    $multiLegs = array_values(array_slice($multiLegs, 0, 6));
@endphp
@php
    $hotelInput = $hotelSearchInput ?? session('public.hotel_search.input', []);
    $hotelProviders = $providerOptions ?? \App\Support\HotelProvider::options();
    $hotelDestinations = $downtownDestinations ?? \App\Services\DowntownTravel\DowntownTravelHotelService::destinationOptions();
    $hotelProviderSelected = old('provider', $hotelInput['provider'] ?? ($hotelProvider ?? \App\Support\HotelProvider::current()));
    $hotelIsDowntown = $hotelProviderSelected === \App\Support\HotelProvider::DOWNTOWN_TRAVEL_HOTELS;
    $hotelCheckIn = old('check_in', $hotelInput['check_in'] ?? now()->addMonths(2)->format('Y-m-d'));
    $hotelCheckOut = old('check_out', $hotelInput['check_out'] ?? now()->addMonths(2)->addDay()->format('Y-m-d'));
    $openHotelsTab = request()->query('tab') === 'hotels';
@endphp
<div class="search-area home-flight-search-area" id="home-flight-search">
    <div class="container">
        <div class="search-wrapper home-flight-search-only">
            @if(session('success') || session('error'))
                <div class="alert {{ session('error') ? 'alert-danger' : 'alert-success' }} home-flight-alert">
                    {{ session('error') ?? session('success') }}
                </div>
            @endif

            <div class="home-search-tabs search-header">
                <div class="search-nav">
                    <ul class="nav nav-pills" role="tablist">
                        <li class="nav-item" role="presentation">
                            <button class="nav-link {{ $openHotelsTab ? '' : 'active' }}" id="home-tab-flights"
                                data-bs-toggle="pill" data-bs-target="#pills-1" type="button" role="tab"
                                aria-controls="pills-1" aria-selected="{{ $openHotelsTab ? 'false' : 'true' }}">
                                <i class="far fa-plane-departure"></i>Flights
                            </button>
                        </li>
                        <li class="nav-item" role="presentation">
                            <button class="nav-link {{ $openHotelsTab ? 'active' : '' }}" id="home-tab-hotels"
                                data-bs-toggle="pill" data-bs-target="#pills-hotels" type="button" role="tab"
                                aria-controls="pills-hotels" aria-selected="{{ $openHotelsTab ? 'true' : 'false' }}">
                                <i class="far fa-hotel"></i>Hotels
                            </button>
                        </li>
                    </ul>
                </div>
            </div>

            <div class="tab-content home-flight-panel" id="pills-tabContent">
                <div class="tab-pane fade {{ $openHotelsTab ? '' : 'show active' }}" id="pills-1" role="tabpanel" tabindex="0">
                    <div class="flight-search ft-group home-flight-search">
                        <div class="search-form">
                            <form action="{{ route('frontend.flights.search') }}" method="POST" id="homeFlightSearchForm">
                                @csrf

                                <div class="home-flight-toolbar">
                                    <div class="home-flight-toolbar-copy">
                                        <span class="home-flight-kicker"><i class="far fa-plane-departure"></i> Flight Search</span>
                                        <p>Find the best routes with flexible trip options</p>
                                    </div>
                                    <div class="flight-type home-trip-toggle" role="radiogroup" aria-label="Trip type">
                                        <div class="form-check form-check-inline">
                                            <input class="form-check-input" type="radio"
                                                {{ (! $isRound && ! $isMulti) ? 'checked' : '' }}
                                                value="one-way" name="trip_type" id="flight-type1">
                                            <label class="form-check-label" for="flight-type1">One Way</label>
                                        </div>
                                        <div class="form-check form-check-inline">
                                            <input class="form-check-input" type="radio"
                                                {{ $isRound ? 'checked' : '' }}
                                                value="round-way" name="trip_type" id="flight-type2">
                                            <label class="form-check-label" for="flight-type2">Round Trip</label>
                                        </div>
                                        <div class="form-check form-check-inline">
                                            <input class="form-check-input" type="radio"
                                                {{ $isMulti ? 'checked' : '' }}
                                                value="multi-city" name="trip_type" id="flight-type3">
                                            <label class="form-check-label" for="flight-type3">Multi Destination</label>
                                        </div>
                                    </div>
                                </div>

                                <div class="flight-search-wrapper">
                                    <div class="flight-search-content">
                                        <div class="flight-search-item" id="homeSimpleRoute" style="{{ $isMulti ? 'display: none;' : '' }}">
                                            <div class="row g-3 align-items-stretch home-flight-fields">
                                                <div class="col-lg-3 col-md-6">
                                                    <div class="form-group home-airport-field home-field-card">
                                                        <div class="airport-picker home-airport-picker"
                                                            data-field="origin"
                                                            data-initial-code="{{ $originCode }}"
                                                            data-initial-label="{{ $originAirport['label'] ?? $originCode }}"
                                                            data-search-url="{{ $airportSearchUrl }}">
                                                            <label class="flight-field-label" for="home_origin_display">From</label>
                                                            <div class="airport-picker-input-wrap">
                                                                <i class="fas fa-plane-departure airport-picker-icon" aria-hidden="true"></i>
                                                                <input type="text"
                                                                    id="home_origin_display"
                                                                    class="form-control airport-picker-display"
                                                                    placeholder="City or airport"
                                                                    value="{{ $originAirport['label'] ?? $originCode }}"
                                                                    autocomplete="off"
                                                                    autocorrect="off"
                                                                    spellcheck="false"
                                                                    role="combobox"
                                                                    aria-expanded="false"
                                                                    aria-autocomplete="list"
                                                                    aria-controls="home_origin_list">
                                                                <input type="hidden" name="origin" id="home_origin" class="airport-picker-code" value="{{ $originCode }}" @if(!$isMulti) required @endif>
                                                            </div>
                                                            <ul id="home_origin_list" class="airport-picker-list" role="listbox" hidden></ul>
                                                        </div>
                                                    </div>
                                                </div>
                                                <div class="col-lg-3 col-md-6">
                                                    <div class="form-group home-airport-field home-airport-field-to home-field-card">
                                                        <button type="button" class="search-form-swap home-swap-airports" id="homeSwapAirports" title="Swap airports" aria-label="Swap from and to">
                                                            <i class="far fa-repeat"></i>
                                                        </button>
                                                        <div class="airport-picker home-airport-picker"
                                                            data-field="destination"
                                                            data-initial-code="{{ $destCode }}"
                                                            data-initial-label="{{ $destAirport['label'] ?? $destCode }}"
                                                            data-search-url="{{ $airportSearchUrl }}">
                                                            <label class="flight-field-label" for="home_destination_display">To</label>
                                                            <div class="airport-picker-input-wrap">
                                                                <i class="fas fa-plane-arrival airport-picker-icon" aria-hidden="true"></i>
                                                                <input type="text"
                                                                    id="home_destination_display"
                                                                    class="form-control airport-picker-display"
                                                                    placeholder="City or airport"
                                                                    value="{{ $destAirport['label'] ?? $destCode }}"
                                                                    autocomplete="off"
                                                                    autocorrect="off"
                                                                    spellcheck="false"
                                                                    role="combobox"
                                                                    aria-expanded="false"
                                                                    aria-autocomplete="list"
                                                                    aria-controls="home_destination_list">
                                                                <input type="hidden" name="destination" id="home_destination" class="airport-picker-code" value="{{ $destCode }}" @if(!$isMulti) required @endif>
                                                            </div>
                                                            <ul id="home_destination_list" class="airport-picker-list" role="listbox" hidden></ul>
                                                        </div>
                                                    </div>
                                                </div>
                                                <div class="col-lg-3 col-md-6">
                                                    <div class="form-group home-field-card">
                                                        <div class="search-form-date">
                                                            <div class="search-form-journey">
                                                                <label>Journey Date</label>
                                                                <div class="form-group-icon">
                                                                    <input type="text" name="departure_date"
                                                                        class="form-control date-picker journey-date"
                                                                        value="{{ isset($homeFlightInput['departure_date']) ? \Carbon\Carbon::parse($homeFlightInput['departure_date'])->format('m/d/Y') : '' }}"
                                                                        @if(!$isMulti) required @endif>
                                                                    <i class="fal fa-calendar-days"></i>
                                                                </div>
                                                                <p class="journey-day-name"></p>
                                                            </div>
                                                            <div class="search-form-return" style="{{ $isRound ? '' : 'display: none;' }}">
                                                                <label>Return Date</label>
                                                                <div class="form-group-icon">
                                                                    <input type="text" name="return_date"
                                                                        class="form-control date-picker return-date"
                                                                        value="{{ isset($homeFlightInput['return_date']) ? \Carbon\Carbon::parse($homeFlightInput['return_date'])->format('m/d/Y') : '' }}">
                                                                    <i class="fal fa-calendar-days"></i>
                                                                </div>
                                                                <p class="return-day-name"></p>
                                                            </div>
                                                        </div>
                                                    </div>
                                                </div>
                                                <div class="col-lg-3 col-md-6">
                                                    <div class="home-field-card home-passenger-wrap">
                                                        @include('frontend.partials.flight-passenger-box', ['homeFlightInput' => $homeFlightInput])
                                                    </div>
                                                </div>
                                            </div>
                                        </div>

                                        <div id="homeMultiCity" class="home-multicity" style="{{ $isMulti ? '' : 'display: none;' }}">
                                            <div id="homeMultiCityLegs">
                                                @foreach($multiLegs as $index => $leg)
                                                    @include('frontend.partials.flight-multicity-leg', [
                                                        'index' => $index,
                                                        'leg' => $leg,
                                                        'airportSearchUrl' => $airportSearchUrl,
                                                        'canRemove' => $index > 1,
                                                    ])
                                                @endforeach
                                            </div>
                                            <div class="home-multicity-actions">
                                                <button type="button" class="home-multicity-add" id="homeAddMultiCityLeg">
                                                    <i class="fal fa-plus-circle"></i> Add Another Flight
                                                </button>
                                                <div class="home-multicity-passengers">
                                                    <div class="home-field-card home-passenger-wrap">
                                                        @include('frontend.partials.flight-passenger-box', [
                                                            'homeFlightInput' => $homeFlightInput,
                                                            'idPrefix' => 'multi_',
                                                        ])
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>

                                    <div class="search-btn home-search-btn">
                                        <button type="submit" class="theme-btn home-search-submit">
                                            <span class="far fa-search"></span> {{ $searchSubmitLabel }}
                                        </button>
                                    </div>
                                </div>
                            </form>
                        </div>
                    </div>
                </div>

                <div class="tab-pane fade {{ $openHotelsTab ? 'show active' : '' }}" id="pills-hotels" role="tabpanel" tabindex="0">
                    <div class="flight-search ft-group home-flight-search home-hotel-search">
                        <div class="search-form">
                            <form action="{{ route('frontend.hotels.search') }}" method="POST" id="homeHotelSearchForm">
                                @csrf

                                <div class="home-flight-toolbar">
                                    <div class="home-flight-toolbar-copy">
                                        <span class="home-flight-kicker"><i class="far fa-hotel"></i> Hotel Search</span>
                                        <p>Search stays by city with live wholesale rates</p>
                                    </div>
                                    <div class="home-trip-toggle home-hotel-provider-toggle" role="radiogroup" aria-label="Hotel provider">
                                        @foreach($hotelProviders as $option)
                                            <div class="form-check form-check-inline">
                                                <input class="form-check-input js-home-hotel-provider" type="radio"
                                                    name="provider" value="{{ $option['id'] }}"
                                                    id="home-hotel-provider-{{ $option['id'] }}"
                                                    @checked($hotelProviderSelected === $option['id'])
                                                    @disabled(empty($option['ready']))>
                                                <label class="form-check-label" for="home-hotel-provider-{{ $option['id'] }}">
                                                    {{ $option['label'] }}@if(empty($option['ready'])) (off)@endif
                                                </label>
                                            </div>
                                        @endforeach
                                    </div>
                                </div>

                                @unless($hotelReady ?? \App\Support\HotelProvider::anyReady())
                                    <div class="alert alert-warning py-2 small">
                                        Hotel search is not configured yet. Ask admin to enable Downtown Travel Hotels or Xconnect.
                                    </div>
                                @endunless

                                <div class="flight-search-wrapper">
                                    <div class="flight-search-content">
                                        <div class="flight-search-item">
                                            <div class="row g-3 align-items-stretch home-flight-fields">
                                                <div class="col-lg-3 col-md-6 js-home-downtown-fields" style="{{ $hotelIsDowntown ? '' : 'display:none' }}">
                                                    <div class="form-group home-field-card">
                                                        <label class="flight-field-label">City</label>
                                                        <div class="form-group-icon">
                                                            <select name="destination" class="form-control" @disabled(! $hotelIsDowntown)>
                                                                <option value="">Select city</option>
                                                                @foreach($hotelDestinations as $dest)
                                                                    <option value="{{ $dest['id'] }}"
                                                                        @selected(old('destination', $hotelInput['destination'] ?? '') === $dest['id'])>
                                                                        {{ $dest['label'] }}
                                                                    </option>
                                                                @endforeach
                                                            </select>
                                                            <i class="fal fa-map-marker-alt"></i>
                                                        </div>
                                                    </div>
                                                </div>
                                                <div class="col-lg-3 col-md-6 js-home-xconnect-fields" style="{{ $hotelIsDowntown ? 'display:none' : '' }}">
                                                    <div class="form-group home-field-card">
                                                        <label class="flight-field-label">City ID</label>
                                                        <div class="form-group-icon">
                                                            <input type="text" name="city_id" class="form-control"
                                                                value="{{ old('city_id', $hotelInput['city_id'] ?? '') }}"
                                                                placeholder="Xconnect city ID"
                                                                @disabled($hotelIsDowntown)>
                                                            <i class="fal fa-city"></i>
                                                        </div>
                                                    </div>
                                                </div>
                                                <div class="col-lg-3 col-md-6">
                                                    <div class="form-group home-field-card">
                                                        <div class="search-form-date">
                                                            <div class="search-form-journey">
                                                                <label>Check-in</label>
                                                                <div class="form-group-icon">
                                                                    <input type="date" name="check_in" class="form-control"
                                                                        value="{{ $hotelCheckIn }}" required>
                                                                    <i class="fal fa-calendar-days"></i>
                                                                </div>
                                                            </div>
                                                            <div class="search-form-return">
                                                                <label>Check-out</label>
                                                                <div class="form-group-icon">
                                                                    <input type="date" name="check_out" class="form-control"
                                                                        value="{{ $hotelCheckOut }}" required>
                                                                    <i class="fal fa-calendar-days"></i>
                                                                </div>
                                                            </div>
                                                        </div>
                                                    </div>
                                                </div>
                                                <div class="col-lg-2 col-md-6">
                                                    <div class="form-group home-field-card">
                                                        <label class="flight-field-label">Adults</label>
                                                        <div class="form-group-icon">
                                                            <input type="number" name="adults" class="form-control" min="1" max="8"
                                                                value="{{ old('adults', $hotelInput['adults'] ?? 2) }}">
                                                            <i class="fal fa-user"></i>
                                                        </div>
                                                    </div>
                                                </div>
                                                <div class="col-lg-1 col-md-6">
                                                    <div class="form-group home-field-card">
                                                        <label class="flight-field-label">Kids</label>
                                                        <div class="form-group-icon">
                                                            <input type="number" name="children" class="form-control" min="0" max="6"
                                                                value="{{ old('children', $hotelInput['children'] ?? 0) }}">
                                                            <i class="fal fa-child"></i>
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="search-btn home-search-btn">
                                        <button type="submit" class="theme-btn home-search-submit"
                                            @disabled(!($hotelReady ?? \App\Support\HotelProvider::anyReady()))>
                                            <span class="far fa-search"></span> Search Hotels
                                        </button>
                                    </div>
                                </div>
                            </form>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@once
<script>
(function () {
    function syncHomeHotelProvider() {
        var selected = document.querySelector('.js-home-hotel-provider:checked');
        var isDowntown = selected && selected.value === 'downtown_travel_hotels';
        document.querySelectorAll('.js-home-downtown-fields').forEach(function (el) {
            el.style.display = isDowntown ? '' : 'none';
            el.querySelectorAll('select,input').forEach(function (field) { field.disabled = !isDowntown; });
        });
        document.querySelectorAll('.js-home-xconnect-fields').forEach(function (el) {
            el.style.display = isDowntown ? 'none' : '';
            el.querySelectorAll('select,input').forEach(function (field) { field.disabled = !!isDowntown; });
        });
    }
    document.querySelectorAll('.js-home-hotel-provider').forEach(function (el) {
        el.addEventListener('change', syncHomeHotelProvider);
    });
    syncHomeHotelProvider();
})();
</script>
@endonce

<template id="homeMultiCityLegTemplate">
    @include('frontend.partials.flight-multicity-leg', [
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
