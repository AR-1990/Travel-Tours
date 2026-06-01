@php
    $origin = old('origin', $searchInput['origin'] ?? 'LHR');
    $destination = old('destination', $searchInput['destination'] ?? 'JFK');
    $departure = old('departure_date', $searchInput['departure_date'] ?? now()->addDays(14)->format('Y-m-d'));
    $returnDate = old('return_date', $searchInput['return_date'] ?? '');
    $adults = (int) old('adults', $searchInput['adults'] ?? 1);
    $tripType = old('trip_type', $returnDate !== '' ? 'roundtrip' : 'oneway');
    $airportSearchUrl = $airportSearchUrl ?? route('api.airports.search');
@endphp
<div class="flight-search-card">
    <form method="POST" action="{{ route($flightsRoutePrefix . '.flights.search') }}" id="flightSearchForm">
        @csrf
        <div class="trip-type-tabs" role="group" aria-label="Trip type">
            <label>
                <input type="radio" name="trip_type" value="roundtrip" @checked($tripType === 'roundtrip')>
                <span>Round trip</span>
            </label>
            <label>
                <input type="radio" name="trip_type" value="oneway" @checked($tripType === 'oneway')>
                <span>One way</span>
            </label>
        </div>

        <p class="small text-muted mb-3"><i class="fas fa-info-circle me-1"></i>Type a <strong>city</strong> or <strong>airport name</strong> — no need to know airport codes.</p>

        <div class="row g-3 align-items-end">
            <div class="col-lg-4 col-md-5">
                @include('flights.partials.airport-picker', [
                    'name' => 'origin',
                    'id' => 'origin',
                    'value' => $origin,
                    'label' => 'From',
                    'placeholder' => 'e.g. London, Dubai, New York',
                    'searchUrl' => $airportSearchUrl,
                ])
            </div>
            <div class="col-12 d-md-none d-grid">
                <button type="button" class="btn btn-outline-secondary btn-sm" id="swapAirportsMobile"><i class="fas fa-exchange-alt me-1"></i> Swap</button>
            </div>
            <div class="col-lg-1 col-md-2 d-none d-md-flex justify-content-center">
                <button type="button" class="flight-swap-btn" id="swapAirports" title="Swap" aria-label="Swap origin and destination">
                    <i class="fas fa-exchange-alt"></i>
                </button>
            </div>
            <div class="col-lg-4 col-md-5">
                @include('flights.partials.airport-picker', [
                    'name' => 'destination',
                    'id' => 'destination',
                    'value' => $destination,
                    'label' => 'To',
                    'placeholder' => 'e.g. Paris, Singapore, Karachi',
                    'searchUrl' => $airportSearchUrl,
                ])
            </div>
            <div class="col-md-6 col-lg-3">
                <label class="flight-field-label" for="departure_date">Depart</label>
                <input type="date" name="departure_date" id="departure_date" class="form-control" required value="{{ $departure }}">
            </div>
            <div class="col-md-6 col-lg-3" id="returnDateWrap">
                <label class="flight-field-label" for="return_date">Return</label>
                <input type="date" name="return_date" id="return_date" class="form-control" value="{{ $returnDate }}">
            </div>
            <div class="col-md-6 col-lg-2">
                <label class="flight-field-label" for="adults">Passengers</label>
                <select name="adults" id="adults" class="form-select">
                    @for ($i = 1; $i <= 9; $i++)
                        <option value="{{ $i }}" @selected($adults === $i)>{{ $i }} Adult{{ $i > 1 ? 's' : '' }}</option>
                    @endfor
                </select>
            </div>
            <div class="col-md-6 col-lg-2 d-grid">
                <button type="submit" class="btn btn-primary btn-lg" @disabled(!$travelportReady)>
                    <i class="fas fa-search me-2"></i>Search
                </button>
            </div>
        </div>

        <div class="popular-routes mt-3">
            <span class="small text-muted me-1">Popular routes:</span>
            <button type="button" data-origin="LHR" data-destination="JFK" data-o-label="London — Heathrow (LHR)" data-d-label="New York — John F. Kennedy (JFK)">London → New York</button>
            <button type="button" data-origin="LHR" data-destination="DXB" data-o-label="London — Heathrow (LHR)" data-d-label="Dubai — Dubai Intl (DXB)">London → Dubai</button>
            <button type="button" data-origin="LHR" data-destination="CDG" data-o-label="London — Heathrow (LHR)" data-d-label="Paris — Charles de Gaulle (CDG)">London → Paris</button>
            <button type="button" data-origin="DEL" data-destination="DXB" data-o-label="Delhi — Indira Gandhi (DEL)" data-d-label="Dubai — Dubai Intl (DXB)">Delhi → Dubai</button>
            <button type="button" data-origin="ISB" data-destination="DXB" data-o-label="Islamabad — Islamabad Intl (ISB)" data-d-label="Dubai — Dubai Intl (DXB)">Islamabad → Dubai</button>
        </div>
    </form>
</div>
