@if(!empty($searchResult['solutions']))
    @php
        $solution = $searchResult['solutions'][0];
        $price = \App\Support\FlightDisplay::parsePrice($solution['total_price'] ?? null);
        $base = \App\Support\FlightDisplay::parsePrice($solution['base_price'] ?? null);
        $taxes = \App\Support\FlightDisplay::parsePrice($solution['taxes'] ?? null);
        $ticketBy = \App\Support\FlightDisplay::parseDateTime($solution['latest_ticketing_time'] ?? null);
    @endphp
    <div class="card-modern p-4">
        <div class="d-flex flex-wrap justify-content-between align-items-start gap-3">
            <div>
                <h2 class="h6 mb-2">Price confirmed</h2>
                <div class="small text-muted">
                    @if(!empty($solution['plating_carrier']))Plating carrier: <strong>{{ $solution['plating_carrier'] }}</strong>@endif
                    @if(!empty($solution['fare_basis'])) · Fare basis: <code>{{ $solution['fare_basis'] }}</code>@endif
                </div>
                @if(!empty($ticketBy['date']) || !empty($ticketBy['time']))
                    <p class="small text-muted mt-2 mb-0">Latest ticketing: {{ trim(($ticketBy['date'] ?? '').' '.($ticketBy['time'] ?? '')) }}</p>
                @endif
            </div>
            <div class="text-end">
                <div class="small text-muted">Total</div>
                @if($price)
                    <div class="h4 mb-0">{{ $price['currency'] }} {{ $price['amount'] }}</div>
                @else
                    <div class="h4 mb-0">{{ $solution['total_price'] ?? '—' }}</div>
                @endif
                @if($base || $taxes)
                    <div class="small text-muted mt-1">
                        @if($base)Base {{ $base['currency'] }} {{ $base['amount'] }}@endif
                        @if($taxes) · Taxes {{ $taxes['currency'] }} {{ $taxes['amount'] }}@endif
                    </div>
                @endif
            </div>
        </div>
    </div>
@elseif(!empty($searchResult) && ($searchResult['ok'] ?? false))
    <div class="card-modern p-4">
        <p class="small text-muted mb-0">Price call succeeded, but no pricing solution was parsed. Expand raw output below.</p>
    </div>
@endif
