@extends('admin.layouts.main')

@section('title', 'Agency wallet')

@section('content')
<div class="container-fluid panel-page">
    @include('admin.partials.page-header', [
        'title' => 'Wallet balance',
        'subtitle' => $tenant->name.' · '.$wallet->currency.' '.number_format((float) $wallet->balance, 2),
        'icon' => 'fas fa-wallet',
    ])
    @include('admin.partials.flash')

    <div class="row g-3 mb-4">
        <div class="col-md-4">
            <div class="card border-0 shadow-sm h-100">
                <div class="card-body">
                    <div class="text-muted small">Available balance</div>
                    <div class="display-6">{{ number_format((float) $wallet->balance, 2) }}</div>
                    <div class="text-muted">{{ $wallet->currency }}</div>
                    <p class="small text-muted mt-3 mb-0">Top-ups are credited by the platform admin. Use Wallet as payment when booking.</p>
                </div>
            </div>
        </div>
        <div class="col-md-8">
            <div class="card border-0 shadow-sm h-100">
                <div class="card-body">
                    <h2 class="h6 mb-3">Quick links</h2>
                    <div class="d-flex flex-wrap gap-2">
                        <a href="{{ route('agent.finance.statement') }}" class="btn btn-sm btn-outline-primary">Account statement</a>
                        <a href="{{ route('agent.finance.sales') }}" class="btn btn-sm btn-outline-primary">Sales report</a>
                    </div>
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
                    </tr>
                </thead>
                <tbody>
                    @forelse($transactions as $tx)
                        <tr>
                            <td>{{ $tx->id }}</td>
                            <td class="small">{{ optional($tx->created_at)->format('Y-m-d H:i') }}</td>
                            <td><span class="badge bg-light text-dark">{{ $tx->type }}</span></td>
                            <td>{{ $tx->direction }}</td>
                            <td class="text-end">{{ number_format((float) $tx->amount, 2) }}</td>
                            <td class="text-end">{{ number_format((float) $tx->balance_after, 2) }}</td>
                            <td>{{ $tx->pnr }}</td>
                            <td class="small">{{ $tx->description }}</td>
                        </tr>
                    @empty
                        <tr><td colspan="8" class="text-muted text-center py-4">No wallet activity yet.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        <div class="p-3">{{ $transactions->links() }}</div>
    </div>
</div>
@endsection
