@php
    $segments = $sol['segments'] ?? (($sol['journeys'][0]['segments'] ?? []) ?: []);
    $first = $segments[0] ?? [];
    $last = $segments[count($segments) - 1] ?? $first;
    $dep = \App\Support\FlightDisplay::parseDateTime($first['departure'] ?? null);
    $arr = \App\Support\FlightDisplay::parseDateTime($last['arrival'] ?? null);
    $displayCurrency = $displayCurrency ?? \App\Support\DisplayCurrency::resolve(auth()->user());
    $price = \App\Support\FlightDisplay::forDisplayCurrency($sol['total_price'] ?? null, $displayCurrency);
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
    $stopBucket = \App\Support\FlightDisplay::stopBucket($sol);
    $stops = (int) $stopBucket;
    if ($stopBucket === '2') {
        $outboundSegs = is_array($sol['journeys'][0]['segments'] ?? null) && $sol['journeys'][0]['segments'] !== []
            ? $sol['journeys'][0]['segments']
            : $segments;
        $stops = max(0, count($outboundSegs) - 1);
    }
    $airlineCode = \App\Support\FlightDisplay::primaryAirlineCode($sol);
    $airlineCodes = \App\Support\FlightDisplay::airlineCodes($sol);
    if ($airlineCodes === []) {
        $airlineCodes = [$airlineCode];
    }
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
    $durationLabel = \App\Support\FlightDisplay::formatDuration(
        (string) ($sol['journeys'][0]['travel_time'] ?? $sol['travel_time'] ?? '')
    );
    $detailJourneys = \App\Support\FlightDisplay::solutionJourneys($sol);
    $detailsId = 'flight-details-'.md5((string) ($sol['key'] ?? ($airlineCode.'|'.$sortPrice.'|'.($first['departure'] ?? ''))));
@endphp
<div
    class="col-lg-12 js-flight-result"
    data-price="{{ number_format($sortPrice, 2, '.', '') }}"
    data-stops="{{ $stopBucket }}"
    data-airline="{{ $airlineCode }}"
    data-airlines="{{ implode(',', $airlineCodes) }}"
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
                    <div class="flight-booking-time flex-column align-items-stretch w-100">
                        @foreach($detailJourneys as $journey)
                            @if(count($detailJourneys) > 1)
                                <div class="small text-muted fw-semibold mb-1">{{ $journey['label'] }}</div>
                            @endif
                            @foreach(($journey['segments'] ?? []) as $seg)
                                @php
                                    if (! is_array($seg)) {
                                        continue;
                                    }
                                    $segDep = \App\Support\FlightDisplay::parseDateTime($seg['departure'] ?? null);
                                    $segArr = \App\Support\FlightDisplay::parseDateTime($seg['arrival'] ?? null);
                                    $segOrigin = trim((string) ($seg['origin_city'] ?? ''));
                                    if ($segOrigin === '') {
                                        $segOrigin = \App\Support\FlightDisplay::airportCity($seg['origin'] ?? null);
                                    }
                                    $segDest = trim((string) ($seg['destination_city'] ?? ''));
                                    if ($segDest === '') {
                                        $segDest = \App\Support\FlightDisplay::airportCity($seg['destination'] ?? null);
                                    }
                                    $segFlight = trim((string) ($seg['airline_name'] ?? ''));
                                    if ($segFlight === '') {
                                        $segFlight = \App\Support\FlightDisplay::flightLabel($seg['carrier'] ?? null, $seg['flight_number'] ?? null);
                                    } else {
                                        $fn = trim((string) ($seg['flight_number'] ?? ''));
                                        $segFlight = $fn !== '' ? $segFlight.' '.$fn : $segFlight;
                                    }
                                    $segCabin = \App\Support\FlightDisplay::cabinLabel(
                                        $seg['cabin'] ?? ($journey['cabin'] ?? ($sol['cabin'] ?? null))
                                    );
                                    $segClass = \App\Support\FlightDisplay::bookingClass($seg);
                                @endphp
                                <div class="d-flex flex-wrap align-items-center gap-3 mb-2">
                                    <div class="start-time-info">
                                        <h6 class="start-time-text mb-0">{{ $segDep['time'] ?? '—' }}</h6>
                                        <span class="flight-destination">{{ $segOrigin }}</span>
                                        <small class="d-block text-muted">{{ $segDep['date'] ?? '' }}@if(!empty($segDep['weekday'])), {{ $segDep['weekday'] }}@endif</small>
                                    </div>
                                    <div class="text-center px-2 small text-muted">
                                        <i class="fal fa-plane"></i>
                                        <div>{{ $segFlight }}</div>
                                        @if($segCabin !== '' || $segClass !== '')
                                            <div class="mt-1">
                                                @if($segCabin !== '')
                                                    <span class="badge bg-light text-dark">Cabin {{ $segCabin }}</span>
                                                @endif
                                                @if($segClass !== '')
                                                    <span class="badge bg-light text-dark">Class {{ $segClass }}</span>
                                                @endif
                                            </div>
                                        @endif
                                    </div>
                                    <div class="start-time-info">
                                        <h6 class="end-time-text mb-0">{{ $segArr['time'] ?? '—' }}</h6>
                                        <span class="flight-destination">{{ $segDest }}</span>
                                        <small class="d-block text-muted">{{ $segArr['date'] ?? '' }}</small>
                                    </div>
                                </div>
                            @endforeach
                        @endforeach
                    </div>
                    <div class="flight-booking-duration">
                        <span class="duration-text">{{ $stops === 0 ? 'Non Stop' : $stops.' Stop'.($stops > 1 ? 's' : '') }}@if($durationLabel !== '') · {{ $durationLabel }}@endif</span>
                        <button type="button" class="btn btn-link btn-sm p-0 js-toggle-flight-details" data-bs-toggle="collapse" data-bs-target="#{{ $detailsId }}" aria-expanded="false">
                            More details
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
                    @php
                        $basePriceLabel = \App\Support\FlightDisplay::forDisplayCurrency($sol['base_price'] ?? null, $displayCurrency);
                    @endphp
                    @if($basePriceLabel)
                        <small class="d-block text-muted mt-1">Base {{ $basePriceLabel['currency'] }} {{ $basePriceLabel['amount'] }}</small>
                    @elseif(!empty($sol['base_price']))
                        <small class="d-block text-muted mt-1">Base {{ $sol['base_price'] }}</small>
                    @endif
                    @foreach(\App\Support\FlightDisplay::perPaxPriceLines(
                        is_array($sol['passenger_per_pax'] ?? null) ? $sol['passenger_per_pax'] : null,
                        (string) ($price['currency'] ?? ($sol['currency'] ?? 'USD'))
                    ) as $paxLine)
                        <small class="d-block text-muted">{{ $paxLine }}</small>
                    @endforeach
                    @php
                        $cardCabin = \App\Support\FlightDisplay::cabinLabel($sol['cabin'] ?? ($detailJourneys[0]['cabin'] ?? null));
                        $cardClass = \App\Support\FlightDisplay::bookingClass($first);
                    @endphp
                    @if($cardCabin !== '' || $cardClass !== '')
                        <small class="d-block text-muted mt-1">
                            @if($cardCabin !== '')Cabin {{ $cardCabin }}@endif
                            @if($cardCabin !== '' && $cardClass !== '') · @endif
                            @if($cardClass !== '')Class {{ $cardClass }}@endif
                        </small>
                    @endif
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
            @foreach($detailJourneys as $journey)
                @if(count($detailJourneys) > 1)
                    <div class="small fw-semibold text-dark mb-1">{{ $journey['label'] }}</div>
                @endif
                <ul class="list-unstyled mb-2 small">
                    @foreach(($journey['segments'] ?? []) as $seg)
                        @php
                            $segDep = \App\Support\FlightDisplay::parseDateTime($seg['departure'] ?? null);
                            $segArr = \App\Support\FlightDisplay::parseDateTime($seg['arrival'] ?? null);
                            $segCabin = \App\Support\FlightDisplay::cabinLabel(
                                $seg['cabin'] ?? ($journey['cabin'] ?? ($sol['cabin'] ?? null))
                            );
                            $segClass = \App\Support\FlightDisplay::bookingClass(is_array($seg) ? $seg : []);
                            $depStamp = trim(($segDep['date'] ?? '').(! empty($segDep['weekday']) ? ', '.$segDep['weekday'] : '').(! empty($segDep['time']) ? ' '.$segDep['time'] : ''));
                            $arrStamp = '';
                            if (! empty($segArr['time'])) {
                                $arrDate = (($segArr['date'] ?? '') !== ($segDep['date'] ?? '')) ? trim(($segArr['date'] ?? '').' ') : '';
                                $arrStamp = $arrDate.$segArr['time'];
                            }
                        @endphp
                        <li class="mb-2">
                            <strong>{{ $seg['carrier'] ?? '' }}{{ $seg['flight_number'] ?? '' }}</strong>
                            {{ $seg['origin'] ?? '' }} → {{ $seg['destination'] ?? '' }}
                            @if($depStamp !== '')
                                · {{ $depStamp }}@if($arrStamp !== '') – {{ $arrStamp }}@endif
                            @endif
                            @if($segCabin !== '')
                                · Cabin {{ $segCabin }}
                            @endif
                            @if($segClass !== '')
                                · Class {{ $segClass }}
                            @endif
                        </li>
                    @endforeach
                </ul>
            @endforeach
            @if(!empty($sol['fare_basis']) || !empty($sol['fare_brand']))
                <div class="small text-muted mt-1">
                    @if(!empty($sol['fare_brand']))
                        Fare: <strong>{{ $sol['fare_brand'] }}</strong>
                    @endif
                    @if(!empty($sol['fare_basis']))
                        · Fare basis <code>{{ $sol['fare_basis'] }}</code>
                    @endif
                </div>
            @endif
        </div>
    </div>
</div>
