@if(!empty($searchResult['solutions']))
    <div class="results-header">
        <div>
            <h2 class="h5 mb-1">{{ count($searchResult['solutions']) }} fare option{{ count($searchResult['solutions']) !== 1 ? 's' : '' }} found</h2>
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
            $firstSeg = $sol['segments'][0] ?? null;
            $lastSeg = $sol['segments'][count($sol['segments']) - 1] ?? $firstSeg;
            $carrier = $firstSeg['carrier'] ?? '—';
            $dep = \App\Support\FlightDisplay::parseDateTime($firstSeg['departure'] ?? null);
            $arr = \App\Support\FlightDisplay::parseDateTime($lastSeg['arrival'] ?? null);
        @endphp
        <article class="flight-result-card">
            <div class="row align-items-center g-3">
                <div class="col-auto">
                    <div class="carrier-badge" title="Airline {{ $carrier }}">{{ $carrier }}</div>
                </div>
                <div class="col">
                    <div class="flight-timeline">
                        <div>
                            <div class="flight-time">{{ $dep['time'] ?? '—' }}</div>
                            <div class="flight-airport-code">{{ \App\Support\FlightDisplay::airportShort($firstSeg['origin'] ?? null) }}</div>
                            @if($dep)<div class="small text-muted">{{ $dep['date'] ?? '' }}</div>@endif
                        </div>
                        <div class="flight-path">
                            <i class="fas fa-plane"></i>
                            <div class="small text-muted mt-1">
                                @foreach($sol['segments'] as $seg)
                                    <span class="me-2">{{ $seg['carrier'] }}{{ $seg['flight_number'] }}</span>
                                @endforeach
                            </div>
                        </div>
                        <div>
                            <div class="flight-time">{{ $arr['time'] ?? '—' }}</div>
                            <div class="flight-airport-code">{{ \App\Support\FlightDisplay::airportShort($lastSeg['destination'] ?? null) }}</div>
                            @if($arr)<div class="small text-muted">{{ $arr['date'] ?? '' }}</div>@endif
                        </div>
                    </div>
                    @if(count($sol['segments']) > 1)
                        <p class="small text-muted mb-0 mt-2">
                            <i class="fas fa-info-circle me-1"></i>{{ count($sol['segments']) }} segment{{ count($sol['segments']) > 1 ? 's' : '' }}
                            @if($sol['base_price']) · Base {{ $sol['base_price'] }}@endif
                        </p>
                    @endif
                </div>
                <div class="col-auto">
                    <div class="price-block">
                        @if($price)
                            <div class="price-currency">{{ $price['currency'] }}</div>
                            <div class="price-amount">{{ $price['amount'] }}</div>
                        @else
                            <div class="price-amount">—</div>
                        @endif
                        <button type="button" class="btn btn-primary btn-sm mt-2" disabled title="Booking flow coming next">
                            Select fare
                        </button>
                        <div class="small text-muted">Book soon</div>
                    </div>
                </div>
            </div>
        </article>
    @endforeach
@else
    <div class="empty-results">
        <i class="fas fa-plane-slash fa-2x text-muted mb-3"></i>
        <h3 class="h6">No fares to display</h3>
        <p class="text-muted small mb-0">Try different dates or airports. Technical details are below if you need them.</p>
    </div>
@endif
