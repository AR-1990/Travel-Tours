@php
    $segments = $sol['segments'] ?? (($sol['journeys'][0]['segments'] ?? []) ?: []);
    $first = $segments[0] ?? [];
    $last = $segments[count($segments) - 1] ?? $first;
    $dep = \App\Support\FlightDisplay::parseDateTime($first['departure'] ?? null);
    $arr = \App\Support\FlightDisplay::parseDateTime($last['arrival'] ?? null);
    $price = \App\Support\FlightDisplay::parsePrice($sol['total_price'] ?? null);
    $carrier = $sol['plating_carrier'] ?? ($first['carrier'] ?? '—');
    $stops = max(0, count($segments) - 1);
@endphp
<div class="col-lg-12">
    <div class="flight-booking-item wow fadeInUp">
        <div class="flight-booking-wrapper">
            <div class="flight-booking-info">
                <div class="flight-booking-content">
                    <div class="flight-booking-airline">
                        <div class="flight-airline-img">
                            <span class="flight-airline-code">{{ $carrier }}</span>
                        </div>
                        <h5 class="flight-airline-name">{{ \App\Support\FlightDisplay::flightLabel($carrier, $first['flight_number'] ?? null) }}</h5>
                    </div>
                    <div class="flight-booking-time">
                        <div class="start-time">
                            <div class="start-time-icon">
                                <i class="fal fa-plane-departure"></i>
                            </div>
                            <div class="start-time-info">
                                <h6 class="start-time-text">{{ $dep['time'] ?? '—' }}</h6>
                                <span class="flight-destination">{{ \App\Support\FlightDisplay::airportCity($first['origin'] ?? null) }}</span>
                            </div>
                        </div>
                        <div class="flight-stop">
                            <span class="flight-stop-number">{{ $stops === 0 ? 'Non Stop' : $stops.' Stop'.($stops > 1 ? 's' : '') }}</span>
                            <div class="flight-stop-arrow"></div>
                        </div>
                        <div class="end-time">
                            <div class="start-time-icon">
                                <i class="fal fa-plane-arrival"></i>
                            </div>
                            <div class="start-time-info">
                                <h6 class="end-time-text">{{ $arr['time'] ?? '—' }}</h6>
                                <span class="flight-destination">{{ \App\Support\FlightDisplay::airportCity($last['destination'] ?? null) }}</span>
                            </div>
                        </div>
                    </div>
                    <div class="flight-booking-duration">
                        <span class="duration-text">{{ $dep['date'] ?? '' }}</span>
                    </div>
                </div>
            </div>
            <div class="flight-booking-price">
                <div class="price-info">
                    @if($price)
                        <span class="price-amount">{{ $price['currency'] }} {{ $price['amount'] }}</span>
                    @else
                        <span class="price-amount">{{ $sol['total_price'] ?? '—' }}</span>
                    @endif
                    @if(!empty($sol['base_price']))
                        <small class="d-block text-muted mt-1">Base {{ $sol['base_price'] }}</small>
                    @endif
                </div>
                <form method="POST" action="{{ route('frontend.flights.price') }}" class="flight-price-form mt-2">
                    @csrf
                    <input type="hidden" name="solution_key" value="{{ $sol['key'] ?? '' }}">
                    <button type="submit" class="theme-btn flight-price-btn"
                            @disabled(!($travelportReady ?? false) || empty($sol['key']))>
                        Price &amp; hold<i class="fas fa-arrow-circle-right"></i>
                    </button>
                </form>
            </div>
        </div>
    </div>
</div>
