@php
    $includeAgency = (bool) ($includeAgency ?? true);
    $reservationShowRoute = $reservationShowRoute ?? null;
    $formatMoney = static function (?float $amount): string {
        return $amount === null ? '—' : number_format($amount, 2);
    };
@endphp
<div class="table-responsive">
    <table class="table table-sm table-striped table-hover mb-0 align-middle">
        <thead>
            <tr class="small text-nowrap">
                <th>E-Ticket</th>
                <th>PNR</th>
                <th>Order ID</th>
                <th>Airline</th>
                <th>Status</th>
                @if($includeAgency)
                    <th>Agency</th>
                @endif
                <th class="text-end">Total</th>
                <th class="text-end">Price</th>
                <th class="text-end">Tax</th>
                <th class="text-end">Fuel/Other</th>
                <th class="text-end">Svc fee</th>
                <th class="text-end">Agency comm.</th>
                <th class="text-end">Ticket fee</th>
                <th class="text-end">Profit</th>
                <th>Curr</th>
                <th>Passenger</th>
                <th>Sold</th>
                <th>Route</th>
                <th>Agency user</th>
                <th>Departure</th>
                <th>Arrival</th>
                <th>Provider</th>
                <th>Pay</th>
                <th>Details</th>
            </tr>
        </thead>
        <tbody>
            @forelse($sales as $r)
                <tr class="small text-nowrap">
                    <td>{{ $r->ticketNumbersLabel() }}</td>
                    <td>{{ $r->pnrLabel() }}</td>
                    <td>
                        @if($reservationShowRoute)
                            <a href="{{ route($reservationShowRoute, $r->id) }}">{{ $r->id }}</a>
                        @else
                            {{ $r->id }}
                        @endif
                    </td>
                    <td>{{ $r->airlineSalesLabel() }}</td>
                    <td>{{ $r->status }}</td>
                    @if($includeAgency)
                        <td>{{ $r->salesAgencyLabel() }}</td>
                    @endif
                    <td class="text-end">{{ $formatMoney($r->salesTotalAmount()) }}</td>
                    <td class="text-end">{{ $formatMoney($r->salesBaseAmount()) }}</td>
                    <td class="text-end">{{ $formatMoney($r->salesTaxAmount()) }}</td>
                    <td class="text-end">{{ $formatMoney($r->salesFuelOtherAmount()) }}</td>
                    <td class="text-end">{{ $formatMoney($r->salesServiceFeeAmount()) }}</td>
                    <td class="text-end">{{ $formatMoney($r->salesCommissionAmount()) }}</td>
                    <td class="text-end">{{ $formatMoney($r->salesMarkupAmount()) }}</td>
                    <td class="text-end">{{ $formatMoney($r->salesProfitAmount()) }}</td>
                    <td>{{ $r->salesCurrency() }}</td>
                    <td>{{ $r->passengerName() ?: '—' }}</td>
                    <td>{{ optional($r->booked_at)->format('Y-m-d') }}</td>
                    <td>{{ $r->salesRouteLabel() }}</td>
                    <td>{{ $r->salesAgentLabel() }}</td>
                    <td>{{ optional($r->departure_date)->format('Y-m-d') ?: '—' }}</td>
                    <td>{{ $r->salesArrivalLabel() }}</td>
                    <td>{{ $r->providerLabel() }}</td>
                    <td>{{ $r->form_of_payment ?: '—' }}</td>
                    <td>
                        @if($reservationShowRoute)
                            <a href="{{ route($reservationShowRoute, $r->id) }}" class="btn btn-link btn-sm py-0 px-1">View</a>
                        @else
                            <span class="text-muted">—</span>
                        @endif
                    </td>
                </tr>
            @empty
                <tr>
                    <td colspan="{{ $includeAgency ? 24 : 23 }}" class="text-muted text-center py-4">No sales in this range.</td>
                </tr>
            @endforelse
        </tbody>
        @if(isset($pageTotals) && $sales->count() > 0)
            <tfoot>
                <tr class="small fw-semibold text-nowrap">
                    <td colspan="{{ $includeAgency ? 6 : 5 }}">Page totals</td>
                    <td class="text-end">{{ $formatMoney($pageTotals['total'] ?? null) }}</td>
                    <td class="text-end">{{ $formatMoney($pageTotals['price'] ?? null) }}</td>
                    <td class="text-end">{{ $formatMoney($pageTotals['tax'] ?? null) }}</td>
                    <td class="text-end">{{ $formatMoney($pageTotals['fuel'] ?? null) }}</td>
                    <td class="text-end">{{ $formatMoney($pageTotals['svc'] ?? null) }}</td>
                    <td class="text-end">{{ $formatMoney($pageTotals['comm'] ?? null) }}</td>
                    <td class="text-end">{{ $formatMoney($pageTotals['markup'] ?? null) }}</td>
                    <td class="text-end">{{ $formatMoney($pageTotals['profit'] ?? null) }}</td>
                    <td colspan="10"></td>
                </tr>
            </tfoot>
        @endif
    </table>
</div>
