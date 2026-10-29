@extends('admin.layouts.main')

@section('title', 'Account statement')

@section('content')
<div class="container-fluid panel-page">
    @include('admin.partials.page-header', [
        'title' => 'Account statement',
        'subtitle' => $tenant->name.' wallet ledger',
        'icon' => 'fas fa-file-invoice-dollar',
        'actions' => '<a href="'.e(route('agent.finance.wallet')).'" class="btn btn-outline-secondary btn-sm">Wallet</a>',
    ])
    @include('admin.partials.flash')

    <form method="GET" class="row g-2 mb-3 align-items-end">
        <div class="col-md-3">
            <label class="form-label small">From</label>
            <input type="date" name="from" value="{{ $from }}" class="form-control form-control-sm">
        </div>
        <div class="col-md-3">
            <label class="form-label small">To</label>
            <input type="date" name="to" value="{{ $to }}" class="form-control form-control-sm">
        </div>
        <div class="col-md-3 d-flex gap-2">
            <button class="btn btn-sm btn-warning">Show</button>
            <button class="btn btn-sm btn-outline-secondary" name="export" value="csv">CSV</button>
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
            'includeCustomer' => false,
            'reservationShowRoute' => 'agent.flights.reservations.show',
        ])
        <div class="p-3">{{ $transactions->links() }}</div>
    </div>
</div>
@endsection
