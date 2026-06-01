@if(!empty($searchResult['solutions']))
    @php
        $shown = count($searchResult['solutions']);
        $total = (int) ($searchResult['total_found'] ?? $shown);
    @endphp
    <div class="results-header mb-3">
        <div>
            <h2 class="h5 mb-1">{{ $shown }} fare{{ $shown !== 1 ? 's' : '' }} shown@if($total > $shown) <span class="text-muted fw-normal">of {{ $total }} returned</span>@endif</h2>
            @if(!empty($searchInput))
                <p class="text-muted small mb-0">
                    {{ strtoupper($searchInput['origin'] ?? '') }} → {{ strtoupper($searchInput['destination'] ?? '') }}
                    @if(!empty($searchInput['departure_date']))
                        · depart {{ $searchInput['departure_date'] }}
                    @endif
                </p>
            @endif
        </div>
    </div>

    <div class="table-responsive card-modern">
        <table class="table table-sm table-hover mb-0 align-middle">
            <thead class="table-light">
                <tr>
                    <th>Carrier</th>
                    <th>Fare basis</th>
                    <th>Trip</th>
                    <th class="text-end">Amount</th>
                </tr>
            </thead>
            <tbody>
                @foreach($searchResult['solutions'] as $fare)
                    @php $price = \App\Support\FlightDisplay::parsePrice($fare['total_price'] ?? null); @endphp
                    <tr>
                        <td><span class="carrier-badge d-inline-flex">{{ $fare['plating_carrier'] ?? '—' }}</span></td>
                        <td><code class="small">{{ $fare['fare_basis'] ?? '—' }}</code></td>
                        <td class="small text-muted">{{ $fare['trip_type'] ?? '—' }}</td>
                        <td class="text-end fw-semibold">
                            @if($price)
                                {{ $price['currency'] }} {{ $price['amount'] }}
                            @else
                                {{ $fare['total_price'] ?? '—' }}
                            @endif
                        </td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    </div>
    <p class="small text-muted mt-2 mb-0">Tariff fares for the market — not tied to live seat availability. Use <strong>Fare Rules</strong> with a fare basis for restrictions.</p>
@endif
