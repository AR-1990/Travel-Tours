@php
    $booking = $flightBooking ?? [];
    $priceResult = $flightPriceResult ?? null;
    $solution = $priceResult['solutions'][0] ?? null;
    $search = $searchInput ?? ($flightSearchInput ?? []);
    $ticket = $flightTicket ?? null;
    $isTicketed = ! empty($ticket['ticket_numbers']);
    $pax = $booking['input']['passengers'][0] ?? null;
    if (! is_array($pax)) {
        $pax = [
            'prefix' => $booking['input']['passenger_prefix'] ?? null,
            'first' => $booking['input']['passenger_first'] ?? null,
            'last' => $booking['input']['passenger_last'] ?? null,
            'email' => $booking['input']['passenger_email'] ?? null,
            'phone' => $booking['input']['passenger_phone'] ?? null,
            'dob' => $booking['input']['passenger_dob'] ?? null,
            'gender' => $booking['input']['passenger_gender'] ?? null,
        ];
    }
    $statusLabel = $isTicketed ? 'Ticketed' : 'Reserved';
    $statusClass = $isTicketed ? 'bg-success' : 'bg-warning text-dark';
    $carrierCode = $solution['plating_carrier'] ?? ($solution['segments'][0]['carrier'] ?? null);
    $price = \App\Support\FlightDisplay::parsePrice($solution['total_price'] ?? null);
    $base = \App\Support\FlightDisplay::parsePrice($solution['base_price'] ?? null);
    $taxes = \App\Support\FlightDisplay::parsePrice($solution['taxes'] ?? null);
    $journeys = is_array($solution) ? \App\Support\FlightDisplay::solutionJourneys($solution) : [];
    $ticketRoute = $ticketActionRoute ?? null;
@endphp

<div class="reservation-detail">
    <div class="card border-0 shadow-sm mb-4">
        <div class="card-body {{ !empty($compact) ? '' : 'p-4' }}">
            <div class="d-flex flex-wrap justify-content-between align-items-start gap-3 mb-3">
                <div>
                    <div class="text-muted small mb-1">Reservation file</div>
                    <h2 class="h5 mb-0">{{ $isTicketed ? 'Booking complete' : 'Booking reserved' }}</h2>
                </div>
                <span class="badge {{ $statusClass }} px-3 py-2">{{ $statusLabel }}</span>
            </div>

            <div class="row g-3">
                <div class="col-md-4">
                    <div class="small text-muted">Universal Record</div>
                    <div class="fw-semibold"><code>{{ $booking['universal_locator'] ?? '—' }}</code></div>
                </div>
                <div class="col-md-4">
                    <div class="small text-muted">Air reservation</div>
                    <div class="fw-semibold"><code>{{ $booking['air_reservation_locator'] ?? '—' }}</code></div>
                </div>
                <div class="col-md-4">
                    <div class="small text-muted">Booked at</div>
                    <div class="fw-semibold">
                        @php $bookedAt = \App\Support\FlightDisplay::parseDateTime($booking['booked_at'] ?? null); @endphp
                        {{ trim(($bookedAt['date'] ?? '—').' '.($bookedAt['time'] ?? '')) }}
                    </div>
                </div>
            </div>

            @if(!empty($search))
                <p class="text-muted small mt-3 mb-0">
                    {{ \App\Support\FlightDisplay::tripSummary(
                        $search['origin'] ?? null,
                        $search['destination'] ?? null,
                        $search['departure_date'] ?? null,
                        $search['return_date'] ?? null,
                        (int) ($search['adults'] ?? 1)
                    ) }}
                </p>
            @endif
        </div>
    </div>

    @if($pax && (!empty($pax['first']) || !empty($pax['last'])))
        <div class="card border-0 shadow-sm mb-4">
            <div class="card-body {{ !empty($compact) ? '' : 'p-4' }}">
                <h3 class="h6 mb-3">Passenger</h3>
                <div class="row g-3">
                    <div class="col-md-6">
                        <div class="small text-muted">Name</div>
                        <div class="fw-semibold">
                            {{ trim(($pax['prefix'] ?? '').' '.($pax['first'] ?? '').' '.($pax['last'] ?? '')) }}
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="small text-muted">Gender</div>
                        <div>{{ ($pax['gender'] ?? '') === 'F' ? 'Female' : (($pax['gender'] ?? '') === 'M' ? 'Male' : '—') }}</div>
                    </div>
                    <div class="col-md-3">
                        <div class="small text-muted">Date of birth</div>
                        <div>{{ $pax['dob'] ?? '—' }}</div>
                    </div>
                    <div class="col-md-6">
                        <div class="small text-muted">Email</div>
                        <div>{{ $pax['email'] ?? '—' }}</div>
                    </div>
                    <div class="col-md-6">
                        <div class="small text-muted">Phone</div>
                        <div>{{ $pax['phone'] ?? '—' }}</div>
                    </div>
                </div>
            </div>
        </div>
    @endif

    @if($journeys !== [])
        <div class="card border-0 shadow-sm mb-4">
            <div class="card-body {{ !empty($compact) ? '' : 'p-4' }}">
                <div class="d-flex flex-wrap justify-content-between align-items-center gap-2 mb-3">
                    <h3 class="h6 mb-0">Flight itinerary</h3>
                    @if($carrierCode)
                        <span class="small text-muted">
                            Airline: <strong>{{ \App\Support\FlightDisplay::airlineName($carrierCode) }}</strong>
                            <span class="text-muted">({{ $carrierCode }})</span>
                        </span>
                    @endif
                </div>

                @foreach($journeys as $journey)
                    <div class="{{ !$loop->last ? 'mb-4 pb-3 border-bottom' : '' }}">
                        <div class="small text-muted fw-semibold mb-2">{{ $journey['label'] }}</div>
                        @foreach($journey['segments'] as $seg)
                            @php
                                $dep = \App\Support\FlightDisplay::parseDateTime($seg['departure'] ?? null);
                                $arr = \App\Support\FlightDisplay::parseDateTime($seg['arrival'] ?? null);
                            @endphp
                            <div class="row g-3 align-items-center mb-3">
                                <div class="col-md-3">
                                    <div class="fw-semibold">{{ \App\Support\FlightDisplay::flightLabel($seg['carrier'] ?? null, $seg['flight_number'] ?? null) }}</div>
                                    <div class="small text-muted">{{ $seg['class_of_service'] ?? $solution['fare_basis'] ?? '' }}</div>
                                </div>
                                <div class="col-md-4">
                                    <div class="fw-semibold">{{ $dep['time'] ?? '—' }} · {{ $dep['date'] ?? '' }}</div>
                                    <div class="small">{{ \App\Support\FlightDisplay::airportCity($seg['origin'] ?? null) }}</div>
                                </div>
                                <div class="col-md-1 text-center text-muted"><i class="fas fa-plane"></i></div>
                                <div class="col-md-4">
                                    <div class="fw-semibold">{{ $arr['time'] ?? '—' }} · {{ $arr['date'] ?? '' }}</div>
                                    <div class="small">{{ \App\Support\FlightDisplay::airportCity($seg['destination'] ?? null) }}</div>
                                </div>
                            </div>
                        @endforeach
                    </div>
                @endforeach
            </div>
        </div>
    @endif

    @if(is_array($solution))
        <div class="card border-0 shadow-sm mb-4">
            <div class="card-body {{ !empty($compact) ? '' : 'p-4' }}">
                <h3 class="h6 mb-3">Fare summary</h3>
                <div class="row g-3 align-items-end">
                    <div class="col-md-8">
                        <div class="small text-muted mb-1">
                            @if($carrierCode)
                                {{ \App\Support\FlightDisplay::airlineName($carrierCode) }}
                            @endif
                            @if(!empty($solution['fare_basis']))
                                · Fare basis <code>{{ $solution['fare_basis'] }}</code>
                            @endif
                        </div>
                        @if($base || $taxes)
                            <div class="small text-muted">
                                @if($base)Base {{ $base['currency'] }} {{ $base['amount'] }}@endif
                                @if($taxes) · Taxes {{ $taxes['currency'] }} {{ $taxes['amount'] }}@endif
                            </div>
                        @endif
                    </div>
                    <div class="col-md-4 text-md-end">
                        <div class="small text-muted">Total</div>
                        @if($price)
                            <div class="h4 mb-0 text-primary">{{ $price['currency'] }} {{ $price['amount'] }}</div>
                        @else
                            <div class="h4 mb-0 text-primary">{{ $solution['total_price'] ?? '—' }}</div>
                        @endif
                    </div>
                </div>
            </div>
        </div>
    @endif

    @if($isTicketed)
        <div class="card border-0 shadow-sm mb-4 border-success">
            <div class="card-body {{ !empty($compact) ? '' : 'p-4' }}">
                <h3 class="h6 mb-3 text-success"><i class="fas fa-ticket-alt me-2"></i>E-tickets</h3>
                <ul class="mb-0">
                    @foreach($ticket['ticket_numbers'] as $number)
                        <li><code>{{ $number }}</code></li>
                    @endforeach
                </ul>
            </div>
        </div>
    @elseif(($canBookFlights ?? false) && $ticketRoute)
        <div class="card border-0 shadow-sm mb-4">
            <div class="card-body {{ !empty($compact) ? '' : 'p-4' }}">
                <h3 class="h6 mb-2">Issue e-ticket</h3>
                <p class="text-muted small mb-3">
                    Your reservation is saved. Ticketing requires an IATA-enabled PCC.
                    On the current test account it may fail until Travelport enables ticketing.
                </p>
                <form method="POST" action="{{ $ticketRoute }}">
                    @csrf
                    <button type="submit" class="{{ $ticketButtonClass ?? 'btn btn-primary btn-sm' }}" @disabled(!($travelportReady ?? false))>
                        <i class="fas fa-receipt me-1"></i> Issue ticket
                    </button>
                </form>
            </div>
        </div>
    @endif
</div>
