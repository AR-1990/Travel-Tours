@extends('admin.layouts.main')

@section('title', 'Sales report')

@section('content')
<div class="container-fluid panel-page">
    @include('admin.partials.page-header', [
        'title' => 'Sales report',
        'subtitle' => 'Flight bookings with markup and payment.',
        'icon' => 'fas fa-chart-line',
    ])
    @include('admin.partials.flash')

    <form method="GET" class="row g-2 mb-3 align-items-end">
        <div class="col-md-3">
            <label class="form-label small">Agency</label>
            <select name="tenant_id" class="form-select form-select-sm">
                <option value="">All</option>
                @foreach($tenants as $t)
                    <option value="{{ $t->id }}" @selected($tenantId == $t->id)>{{ $t->name }}</option>
                @endforeach
            </select>
        </div>
        <div class="col-md-2">
            <label class="form-label small">From</label>
            <input type="date" name="from" value="{{ $from }}" class="form-control form-control-sm">
        </div>
        <div class="col-md-2">
            <label class="form-label small">To</label>
            <input type="date" name="to" value="{{ $to }}" class="form-control form-control-sm">
        </div>
        <div class="col-md-2">
            <label class="form-label small">Status</label>
            <select name="status" class="form-select form-select-sm">
                <option value="">All</option>
                @foreach(['reserved','ticketed','cancelled'] as $st)
                    <option value="{{ $st }}" @selected($status === $st)>{{ $st }}</option>
                @endforeach
            </select>
        </div>
        <div class="col-md-1">
            <label class="form-label small">Airline</label>
            <input type="text" name="airline" value="{{ $airline }}" class="form-control form-control-sm" maxlength="3">
        </div>
        <div class="col-md-2 d-flex gap-2">
            <button class="btn btn-sm btn-warning">Show</button>
            <button class="btn btn-sm btn-outline-secondary" name="export" value="csv">CSV</button>
        </div>
    </form>

    <div class="small text-muted mb-2">
        Rows: {{ (int) ($totals->cnt ?? 0) }}
        · Sell {{ number_format((float) ($totals->sell_sum ?? 0), 2) }}
        · Markup {{ number_format((float) ($totals->markup_sum ?? 0), 2) }}
        · Profit {{ number_format((float) ($totals->profit_sum ?? 0), 2) }}
    </div>

    <div class="panel-surface panel-surface--flush">
        <div class="table-responsive">
            <table class="table table-sm table-striped mb-0">
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>Issue/Book</th>
                        <th>Status</th>
                        <th>Agency</th>
                        <th>PNR</th>
                        <th>Route</th>
                        <th>Airline</th>
                        <th>Provider</th>
                        <th class="text-end">Sell</th>
                        <th class="text-end">Markup</th>
                        <th>Pay</th>
                        <th>PAX</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($sales as $r)
                        <tr>
                            <td><a href="{{ route('admin.flights.reservations.show', $r->id) }}">{{ $r->id }}</a></td>
                            <td class="small">{{ optional($r->booked_at)->format('Y-m-d') }}</td>
                            <td>{{ $r->status }}</td>
                            <td>{{ $r->tenant?->name }}</td>
                            <td>{{ $r->air_reservation_locator ?: $r->provider_locator }}</td>
                            <td>{{ $r->origin }}-{{ $r->destination }}</td>
                            <td>{{ $r->carrier }}</td>
                            <td>{{ $r->provider() }}</td>
                            <td class="text-end">{{ $r->sell_total !== null ? number_format((float)$r->sell_total,2) : '—' }}</td>
                            <td class="text-end">{{ $r->markup_amount !== null ? number_format((float)$r->markup_amount,2) : '—' }}</td>
                            <td>{{ $r->form_of_payment }}</td>
                            <td class="small">{{ $r->passenger_first }} {{ $r->passenger_last }}</td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>
    <div class="mt-3">{{ $sales->links() }}</div>
</div>
@endsection
