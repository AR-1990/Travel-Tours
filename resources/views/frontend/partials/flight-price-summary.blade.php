@if(!empty($searchResult['solutions']))
    @php
        $solution = $searchResult['solutions'][0];
        $price = \App\Support\FlightDisplay::parsePrice($solution['total_price'] ?? null);
        $base = \App\Support\FlightDisplay::parsePrice($solution['base_price'] ?? null);
        $taxes = \App\Support\FlightDisplay::parsePrice($solution['taxes'] ?? null);
        $ticketBy = \App\Support\FlightDisplay::parseDateTime($solution['latest_ticketing_time'] ?? null);
    @endphp
    <div class="flight-price-summary card border-0 shadow-sm">
        <div class="card-body p-4">
            <div class="row align-items-start g-3">
                <div class="col-md-8">
                    <h2 class="h5 mb-2">Price confirmed</h2>
                    <p class="text-muted small mb-0">
                        @if(!empty($solution['plating_carrier']))
                            Airline: <strong>{{ \App\Support\FlightDisplay::airlineName($solution['plating_carrier']) }}</strong>
                            <span class="text-muted">({{ $solution['plating_carrier'] }})</span>
                        @endif
                        @if(!empty($solution['fare_basis']))
                            · Fare basis: <code>{{ $solution['fare_basis'] }}</code>
                        @endif
                    </p>
                    @if(!empty($ticketBy['date']) || !empty($ticketBy['time']))
                        <p class="small text-muted mt-2 mb-0">Latest ticketing: {{ trim(($ticketBy['date'] ?? '').' '.($ticketBy['time'] ?? '')) }}</p>
                    @endif
                </div>
                <div class="col-md-4 text-md-end">
                    <div class="small text-muted">Total</div>
                    @if($price)
                        <div class="h3 mb-0 text-primary">{{ $price['currency'] }} {{ $price['amount'] }}</div>
                    @else
                        <div class="h3 mb-0 text-primary">{{ $solution['total_price'] ?? '—' }}</div>
                    @endif
                    @if($base || $taxes)
                        <div class="small text-muted mt-1">
                            @if($base)Base {{ $base['currency'] }} {{ $base['amount'] }}@endif
                            @if($taxes) · Taxes {{ $taxes['currency'] }} {{ $taxes['amount'] }}@endif
                        </div>
                    @endif
                </div>
            </div>
            @if(!empty($searchResult['fare_rules']) && is_array($searchResult['fare_rules']))
                <hr class="my-3">
                <h3 class="h6 mb-2">Fare rules</h3>
                <div class="small">
                    @foreach($searchResult['fare_rules'] as $ruleBlock)
                        @if(!is_array($ruleBlock))
                            @continue
                        @endif
                        <div class="mb-2">
                            <strong>{{ $ruleBlock['DepartureAirport'] ?? '' }} → {{ $ruleBlock['ArrivalAirport'] ?? '' }}</strong>
                            @if(!empty($ruleBlock['refundRuleArray']) && is_array($ruleBlock['refundRuleArray']))
                                <ul class="mb-1">
                                    @foreach($ruleBlock['refundRuleArray'] as $rr)
                                        <li>{{ $rr['title'] ?? '' }}@if(isset($rr['penalty'])): {{ $rr['penalty'] }}@endif</li>
                                    @endforeach
                                </ul>
                            @endif
                            @if(!empty($ruleBlock['FareRules']) && is_array($ruleBlock['FareRules']))
                                <ul class="mb-0 text-muted">
                                    @foreach($ruleBlock['FareRules'] as $fr)
                                        <li>{{ $fr['title'] ?? '' }}@if(!empty($fr['value'])): {{ $fr['value'] }}@endif</li>
                                    @endforeach
                                </ul>
                            @endif
                        </div>
                    @endforeach
                </div>
            @endif
        </div>
    </div>
@elseif(!empty($searchResult) && ($searchResult['ok'] ?? false))
    <div class="alert alert-info">Price call succeeded, but no pricing solution was parsed.</div>
@elseif(!empty($searchResult))
    <div class="alert alert-danger">{{ $searchResult['message'] ?? 'Pricing failed.' }}</div>
@endif
