@if(!empty($searchResult['solutions']))
    @php
        $shown = count($searchResult['solutions']);
        $total = (int) ($searchResult['total_found'] ?? $shown);
    @endphp
    <div class="results-header">
        <div>
            <h2 class="h5 mb-1">{{ $shown }} fare option{{ $shown !== 1 ? 's' : '' }} shown@if($total > $shown) <span class="text-muted fw-normal">of {{ $total }} returned</span>@endif</h2>
            @if(!empty($searchInput))
                <p class="text-muted small mb-0">
                    {{ \App\Support\FlightDisplay::tripSummary(
                        $searchInput['origin'] ?? null,
                        $searchInput['destination'] ?? null,
                        $searchInput['departure_date'] ?? null,
                        $searchInput['return_date'] ?? null,
                        (int) ($searchInput['adults'] ?? 1)
                    ) }}
                </p>
            @endif
        </div>
        <a href="{{ route($flightsRoutePrefix . '.flights.search') }}" class="btn btn-outline-secondary btn-sm">
            <i class="fas fa-edit me-1"></i> Modify search
        </a>
    </div>

    @foreach($searchResult['solutions'] as $sol)
        @php
            $price = \App\Support\FlightDisplay::parsePrice($sol['total_price'] ?? null);
            $journeys = $sol['journeys'] ?? [];
            if ($journeys === [] && !empty($sol['segments'])) {
                $journeys = [['travel_time' => null, 'segments' => $sol['segments']]];
            }
            $carrier = $sol['plating_carrier'] ?? ($sol['segments'][0]['carrier'] ?? '—');
        @endphp
        <article class="flight-result-card">
            <div class="row align-items-start g-3">
                <div class="col-auto">
                    <div class="carrier-badge" title="Plating carrier">{{ $carrier }}</div>
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
                            @endphp
                            <div class="d-flex flex-wrap align-items-center gap-3 mb-2 pb-2 {{ !$loop->last ? 'border-bottom border-light' : '' }}">
                                <div>
                                    <div class="flight-time">{{ $dep['time'] ?? '—' }}</div>
                                    <div class="flight-airport-code">{{ \App\Support\FlightDisplay::airportShort($seg['origin'] ?? null) }}</div>
                                    <div class="small text-muted">{{ $dep['date'] ?? '' }}</div>
                                </div>
                                <div class="text-muted small text-center px-2">
                                    <i class="fas fa-plane text-primary"></i>
                                    <div>{{ $seg['carrier'] }}{{ $seg['flight_number'] }}</div>
                                </div>
                                <div>
                                    <div class="flight-time">{{ $arr['time'] ?? '—' }}</div>
                                    <div class="flight-airport-code">{{ \App\Support\FlightDisplay::airportShort($seg['destination'] ?? null) }}</div>
                                    <div class="small text-muted">{{ $arr['date'] ?? '' }}</div>
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
                        @if($sol['base_price'])
                            <div class="small text-muted">Base {{ $sol['base_price'] }}</div>
                        @endif
                        <form method="POST" action="{{ route($flightsRoutePrefix . '.flights.operation', ['operation' => 'air_price']) }}" class="mt-2">
                            @csrf
                            <input type="hidden" name="solution_key" value="{{ $sol['key'] ?? '' }}">
                            <button type="submit" class="btn btn-primary btn-sm" @disabled(!$travelportReady || empty($sol['key']))>
                                Price &amp; hold
                            </button>
                        </form>
                    </div>
                </div>
            </div>
        </article>
    @endforeach
@else
    <div class="empty-results">
        <i class="fas fa-plane-slash fa-2x text-muted mb-3"></i>
        <h3 class="h6">No fares to display</h3>
        <p class="text-muted small mb-0">Try different dates or airports.</p>
    </div>
@endif
