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
            <button class="btn btn-sm btn-primary">Show</button>
            <button class="btn btn-sm btn-outline-secondary" name="export" value="csv">Excel/CSV</button>
        </div>
    </form>

    @if($summary->isNotEmpty())
        <div class="mb-3 small">
            @foreach($summary as $row)
                <span class="badge bg-light text-dark me-2">{{ $row->type }}: debit {{ number_format((float)$row->debit_total,2) }} / credit {{ number_format((float)$row->credit_total,2) }}</span>
            @endforeach
        </div>
    @endif

    <div class="panel-surface panel-surface--flush">
        <div class="table-responsive">
            <table class="table table-sm table-striped mb-0">
                <thead>
                    <tr>
                        <th>created_at</th>
                        <th>customer</th>
                        <th>type</th>
                        <th class="text-end">debit</th>
                        <th class="text-end">credit</th>
                        <th class="text-end">balance</th>
                        <th>PNR</th>
                        <th>order</th>
                        <th>vendor</th>
                        <th>description</th>
                        <th>authority</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($transactions as $tx)
                        <tr>
                            <td class="small">{{ optional($tx->created_at)->format('Y-m-d H:i') }}</td>
                            <td>{{ $tx->tenant?->name }}</td>
                            <td><span class="badge text-bg-primary">{{ $tx->type === 'sale' ? 'T. Sale' : ucfirst($tx->type) }}</span></td>
                            <td class="text-end">{{ $tx->direction === 'debit' ? number_format((float)$tx->amount,2) : '0' }}</td>
                            <td class="text-end">{{ $tx->direction === 'credit' ? number_format((float)$tx->amount,2) : '0' }}</td>
                            <td class="text-end">{{ number_format((float)$tx->balance_after,2) }}</td>
                            <td>{{ $tx->pnr }}</td>
                            <td>{{ $tx->order_ref }}</td>
                            <td>{{ $tx->vendor }}</td>
                            <td class="small">{{ $tx->description }}</td>
                            <td class="small">{{ $tx->creator?->email }}</td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>
    <div class="mt-3">{{ $transactions->links() }}</div>
</div>
@endsection
