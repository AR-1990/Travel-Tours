@php
    $searchResult = $searchResult ?? ($flightPriceResult ?? null);
    $solution = is_array($searchResult['solutions'][0] ?? null) ? $searchResult['solutions'][0] : null;
    $fareRules = is_array($searchResult['fare_rules'] ?? null) ? $searchResult['fare_rules'] : [];
    $fareBrand = trim((string) ($solution['fare_brand'] ?? data_get($solution, 'journeys.0.fare_brand', '')));
    $cabin = \App\Support\FlightDisplay::cabinLabel($solution['cabin'] ?? data_get($solution, 'journeys.0.cabin'));
    $fareBasis = trim((string) ($solution['fare_basis'] ?? ''));
    $provider = strtolower((string) ($searchResult['provider'] ?? ($solution['provider'] ?? '')));
    $hasContent = $fareRules !== [] || $fareBrand !== '' || $cabin !== '' || $fareBasis !== '';
@endphp
<div class="card border-0 shadow-sm mb-4 book-section book-section-rules">
    <div class="card-body p-4">
        <h2 class="h5 mb-3">2. Flight rules</h2>

        <div class="row g-3 small mb-3">
            @if($cabin !== '')
                <div class="col-sm-4">
                    <div class="text-muted">Cabin</div>
                    <strong>{{ $cabin }}</strong>
                </div>
            @endif
            @if($fareBrand !== '')
                <div class="col-sm-4">
                    <div class="text-muted">Fare brand</div>
                    <strong>{{ $fareBrand }}</strong>
                </div>
            @endif
            @if($fareBasis !== '')
                <div class="col-sm-4">
                    <div class="text-muted">Fare basis</div>
                    <code>{{ $fareBasis }}</code>
                </div>
            @endif
        </div>

        @if($fareRules !== [])
            <div class="small">
                @foreach($fareRules as $ruleBlock)
                    @if(! is_array($ruleBlock))
                        @continue
                    @endif
                    <div class="mb-2">
                        @if(!empty($ruleBlock['DepartureAirport']) || !empty($ruleBlock['ArrivalAirport']))
                            <strong>{{ $ruleBlock['DepartureAirport'] ?? '' }} → {{ $ruleBlock['ArrivalAirport'] ?? '' }}</strong>
                        @endif
                        @if(!empty($ruleBlock['refundRuleArray']) && is_array($ruleBlock['refundRuleArray']))
                            <ul class="mb-1">
                                @foreach($ruleBlock['refundRuleArray'] as $rr)
                                    @if(! is_array($rr))
                                        @continue
                                    @endif
                                    <li>{{ $rr['title'] ?? '' }}@if(isset($rr['penalty'])): {{ $rr['penalty'] }}@endif</li>
                                @endforeach
                            </ul>
                        @endif
                        @if(!empty($ruleBlock['FareRules']) && is_array($ruleBlock['FareRules']))
                            <ul class="mb-0 text-muted">
                                @foreach($ruleBlock['FareRules'] as $fr)
                                    @if(! is_array($fr))
                                        @continue
                                    @endif
                                    <li>{{ $fr['title'] ?? '' }}@if(!empty($fr['value'])): {{ $fr['value'] }}@endif</li>
                                @endforeach
                            </ul>
                        @endif
                        @if(!empty($ruleBlock['title']) || !empty($ruleBlock['text']))
                            <div class="text-muted">{{ $ruleBlock['title'] ?? '' }} {{ $ruleBlock['text'] ?? '' }}</div>
                        @endif
                    </div>
                @endforeach
            </div>
        @elseif(! $hasContent)
            <p class="small text-muted mb-0">Fare conditions follow the selected offer. Full rules may be available after pricing from the airline.</p>
        @else
            <p class="small text-muted mb-2">Selected fare conditions are applied at ticketing. Refund/change penalties depend on the airline fare rules.</p>
        @endif

        @if($provider === \App\Support\FlightProvider::TRAVELPORT && !empty($searchResult['ok']))
            <a href="{{ route(isset($flightsRoutePrefix) ? $flightsRoutePrefix.'.flights.operation' : 'frontend.flights.operation', ['operation' => 'air_fare_rules']) }}"
               class="btn btn-sm btn-outline-secondary mt-2">
                Show fare rules
            </a>
        @endif
    </div>
</div>
