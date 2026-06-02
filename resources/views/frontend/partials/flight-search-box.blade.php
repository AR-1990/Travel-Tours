@php
    $homeFlightInput = $flightSearchInput ?? [];
    $airportOptions = $airportOptions ?? [];
    $searchSubmitLabel = $searchSubmitLabel ?? 'Search Now';
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
                            <form action="{{ route('frontend.flights.search') }}" method="POST">
                                @csrf
                                <div class="flight-type">
                                    <div class="form-check form-check-inline">
                                        <input class="form-check-input" type="radio"
                                            {{ in_array($homeFlightInput['trip_type'] ?? 'oneway', ['oneway', 'one-way'], true) ? 'checked' : '' }}
                                            value="one-way" name="trip_type" id="flight-type1">
                                        <label class="form-check-label" for="flight-type1">One Way</label>
                                    </div>
                                    <div class="form-check form-check-inline">
                                        <input class="form-check-input" type="radio"
                                            {{ ($homeFlightInput['trip_type'] ?? '') === 'roundtrip' ? 'checked' : '' }}
                                            value="round-way" name="trip_type" id="flight-type2">
                                        <label class="form-check-label" for="flight-type2">Round Way</label>
                                    </div>
                                </div>
                                <div class="flight-search-wrapper">
                                    <div class="flight-search-content">
                                        <div class="flight-search-item">
                                            <div class="row">
                                                <div class="col-lg-3">
                                                    <div class="form-group">
                                                        <label>From</label>
                                                        <select name="origin" class="form-control home-airport-select" aria-describedby="home-origin-hint">
                                                            @foreach($airportOptions as $code => $label)
                                                                <option value="{{ $code }}" @selected(($homeFlightInput['origin'] ?? 'JFK') === $code)>{{ $code }}</option>
                                                            @endforeach
                                                        </select>
                                                        <p class="home-airport-hint" id="home-origin-hint">{{ $airportOptions[$homeFlightInput['origin'] ?? 'JFK'] ?? '' }}</p>
                                                    </div>
                                                </div>
                                                <div class="col-lg-3">
                                                    <div class="form-group">
                                                        <div class="search-form-swap"><i class="far fa-repeat"></i></div>
                                                        <label>To</label>
                                                        <select name="destination" class="form-control home-airport-select" aria-describedby="home-destination-hint">
                                                            @foreach($airportOptions as $code => $label)
                                                                <option value="{{ $code }}" @selected(($homeFlightInput['destination'] ?? 'LAX') === $code)>{{ $code }}</option>
                                                            @endforeach
                                                        </select>
                                                        <p class="home-airport-hint" id="home-destination-hint">{{ $airportOptions[$homeFlightInput['destination'] ?? 'LAX'] ?? '' }}</p>
                                                    </div>
                                                </div>
                                                <div class="col-lg-3">
                                                    <div class="form-group">
                                                        <div class="search-form-date">
                                                            <div class="search-form-journey">
                                                                <label>Journey Date</label>
                                                                <div class="form-group-icon">
                                                                    <input type="text" name="departure_date"
                                                                        class="form-control date-picker journey-date"
                                                                        value="{{ isset($homeFlightInput['departure_date']) ? \Carbon\Carbon::parse($homeFlightInput['departure_date'])->format('m/d/Y') : '' }}">
                                                                    <i class="fal fa-calendar-days"></i>
                                                                </div>
                                                                <p class="journey-day-name"></p>
                                                            </div>
                                                            <div class="search-form-return">
                                                                <label>Return Date</label>
                                                                <div class="form-group-icon">
                                                                    <input type="text" name="return_date"
                                                                        class="form-control date-picker return-date"
                                                                        value="{{ isset($homeFlightInput['return_date']) ? \Carbon\Carbon::parse($homeFlightInput['return_date'])->format('m/d/Y') : '' }}">
                                                                </div>
                                                                <p class="return-day-name"></p>
                                                            </div>
                                                        </div>
                                                    </div>
                                                </div>
                                                <div class="col-lg-3">
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
