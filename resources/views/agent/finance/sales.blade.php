@extends('admin.layouts.main')

@section('title', 'Sales report')

@section('content')
<div class="container-fluid panel-page">
    @include('admin.partials.page-header', [
        'title' => 'Sales report',
        'subtitle' => $tenant->name.' flight bookings with e-ticket, fare, tax, fees and route details.',
        'icon' => 'fas fa-chart-line',
    ])
    @include('admin.partials.flash')

    <form method="GET" class="row g-2 mb-3 align-items-end">
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
        · Ticket fee {{ number_format((float) ($totals->markup_sum ?? 0), 2) }}
        · Profit {{ number_format((float) ($totals->profit_sum ?? 0), 2) }}
        · Svc fee {{ number_format((float) ($totals->svc_sum ?? 0), 2) }}
        · Comm. {{ number_format((float) ($totals->comm_sum ?? 0), 2) }}
    </div>

    <div class="panel-surface panel-surface--flush">
        @include('flights.partials.sales-report-table', [
            'sales' => $sales,
            'includeAgency' => false,
            'reservationShowRoute' => null,
            'pageTotals' => $pageTotals ?? null,
        ])
        <div class="p-3">{{ $sales->links() }}</div>
    </div>
</div>
@endsection
