@extends('admin.layouts.main')

@section('title', 'Wallet · '.$tenant->name)

@section('content')
<div class="container-fluid panel-page">
    @include('admin.partials.page-header', [
        'title' => $tenant->name.' wallet',
        'subtitle' => 'Balance '.$wallet->currency.' '.number_format((float) $wallet->balance, 2),
        'icon' => 'fas fa-wallet',
        'actions' => '<a href="'.e(route('admin.finance.wallets.index')).'" class="btn btn-outline-secondary btn-sm">All wallets</a>',
    ])
    @include('admin.partials.flash')

    <div class="row g-3 mb-4">
        <div class="col-md-4">
            <div class="card border-0 shadow-sm h-100">
                <div class="card-body">
                    <h2 class="h6">Top-up</h2>
                    <form method="POST" action="{{ route('admin.finance.wallets.topup', $tenant->id) }}">
                        @csrf
                        <input type="number" step="0.01" min="0.01" name="amount" class="form-control form-control-sm mb-2" placeholder="Amount" required>
                        <input type="text" name="description" class="form-control form-control-sm mb-2" placeholder="Description (optional)">
                        <button class="btn btn-sm btn-success">Credit wallet</button>
                    </form>
                </div>
            </div>
        </div>
        <div class="col-md-4">
            <div class="card border-0 shadow-sm h-100">
                <div class="card-body">
                    <h2 class="h6">Refund</h2>
                    <form method="POST" action="{{ route('admin.finance.wallets.refund', $tenant->id) }}">
                        @csrf
                        <input type="number" step="0.01" min="0.01" name="amount" class="form-control form-control-sm mb-2" placeholder="Amount" required>
                        <input type="text" name="pnr" class="form-control form-control-sm mb-2" placeholder="PNR (optional)">
                        <input type="text" name="description" class="form-control form-control-sm mb-2" placeholder="Description (optional)">
                        <button class="btn btn-sm btn-primary">Credit refund</button>
                    </form>
                </div>
            </div>
        </div>
        <div class="col-md-4">
            <div class="card border-0 shadow-sm h-100">
                <div class="card-body">
                    <div class="text-muted small">Current balance</div>
                    <div class="display-6">{{ number_format((float) $wallet->balance, 2) }}</div>
                    <div class="text-muted">{{ $wallet->currency }}</div>
                </div>
            </div>
        </div>
    </div>

    <div class="panel-surface panel-surface--flush">
        <div class="table-responsive">
            <table class="table table-sm table-striped mb-0">
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>Date</th>
                        <th>Type</th>
                        <th>Dir</th>
                        <th class="text-end">Amount</th>
                        <th class="text-end">Balance</th>
                        <th>PNR</th>
                        <th>Description</th>
                        <th>By</th>
                        <th></th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($transactions as $tx)
                        <tr>
                            <td>{{ $tx->id }}</td>
                            <td class="small">{{ optional($tx->created_at)->format('Y-m-d H:i') }}</td>
                            <td><span class="badge bg-light text-dark">{{ $tx->type }}</span></td>
                            <td>{{ $tx->direction }}</td>
                            <td class="text-end">{{ number_format((float) $tx->amount, 2) }}</td>
                            <td class="text-end">{{ number_format((float) $tx->balance_after, 2) }}</td>
                            <td>{{ $tx->pnr }}</td>
                            <td class="small">{{ $tx->description }}</td>
                            <td class="small">{{ $tx->creator?->email }}</td>
                            <td>
                                @if($tx->type !== 'reverse')
                                    <form method="POST" action="{{ route('admin.finance.wallets.reverse', $tx->id) }}" class="d-inline"
                                        data-swal-confirm data-swal-title="Reverse this transaction?" data-swal-icon="warning"
                                        data-swal-confirm-text="Reverse" data-swal-confirm-color="#dc3545">
                                        @csrf
                                        <button class="btn btn-xs btn-outline-danger btn-sm">Reverse</button>
                                    </form>
                                @endif
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>
    <div class="mt-3">{{ $transactions->links() }}</div>
</div>
@endsection
