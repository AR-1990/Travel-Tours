@if(!empty($searchResult['solutions']))
    @php
        $onPage = count($searchResult['solutions']);
        $listed = (int) ($searchResult['solutions_total'] ?? $onPage);
        $apiTotal = (int) ($searchResult['total_found'] ?? $listed);
        $paginator = $searchResult['solutions_paginator'] ?? null;
    @endphp
    <div class="results-header">
        <div>
            <h2 class="h5 mb-1 d-flex flex-wrap align-items-center gap-2">
                @if($paginator)
                    {{ $paginator->total() }} fare option{{ $paginator->total() !== 1 ? 's' : '' }}
                    <span class="text-muted fw-normal">(page {{ $paginator->currentPage() }} of {{ $paginator->lastPage() }})</span>
                @else
                    {{ $onPage }} fare option{{ $onPage !== 1 ? 's' : '' }}
                @endif
                @include('flights.partials.provider-badge', [
                    'provider' => \App\Support\FlightProvider::fromResult($searchResult ?? null),
                    'size' => 'sm',
                ])
                @if($apiTotal > $listed)
                    <span class="text-muted fw-normal">· {{ $listed }} loaded</span>
                @endif
            </h2>
            @if(!empty($searchInput))
                <p class="text-muted small mb-0">
                    {{ \App\Support\FlightDisplay::tripSummary(
                        $searchInput['origin'] ?? null,
                        $searchInput['destination'] ?? null,
                        $searchInput['departure_date'] ?? null,
                        $searchInput['return_date'] ?? null,
                        (int) ($searchInput['adults'] ?? 1),
                        $searchInput['legs'] ?? null,
                        (int) ($searchInput['children'] ?? 0),
                        (int) ($searchInput['infants'] ?? 0)
                    ) }}
                </p>
            @endif
        </div>
        <a href="{{ route($flightsRoutePrefix . '.flights.search') }}" class="btn btn-outline-secondary btn-sm">
            <i class="fas fa-edit me-1"></i> Modify search
        </a>
    </div>

    @include('flights.partials.result-filters', [
        'filterSolutions' => $searchResult['solutions'] ?? [],
        'resultsListId' => 'agency-flight-results-list',
    ])

    <div id="agency-flight-results-list">
    @foreach($searchResult['solutions'] as $sol)
        @php
            $displayCurrency = $displayCurrency ?? \App\Support\DisplayCurrency::resolve(auth()->user());
            $price = \App\Support\FlightDisplay::forDisplayCurrency($sol['total_price'] ?? null, $displayCurrency);
            $basePriceLabel = \App\Support\FlightDisplay::forDisplayCurrency($sol['base_price'] ?? null, $displayCurrency);
            $journeys = $sol['journeys'] ?? [];
            if ($journeys === [] && !empty($sol['segments'])) {
                $journeys = [['travel_time' => null, 'segments' => $sol['segments']]];
            }
            $carrier = $sol['plating_carrier'] ?? ($sol['segments'][0]['carrier'] ?? '—');
            $carrierName = trim((string) ($sol['airline_name'] ?? $sol['segments'][0]['airline_name'] ?? ''));
            if ($carrierName === '') {
                $carrierName = \App\Support\FlightDisplay::airlineName($carrier);
            }
            $solProvider = strtolower((string) ($sol['provider'] ?? ($searchResult['provider'] ?? 'travelport')));
            $solReady = match ($solProvider) {
                'sunspring' => (bool) ($sunspringReady ?? false),
                'downtown_travel' => (bool) ($downtownTravelReady ?? false),
                default => (bool) ($providerReady ?? $travelportReady ?? false),
            };
            $stopBucket = \App\Support\FlightDisplay::stopBucket($sol);
            $airlineCodes = \App\Support\FlightDisplay::airlineCodes($sol);
            $airlineCode = \App\Support\FlightDisplay::primaryAirlineCode($sol);
            $perPaxLines = \App\Support\FlightDisplay::perPaxPriceLines(
                is_array($sol['passenger_per_pax'] ?? null) ? $sol['passenger_per_pax'] : null,
                (string) ($price['currency'] ?? ($sol['currency'] ?? 'USD'))
            );
        @endphp
        <article
            class="flight-result-card js-flight-result"
            data-stops="{{ $stopBucket }}"
            data-airline="{{ $airlineCode }}"
            data-airlines="{{ implode(',', $airlineCodes) }}"
        >
            <div class="row align-items-start g-3">
                <div class="col-auto">
                    <div class="carrier-badge" title="{{ $carrierName }}">{{ $carrier }}</div>
                    <div class="small text-muted text-center mt-1" style="max-width:4.5rem;line-height:1.2;">{{ $carrierName }}</div>
                    <div class="mt-2 text-center">
                        @include('flights.partials.provider-badge', [
                            'provider' => $sol['provider'] ?? ($searchResult['provider'] ?? null),
                            'sol' => $sol,
                            'size' => 'sm',
                        ])
                    </div>
                </div>
                <div class="col">
                    @foreach($journeys as $jIndex => $journey)
                        @php $jSegs = $journey['segments'] ?? []; @endphp
                        @if(count($journeys) > 1)
                            <div class="small text-muted fw-semibold mb-1">{{ $jIndex === 0 ? 'Outbound' : ($jIndex === 1 ? 'Return' : 'Leg '.($jIndex + 1)) }}</div>
                        @endif
                        @foreach($jSegs as $seg)
                            @php
                                $dep = \App\Support\FlightDisplay::parseDateTime($seg['departure'] ?? null);
                                $arr = \App\Support\FlightDisplay::parseDateTime($seg['arrival'] ?? null);
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
                                $segClass = \App\Support\FlightDisplay::bookingClass(is_array($seg) ? $seg : []);
                            @endphp
                            <div class="d-flex flex-wrap align-items-center gap-3 mb-2 pb-2 {{ !$loop->last ? 'border-bottom border-light' : '' }}">
                                <div>
                                    <div class="flight-time">{{ $dep['time'] ?? '—' }}</div>
                                    <div class="flight-airport-code">{{ $segOrigin }}</div>
                                    <div class="small text-muted">{{ $dep['date'] ?? '' }}@if(!empty($dep['weekday'])), {{ $dep['weekday'] }}@endif</div>
                                </div>
                                <div class="text-muted small text-center px-2">
                                    <i class="fas fa-plane text-primary"></i>
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
                                <div>
                                    <div class="flight-time">{{ $arr['time'] ?? '—' }}</div>
                                    <div class="flight-airport-code">{{ $segDest }}</div>
                                    <div class="small text-muted">{{ $arr['date'] ?? '' }}@if(!empty($arr['weekday']) && ($arr['date'] ?? '') !== ($dep['date'] ?? '')), {{ $arr['weekday'] }}@endif</div>
                                </div>
                            </div>
                        @endforeach
                    @endforeach
                </div>
                <div class="col-auto text-end">
                    <div class="price-block">
                        @if($price)
                            <div class="price-currency">{{ $price['currency'] }}</div>
                            <div class="price-amount">{{ $price['amount'] }}</div>
                        @else
                            <div class="price-amount small">{{ $sol['total_price'] ?? '—' }}</div>
                        @endif
                        @if($basePriceLabel)
                            <div class="small text-muted">Base {{ $basePriceLabel['currency'] }} {{ $basePriceLabel['amount'] }}</div>
                        @elseif(!empty($sol['base_price']))
                            <div class="small text-muted">Base {{ $sol['base_price'] }}</div>
                        @endif
                        @foreach($perPaxLines as $paxLine)
                            <div class="small text-muted">{{ $paxLine }}</div>
                        @endforeach
                        <form method="POST" action="{{ route($flightsRoutePrefix . '.flights.price') }}" class="mt-2">
                            @csrf
                            <input type="hidden" name="solution_key" value="{{ $sol['key'] ?? '' }}">
                            <input type="hidden" name="provider" value="{{ $solProvider }}">
                            <button type="submit" class="btn btn-primary btn-sm" @disabled(! $solReady || empty($sol['key']))>
                                Price &amp; hold
                            </button>
                        </form>
                    </div>
                </div>
            </div>
        </article>
    @endforeach
    </div>

    @include('flights.partials.results-pagination')
@elseif(!empty($searchResult) && ($searchResult['ok'] ?? false))
    <div class="empty-results">
        <i class="fas fa-plane-slash fa-2x text-muted mb-3"></i>
        <h3 class="h6">No fares to display</h3>
        <p class="text-muted small mb-0">Try different dates or airports.</p>
    </div>
@else
    <div class="empty-results">
        <i class="fas fa-plane-slash fa-2x text-muted mb-3"></i>
        <h3 class="h6">No fares to display</h3>
        <p class="text-muted small mb-0">Try different dates or airports.</p>
    </div>
@endif
