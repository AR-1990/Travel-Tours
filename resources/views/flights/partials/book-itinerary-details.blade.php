@php
    $searchResult = $searchResult ?? ($flightPriceResult ?? null);
    $solution = is_array($searchResult['solutions'][0] ?? null) ? $searchResult['solutions'][0] : null;
    $journeys = is_array($solution) ? \App\Support\FlightDisplay::solutionJourneys($solution) : [];
    $price = is_array($solution) ? \App\Support\FlightDisplay::parsePrice($solution['total_price'] ?? null) : null;
    $carrier = is_array($solution) ? ($solution['plating_carrier'] ?? null) : null;
@endphp
@if(is_array($solution) && $journeys !== [])
<div class="card border-0 shadow-sm mb-4 book-section book-section-details">
    <div class="card-body p-4">
        <div class="d-flex flex-wrap justify-content-between align-items-start gap-2 mb-3">
            <div>
                <h2 class="h5 mb-1">1. Flight details</h2>
                <p class="small text-muted mb-0">
                    @if($carrier)
                        {{ \App\Support\FlightDisplay::airlineName($carrier) }}
                        <span class="text-muted">({{ $carrier }})</span>
                    @endif
                    @if(!empty($solution['fare_basis']))
                        · Fare basis <code>{{ $solution['fare_basis'] }}</code>
                    @endif
                </p>
            </div>
            <div class="text-md-end">
                <div class="small text-muted">Total</div>
                @if($price)
                    <div class="h5 mb-0 text-primary">{{ $price['currency'] }} {{ $price['amount'] }}</div>
                @else
                    <div class="h5 mb-0 text-primary">{{ $solution['total_price'] ?? '—' }}</div>
                @endif
            </div>
        </div>

        @foreach($journeys as $journey)
            <div class="mb-3">
                @if(count($journeys) > 1)
                    <div class="small fw-semibold text-uppercase text-muted mb-2">{{ $journey['label'] }}</div>
                @endif
                @foreach(($journey['segments'] ?? []) as $seg)
                    @php
                        if (! is_array($seg)) {
                            continue;
                        }
                        $dep = \App\Support\FlightDisplay::parseDateTime($seg['departure'] ?? null);
                        $arr = \App\Support\FlightDisplay::parseDateTime($seg['arrival'] ?? null);
                        $cabin = \App\Support\FlightDisplay::cabinLabel($seg['cabin'] ?? ($journey['cabin'] ?? ($solution['cabin'] ?? null)));
                        $bookingClass = \App\Support\FlightDisplay::bookingClass($seg);
                        $flightNo = trim(($seg['carrier'] ?? '').($seg['flight_number'] ?? ''));
                        $origin = $seg['origin_city'] ?? \App\Support\FlightDisplay::airportCity($seg['origin'] ?? null);
                        $dest = $seg['destination_city'] ?? \App\Support\FlightDisplay::airportCity($seg['destination'] ?? null);
                    @endphp
                    <div class="border rounded px-3 py-2 mb-2">
                        <div class="d-flex flex-wrap justify-content-between gap-2">
                            <div>
                                <strong>{{ $flightNo !== '' ? $flightNo : 'Flight' }}</strong>
                                <span class="text-muted">· {{ $origin }} → {{ $dest }}</span>
                            </div>
                            <div class="small text-nowrap">
                                @if($cabin !== '')
                                    <span class="me-2">Cabin: <strong>{{ $cabin }}</strong></span>
                                @endif
                                @if($bookingClass !== '')
                                    <span>Booking class: <strong>{{ $bookingClass }}</strong></span>
                                @endif
                            </div>
                        </div>
                        <div class="small text-muted mt-1">
                            @if(!empty($dep['date']))
                                {{ $dep['date'] }}@if(!empty($dep['weekday'])), {{ $dep['weekday'] }}@endif
                                {{ $dep['time'] ?? '' }}
                            @endif
                            →
                            @if(!empty($arr['date']))
                                @if(($arr['date'] ?? '') !== ($dep['date'] ?? ''))
                                    {{ $arr['date'] }}
                                @endif
                                {{ $arr['time'] ?? '' }}
                            @endif
                        </div>
                    </div>
                @endforeach
                @php
                    $travelTime = \App\Support\FlightDisplay::formatDuration($journey['travel_time'] ?? null);
                @endphp
                @if($travelTime !== '')
                    <div class="small text-muted">Duration: {{ $travelTime }}</div>
                @endif
            </div>
        @endforeach
    </div>
</div>
@endif
