@php
    $origin = old('origin', $searchInput['origin'] ?? 'LHR');
    $destination = old('destination', $searchInput['destination'] ?? 'JFK');
    $departure = old('departure_date', $searchInput['departure_date'] ?? now()->addDays(14)->format('Y-m-d'));
    $returnDate = old('return_date', $searchInput['return_date'] ?? '');
    $adults = (int) old('adults', $searchInput['adults'] ?? 1);
    $tripType = old('trip_type', $returnDate !== '' ? 'roundtrip' : 'oneway');
    $airportSearchUrl = $airportSearchUrl ?? route('api.airports.search');

    $popularRoutePairs = [
        ['LHR', 'JFK'],
        ['LHR', 'DXB'],
        ['LHR', 'CDG'],
        ['DEL', 'DXB'],
        ['ISB', 'DXB'],
        ['ORD', 'CDG'],
        ['NYC', 'LON'],
    ];
@endphp
<div class="flight-search-card">
    <form method="POST" action="{{ route($flightsRoutePrefix . '.flights.search') }}" id="flightSearchForm">
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
        </div>

        <p class="small text-muted mb-3">
            <i class="fas fa-info-circle me-1"></i>
            Type a <strong>city</strong> or <strong>airport name</strong> — same picker as the public site ({{ number_format(\App\Support\AirportDirectory::count()) }} airports).
        </p>

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
                <input type="date" name="departure_date" id="departure_date" class="form-control" required value="{{ $departure }}">
            </div>
            <div class="col-md-6 col-lg-3" id="returnDateWrap" style="{{ $tripType === 'roundtrip' ? '' : 'display: none;' }}">
                <label class="flight-field-label" for="return_date">Return Date</label>
                <input type="date" name="return_date" id="return_date" class="form-control" value="{{ $returnDate }}">
            </div>
            <div class="col-md-6 col-lg-2">
                <label class="flight-field-label" for="adults">Passengers</label>
                <select name="adults" id="adults" class="form-select">
                    @for ($i = 1; $i <= 9; $i++)
                        <option value="{{ $i }}" @selected($adults === $i)>{{ $i }} Passenger{{ $i > 1 ? 's' : '' }}</option>
                    @endfor
                </select>
            </div>
            <div class="col-md-6 col-lg-2 d-grid">
                <button type="submit" class="btn btn-primary btn-lg" @disabled(!$travelportReady)>
                    <i class="fas fa-search me-2"></i>Search Now
                </button>
            </div>
        </div>

        <div class="popular-routes mt-3">
            <span class="small text-muted me-1">Popular routes:</span>
            @foreach($popularRoutePairs as [$fromCode, $toCode])
                @php
                    $fromAirport = \App\Support\AirportDirectory::find($fromCode);
                    $toAirport = \App\Support\AirportDirectory::find($toCode);
                    $fromCity = $fromAirport['city'] ?? $fromCode;
                    $toCity = $toAirport['city'] ?? $toCode;
                @endphp
                <button type="button"
                    data-origin="{{ $fromCode }}"
                    data-destination="{{ $toCode }}"
                    data-o-label="{{ $fromAirport['label'] ?? $fromCode }}"
                    data-d-label="{{ $toAirport['label'] ?? $toCode }}">
                    {{ $fromCity }} → {{ $toCity }}
                </button>
            @endforeach
        </div>
    </form>
</div>
