@php
    $idPrefix = $idPrefix ?? '';
    $clampedPax = \App\Support\FlightPassengerLimits::clampCounts(
        (int) ($homeFlightInput['adults'] ?? 1),
        (int) ($homeFlightInput['children'] ?? 0),
        (int) ($homeFlightInput['infants'] ?? $homeFlightInput['infant'] ?? 0),
    );
    $adults = $clampedPax['adults'];
    $children = $clampedPax['children'];
    $infants = $clampedPax['infants'];
    $totalPax = \App\Support\FlightPassengerLimits::total($adults, $children, $infants);
    $cabinClass = (string) ($homeFlightInput['cabin_class'] ?? 'Economy');
    if (! in_array($cabinClass, ['Economy', 'Business', 'First Class'], true)) {
        $cabinClass = 'Economy';
    }
@endphp
<div class="home-pax-cabin-split">
    <div class="form-group dropdown passenger-box home-passenger-only" data-flight-pax-box="1" data-max-passengers="{{ \App\Support\FlightPassengerLimits::MAX_TOTAL }}">
        <div class="passenger-class" role="menu" data-bs-toggle="dropdown" aria-expanded="false">
            <label>Passengers</label>
            <div class="form-group-icon">
                <div class="passenger-total">
                    <span class="passenger-total-amount">{{ $totalPax }}</span> Passenger{{ $totalPax === 1 ? '' : 's' }}
                </div>
                <i class="fal fa-user-tie-hair"></i>
            </div>
        </div>
        <div class="dropdown-menu dropdown-menu-end">
            <div class="dropdown-item">
                <div class="passenger-item">
                    <div class="passenger-info">
                        <h6>Adults</h6>
                        <p>12+ Years</p>
                    </div>
                    <div class="passenger-qty" data-pax-role="adult">
                        <button type="button" class="minus-btn" @disabled($adults <= 1)><i class="far fa-minus"></i></button>
                        <input type="text" name="adults" class="qty-amount passenger-adult"
                            value="{{ $adults }}" readonly>
                        <button type="button" class="plus-btn" @disabled($totalPax >= \App\Support\FlightPassengerLimits::MAX_TOTAL)><i class="far fa-plus"></i></button>
                    </div>
                </div>
            </div>
            <div class="dropdown-item">
                <div class="passenger-item">
                    <div class="passenger-info">
                        <h6>Children</h6>
                        <p>2–12 Years</p>
                    </div>
                    <div class="passenger-qty" data-pax-role="child">
                        <button type="button" class="minus-btn" @disabled($children <= 0)><i class="far fa-minus"></i></button>
                        <input type="text" name="children" class="qty-amount passenger-children" value="{{ $children }}" readonly>
                        <button type="button" class="plus-btn" @disabled($totalPax >= \App\Support\FlightPassengerLimits::MAX_TOTAL)><i class="far fa-plus"></i></button>
                    </div>
                </div>
            </div>
            <div class="dropdown-item">
                <div class="passenger-item">
                    <div class="passenger-info">
                        <h6>Infant</h6>
                        <p>1–2 Years (under 2)</p>
                    </div>
                    <div class="passenger-qty" data-pax-role="infant">
                        <button type="button" class="minus-btn" @disabled($infants <= 0)><i class="far fa-minus"></i></button>
                        <input type="text" name="infants" class="qty-amount passenger-infant" value="{{ $infants }}" readonly>
                        <button type="button" class="plus-btn" @disabled($totalPax >= \App\Support\FlightPassengerLimits::MAX_TOTAL)><i class="far fa-plus"></i></button>
                    </div>
                </div>
            </div>
            <div class="dropdown-item">
                <p class="small text-muted mb-0 px-1">Max {{ \App\Support\FlightPassengerLimits::MAX_TOTAL }} passengers total. Age is checked from date of birth / passport at booking.</p>
            </div>
        </div>
    </div>

    <div class="form-group dropdown passenger-box home-cabin-only">
        <div class="passenger-class" role="menu" data-bs-toggle="dropdown" aria-expanded="false">
            <label>Cabin class</label>
            <div class="form-group-icon">
                <div class="passenger-total">
                    <span class="passenger-class-name">{{ $cabinClass }}</span>
                </div>
                <i class="fal fa-seat-airline"></i>
            </div>
        </div>
        <div class="dropdown-menu dropdown-menu-end">
            <div class="dropdown-item">
                <div class="passenger-class-info">
                    <div class="form-check">
                        <input class="form-check-input" type="radio" value="Economy" name="cabin_class" id="{{ $idPrefix }}cabin-class1" @checked($cabinClass === 'Economy')>
                        <label class="form-check-label" for="{{ $idPrefix }}cabin-class1">Economy</label>
                    </div>
                    <div class="form-check">
                        <input class="form-check-input" type="radio" value="Business" name="cabin_class" id="{{ $idPrefix }}cabin-class2" @checked($cabinClass === 'Business')>
                        <label class="form-check-label" for="{{ $idPrefix }}cabin-class2">Business</label>
                    </div>
                    <div class="form-check">
                        <input class="form-check-input" type="radio" value="First Class" name="cabin_class" id="{{ $idPrefix }}cabin-class3" @checked($cabinClass === 'First Class')>
                        <label class="form-check-label" for="{{ $idPrefix }}cabin-class3">First Class</label>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
