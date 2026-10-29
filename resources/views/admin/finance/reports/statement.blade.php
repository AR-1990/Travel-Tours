@extends('admin.layouts.main')

@section('title', 'Account statement')

@section('content')
<div class="container-fluid panel-page">
    @include('admin.partials.page-header', [
        'title' => 'Finance report / Account statement',
        'subtitle' => 'Search by agency and date range. Export CSV.',
        'icon' => 'fas fa-file-invoice-dollar',
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
        <div class="col-md-3 d-flex gap-2">
            <button class="btn btn-sm btn-warning">Show</button>
            <button class="btn btn-sm btn-outline-secondary" name="export" value="csv">Excel/CSV</button>
        </div>
    </form>

    @if($summary->isNotEmpty())
        <div class="table-responsive mb-3" style="max-width: 28rem;">
            <table class="table table-sm table-bordered mb-0">
                <thead>
                    <tr class="small">
                        <th>Tr. type</th>
                        <th class="text-end">Credit</th>
                        <th class="text-end">Debit</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($summary as $row)
                        <tr class="small">
                            <td>{{ $row->type === 'sale' ? 'T. Sale' : ($row->type === 'topup' ? 'Payment' : ucfirst($row->type)) }}</td>
                            <td class="text-end">{{ number_format((float) $row->credit_total, 2) }}</td>
                            <td class="text-end">{{ number_format((float) $row->debit_total, 2) }}</td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    @endif

    <div class="panel-surface panel-surface--flush">
        @include('finance.partials.account-statement-table', [
            'transactions' => $transactions,
            'includeCustomer' => true,
        ])
    </div>
    <div class="mt-3">{{ $transactions->links() }}</div>
</div>
@endsection
