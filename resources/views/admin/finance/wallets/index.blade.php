@extends('admin.layouts.main')

@section('title', 'Agency wallets')

@section('content')
<div class="container-fluid panel-page">
    @include('admin.partials.page-header', [
        'title' => 'Agency wallets',
        'subtitle' => 'Top-up, refund, and reverse wallet transactions.',
        'icon' => 'fas fa-wallet',
    ])
    @include('admin.partials.flash')

    <form method="GET" class="row g-2 mb-3">
        <div class="col-md-4">
            <select name="tenant_id" class="form-select form-select-sm">
                <option value="">All agencies</option>
                @foreach($tenants as $t)
                    <option value="{{ $t->id }}" @selected(request('tenant_id') == $t->id)>{{ $t->name }} @if($t->agency_code)({{ $t->agency_code }})@endif</option>
                @endforeach
            </select>
        </div>
        <div class="col-md-2">
            <button class="btn btn-sm btn-primary">Filter</button>
        </div>
    </form>

    <div class="panel-surface panel-surface--flush">
        <div class="table-responsive">
            <table class="table table-striped mb-0">
                <thead>
                    <tr>
                        <th>Agency</th>
                        <th>Currency</th>
                        <th class="text-end">Balance</th>
                        <th>Active</th>
                        <th></th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($wallets as $wallet)
                        <tr>
                            <td>{{ $wallet->tenant?->name ?? ('#'.$wallet->tenant_id) }}</td>
                            <td>{{ $wallet->currency }}</td>
                            <td class="text-end fw-semibold">{{ number_format((float) $wallet->balance, 2) }}</td>
                            <td>{{ $wallet->is_active ? 'Yes' : 'No' }}</td>
                            <td><a href="{{ route('admin.finance.wallets.show', $wallet->tenant_id) }}" class="btn btn-sm btn-outline-primary">Open</a></td>
                        </tr>
                    @empty
                        <tr><td colspan="5" class="text-muted p-3">No wallets yet. Open an agency to create one on first top-up.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
    <div class="mt-3">{{ $wallets->links() }}</div>

    <div class="card border-0 shadow-sm mt-4">
        <div class="card-body">
            <h2 class="h6">Create / open wallet</h2>
            <form method="GET" action="{{ url('/admin/finance/wallets') }}" class="row g-2" id="open-wallet-form">
                <div class="col-md-6">
                    <select id="open-tenant" class="form-select form-select-sm" required>
                        <option value="">Select agency…</option>
                        @foreach($tenants as $t)
                            <option value="{{ $t->id }}">{{ $t->name }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-3">
                    <button type="button" class="btn btn-sm btn-primary" onclick="var id=document.getElementById('open-tenant').value; if(id) location.href='{{ url('/admin/finance/wallets') }}/'+id;">Open wallet</button>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection
