@php
    $segments = $sol['segments'] ?? (($sol['journeys'][0]['segments'] ?? []) ?: []);
    $first = $segments[0] ?? [];
    $last = $segments[count($segments) - 1] ?? $first;
    $dep = \App\Support\FlightDisplay::parseDateTime($first['departure'] ?? null);
    $arr = \App\Support\FlightDisplay::parseDateTime($last['arrival'] ?? null);
    $price = \App\Support\FlightDisplay::parsePrice($sol['total_price'] ?? null);
    $carrier = $sol['plating_carrier'] ?? ($first['carrier'] ?? '—');
    $airlineLabel = trim((string) ($sol['airline_name'] ?? $first['airline_name'] ?? ''));
    if ($airlineLabel === '') {
        $airlineLabel = \App\Support\FlightDisplay::flightLabel($carrier, $first['flight_number'] ?? null);
    } else {
        $flightNo = trim((string) ($first['flight_number'] ?? ''));
        $airlineLabel = $flightNo !== '' ? $airlineLabel.' '.$flightNo : $airlineLabel;
    }
    $originLabel = trim((string) ($first['origin_city'] ?? ''));
    if ($originLabel === '') {
        $originLabel = \App\Support\FlightDisplay::airportCity($first['origin'] ?? null);
    }
    $destLabel = trim((string) ($last['destination_city'] ?? ''));
    if ($destLabel === '') {
        $destLabel = \App\Support\FlightDisplay::airportCity($last['destination'] ?? null);
    }
    $stops = max(0, count($segments) - 1);
    $stopBucket = $stops === 0 ? '0' : ($stops === 1 ? '1' : '2');
    $airlineCode = strtoupper(preg_replace('/[^A-Za-z0-9]/', '', (string) $carrier) ?: 'XX');
    $airlineNameOnly = trim((string) ($sol['airline_name'] ?? $first['airline_name'] ?? \App\Support\FlightDisplay::airlineName($carrier)));
    $solProvider = strtolower((string) ($sol['provider'] ?? ($flightSearchResult['provider'] ?? 'travelport')));
    $solReady = match ($solProvider) {
        'sunspring' => (bool) ($sunspringReady ?? false),
        'downtown_travel' => (bool) ($downtownTravelReady ?? false),
        default => (bool) ($travelportReady ?? $providerReady ?? false),
    };
    $sortPrice = \App\Support\FlightDisplay::priceSortKey($sol['total_price'] ?? null);
    if (! is_finite($sortPrice) || $sortPrice >= 999999999) {
        $sortPrice = 999999999;
    }
    $durationLabel = trim((string) ($sol['journeys'][0]['travel_time'] ?? $sol['travel_time'] ?? ''));
    $detailsId = 'flight-details-'.md5((string) ($sol['key'] ?? ($airlineCode.'|'.$sortPrice.'|'.($first['departure'] ?? ''))));
@endphp
<div
    class="col-lg-12 js-flight-result"
    data-price="{{ number_format($sortPrice, 2, '.', '') }}"
    data-stops="{{ $stopBucket }}"
    data-airline="{{ $airlineCode }}"
    data-airline-name="{{ e($airlineNameOnly !== '' ? $airlineNameOnly : $airlineCode) }}"
>
    <div class="flight-booking-item wow fadeInUp">
        <div class="flight-booking-wrapper">
            <div class="flight-booking-info">
                <div class="flight-booking-content">
                    <div class="flight-booking-airline">
                        <div class="flight-airline-img">
                            <span class="flight-airline-code">{{ $carrier }}</span>
                        </div>
                        <div>
                            <h5 class="flight-airline-name mb-1">{{ $airlineLabel }}</h5>
                            @include('flights.partials.provider-badge', [
                                'provider' => $sol['provider'] ?? ($flightSearchResult['provider'] ?? null),
                                'sol' => $sol,
                                'size' => 'sm',
                            ])
                        </div>
                    </div>
                    <div class="flight-booking-time">
                        <div class="start-time">
                            <div class="start-time-icon">
                                <i class="fal fa-plane-departure"></i>
                            </div>
                            <div class="start-time-info">
                                <h6 class="start-time-text">{{ $dep['time'] ?? '—' }}</h6>
                                <span class="flight-destination">{{ $originLabel }}</span>
                            </div>
                        </div>
                        <div class="flight-stop">
                            <span class="flight-stop-number">{{ $stops === 0 ? 'Non Stop' : $stops.' Stop'.($stops > 1 ? 's' : '') }}</span>
                            <div class="flight-stop-arrow"></div>
                            @if($durationLabel !== '')
                                <small class="d-block text-muted mt-1">{{ $durationLabel }}</small>
                            @endif
                        </div>
                        <div class="end-time">
                            <div class="start-time-icon">
                                <i class="fal fa-plane-arrival"></i>
                            </div>
                            <div class="start-time-info">
                                <h6 class="end-time-text">{{ $arr['time'] ?? '—' }}</h6>
                                <span class="flight-destination">{{ $destLabel }}</span>
                            </div>
                        </div>
                    </div>
                    <div class="flight-booking-duration">
                        <span class="duration-text">{{ $dep['date'] ?? '' }}</span>
                        <button type="button" class="btn btn-link btn-sm p-0 js-toggle-flight-details" data-bs-toggle="collapse" data-bs-target="#{{ $detailsId }}" aria-expanded="false">
                            Flight details
                        </button>
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
                    <small class="d-block text-muted mt-1">{{ $stops === 0 ? 'Non-stop' : $stops.' stop'.($stops > 1 ? 's' : '') }} before select</small>
                </div>
                <form method="POST" action="{{ route('frontend.flights.price') }}" class="flight-price-form mt-2">
                    @csrf
                    <input type="hidden" name="solution_key" value="{{ $sol['key'] ?? '' }}">
                    <input type="hidden" name="provider" value="{{ $solProvider }}">
                    <button type="submit" class="theme-btn flight-price-btn"
                            @disabled(! $solReady || empty($sol['key']))>
                        Price &amp; hold<i class="fas fa-arrow-circle-right"></i>
                    </button>
                </form>
            </div>
        </div>
        <div class="collapse border-top px-3 py-3" id="{{ $detailsId }}">
            <div class="small text-muted mb-2">Segment details</div>
            <ul class="list-unstyled mb-0 small">
                @foreach($segments as $seg)
                    <li class="mb-2">
                        <strong>{{ $seg['carrier'] ?? '' }}{{ $seg['flight_number'] ?? '' }}</strong>
                        {{ $seg['origin'] ?? '' }} → {{ $seg['destination'] ?? '' }}
                        · {{ \App\Support\FlightDisplay::parseDateTime($seg['departure'] ?? null)['time'] ?? '' }}
                        – {{ \App\Support\FlightDisplay::parseDateTime($seg['arrival'] ?? null)['time'] ?? '' }}
                        @if(!empty($seg['cabin']))
                            · {{ $seg['cabin'] }}
                        @endif
                        @if(!empty($seg['class_of_service']))
                            · Class {{ $seg['class_of_service'] }}
                        @endif
                    </li>
                @endforeach
            </ul>
            @if(!empty($sol['fare_basis']) || !empty($sol['cabin']) || !empty($sol['fare_brand']))
                <div class="small text-muted mt-2">
                    @if(!empty($sol['fare_brand']))
                        Fare: <strong>{{ $sol['fare_brand'] }}</strong>
                    @elseif(!empty($sol['cabin']))
                        Cabin: <strong>{{ $sol['cabin'] }}</strong>
                    @endif
                    @if(!empty($sol['fare_basis']))
                        · Fare basis <code>{{ $sol['fare_basis'] }}</code>
                    @endif
                </div>
            @endif
        </div>
    </div>
</div>
