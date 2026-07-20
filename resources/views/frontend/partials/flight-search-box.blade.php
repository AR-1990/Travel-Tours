@php
    $homeFlightInput = $flightSearchInput ?? [];
    $originCode = strtoupper((string) ($homeFlightInput['origin'] ?? 'JFK'));
    $destCode = strtoupper((string) ($homeFlightInput['destination'] ?? 'LAX'));
    $originAirport = \App\Support\AirportDirectory::find($originCode);
    $destAirport = \App\Support\AirportDirectory::find($destCode);
    $searchSubmitLabel = $searchSubmitLabel ?? 'Search Now';
    $tripType = \Illuminate\Support\Str::of((string) ($homeFlightInput['trip_type'] ?? 'oneway'))->lower();
    $isRound = in_array((string) $tripType, ['roundtrip', 'round-way', 'round_way'], true);
@endphp
<div class="search-area" id="home-flight-search">
    <div class="container">
        <div class="search-wrapper home-flight-search-only">
            <div class="search-header">
                <div class="search-nav">
                    <ul class="nav nav-pills" role="tablist">
                        <li class="nav-item home-flights-tab" role="presentation">
                            <span class="nav-link active"><i class="far fa-plane-departure"></i> Flights</span>
                        </li>
                        <li class="nav-item" role="presentation">
                            <span class="nav-link nav-link-disabled" title="Coming soon"><i class="far fa-hotel"></i> Hotels</span>
                        </li>
                        <li class="nav-item" role="presentation">
                            <span class="nav-link nav-link-disabled" title="Coming soon"><i class="far fa-person-biking-mountain"></i> Activity</span>
                        </li>
                        <li class="nav-item" role="presentation">
                            <span class="nav-link nav-link-disabled" title="Coming soon"><i class="far fa-car-building"></i> Holiday Package</span>
                        </li>
                        <li class="nav-item" role="presentation">
                            <span class="nav-link nav-link-disabled" title="Coming soon"><i class="far fa-car"></i> Cars</span>
                        </li>
                        <li class="nav-item" role="presentation">
                            <span class="nav-link nav-link-disabled" title="Coming soon"><i class="far fa-ship"></i> Cruises</span>
                        </li>
                        <li class="nav-item" role="presentation">
                            <span class="nav-link nav-link-disabled" title="Coming soon"><i class="far fa-earth-americas"></i> Tours</span>
                        </li>
                    </ul>
                </div>
            </div>
            @if(session('success') || session('error'))
                <div class="alert {{ session('error') ? 'alert-danger' : 'alert-success' }} home-flight-alert mx-3">
                    {{ session('error') ?? session('success') }}
                </div>
            @endif
            <div class="tab-content" id="pills-tabContent">
                <div class="tab-pane fade show active" id="pills-1" role="tabpanel" tabindex="0">
                    <div class="flight-search ft-group home-flight-search">
                        <div class="search-form">
                            <form action="{{ route('frontend.flights.search') }}" method="POST" id="homeFlightSearchForm">
                                @csrf
                                <div class="flight-type">
                                    <div class="form-check form-check-inline">
                                        <input class="form-check-input" type="radio"
                                            {{ ! $isRound ? 'checked' : '' }}
                                            value="one-way" name="trip_type" id="flight-type1">
                                        <label class="form-check-label" for="flight-type1">One Way</label>
                                    </div>
                                    <div class="form-check form-check-inline">
                                        <input class="form-check-input" type="radio"
                                            {{ $isRound ? 'checked' : '' }}
                                            value="round-way" name="trip_type" id="flight-type2">
                                        <label class="form-check-label" for="flight-type2">Round Trip</label>
                                    </div>
                                </div>
                                <div class="flight-search-wrapper">
                                    <div class="flight-search-content">
                                        <div class="flight-search-item">
                                            <div class="row align-items-stretch home-flight-fields">
                                                <div class="col-lg-3 col-md-6">
                                                    <div class="form-group home-airport-field">
                                                        <div class="airport-picker home-airport-picker"
                                                            data-field="origin"
                                                            data-initial-code="{{ $originCode }}"
                                                            data-initial-label="{{ $originAirport['label'] ?? $originCode }}"
                                                            data-search-url="{{ route('api.airports.search') }}">
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
                                                                <input type="hidden" name="origin" id="home_origin" class="airport-picker-code" value="{{ $originCode }}" required>
                                                            </div>
                                                            <ul id="home_origin_list" class="airport-picker-list" role="listbox" hidden></ul>
                                                        </div>
                                                    </div>
                                                </div>
                                                <div class="col-lg-3 col-md-6">
                                                    <div class="form-group home-airport-field home-airport-field-to">
                                                        <button type="button" class="search-form-swap home-swap-airports" id="homeSwapAirports" title="Swap airports" aria-label="Swap from and to">
                                                            <i class="far fa-repeat"></i>
                                                        </button>
                                                        <div class="airport-picker home-airport-picker"
                                                            data-field="destination"
                                                            data-initial-code="{{ $destCode }}"
                                                            data-initial-label="{{ $destAirport['label'] ?? $destCode }}"
                                                            data-search-url="{{ route('api.airports.search') }}">
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
                                                                <input type="hidden" name="destination" id="home_destination" class="airport-picker-code" value="{{ $destCode }}" required>
                                                            </div>
                                                            <ul id="home_destination_list" class="airport-picker-list" role="listbox" hidden></ul>
                                                        </div>
                                                    </div>
                                                </div>
                                                <div class="col-lg-3 col-md-6">
                                                    <div class="form-group">
                                                        <div class="search-form-date">
                                                            <div class="search-form-journey">
                                                                <label>Journey Date</label>
                                                                <div class="form-group-icon">
                                                                    <input type="text" name="departure_date"
                                                                        class="form-control date-picker journey-date"
                                                                        value="{{ isset($homeFlightInput['departure_date']) ? \Carbon\Carbon::parse($homeFlightInput['departure_date'])->format('m/d/Y') : '' }}"
                                                                        required>
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
                                                    <div class="form-group dropdown passenger-box">
                                                        <div class="passenger-class" role="menu" data-bs-toggle="dropdown" aria-expanded="false">
                                                            <label>Passenger, Class</label>
                                                            <div class="form-group-icon">
                                                                <div class="passenger-total">
                                                                    <span class="passenger-total-amount">{{ $homeFlightInput['adults'] ?? 1 }}</span> Passenger
                                                                </div>
                                                                <i class="fal fa-user-tie-hair"></i>
                                                            </div>
                                                            <p class="passenger-class-name">Economy</p>
                                                        </div>
                                                        <div class="dropdown-menu dropdown-menu-end">
                                                            <div class="dropdown-item">
                                                                <div class="passenger-item">
                                                                    <div class="passenger-info">
                                                                        <h6>Adults</h6>
                                                                        <p>12+ Years</p>
                                                                    </div>
                                                                    <div class="passenger-qty">
                                                                        <button type="button" class="minus-btn"><i class="far fa-minus"></i></button>
                                                                        <input type="text" name="adults" class="qty-amount passenger-adult"
                                                                            value="{{ $homeFlightInput['adults'] ?? 1 }}" readonly>
                                                                        <button type="button" class="plus-btn"><i class="far fa-plus"></i></button>
                                                                    </div>
                                                                </div>
                                                            </div>
                                                            <div class="dropdown-item">
                                                                <div class="passenger-item">
                                                                    <div class="passenger-info">
                                                                        <h6>Children</h6>
                                                                        <p>2-12 Years</p>
                                                                    </div>
                                                                    <div class="passenger-qty">
                                                                        <button type="button" class="minus-btn"><i class="far fa-minus"></i></button>
                                                                        <input type="text" name="children" class="qty-amount passenger-children" value="0" readonly>
                                                                        <button type="button" class="plus-btn"><i class="far fa-plus"></i></button>
                                                                    </div>
                                                                </div>
                                                            </div>
                                                            <div class="dropdown-item">
                                                                <div class="passenger-item">
                                                                    <div class="passenger-info">
                                                                        <h6>Infant</h6>
                                                                        <p>Below 2 Years</p>
                                                                    </div>
                                                                    <div class="passenger-qty">
                                                                        <button type="button" class="minus-btn"><i class="far fa-minus"></i></button>
                                                                        <input type="text" name="infant" class="qty-amount passenger-infant" value="0" readonly>
                                                                        <button type="button" class="plus-btn"><i class="far fa-plus"></i></button>
                                                                    </div>
                                                                </div>
                                                            </div>
                                                            <div class="dropdown-item">
                                                                <h6 class="mb-3 mt-2">Cabin Class</h6>
                                                                <div class="passenger-class-info">
                                                                    <div class="form-check">
                                                                        <input class="form-check-input" type="radio" value="Economy" name="cabin_class" id="cabin-class1" checked>
                                                                        <label class="form-check-label" for="cabin-class1">Economy</label>
                                                                    </div>
                                                                    <div class="form-check">
                                                                        <input class="form-check-input" type="radio" value="Business" name="cabin_class" id="cabin-class2">
                                                                        <label class="form-check-label" for="cabin-class2">Business</label>
                                                                    </div>
                                                                    <div class="form-check">
                                                                        <input class="form-check-input" type="radio" value="First Class" name="cabin_class" id="cabin-class3">
                                                                        <label class="form-check-label" for="cabin-class3">First Class</label>
                                                                    </div>
                                                                </div>
                                                            </div>
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="search-btn">
                                        <button type="submit" class="theme-btn">
                                            <span class="far fa-search"></span> {{ $searchSubmitLabel }}
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
