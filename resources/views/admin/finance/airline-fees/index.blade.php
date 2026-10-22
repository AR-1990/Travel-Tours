@extends('admin.layouts.main')

@section('title', 'Airline fees')

@section('content')
<div class="container-fluid panel-page">
    @include('admin.partials.page-header', [
        'title' => 'Airline service / other fees',
        'subtitle' => 'Manual fees by airline (service, ticketing, other). Applied on booking.',
        'icon' => 'fas fa-plane',
    ])
    @include('admin.partials.flash')

    <div class="card border-0 shadow-sm mb-4">
        <div class="card-body">
            <form method="POST" action="{{ route('admin.finance.airline-fees.store') }}" class="row g-2 align-items-end">
                @csrf
                <div class="col-md-1">
                    <label class="form-label small">Airline</label>
                    <input type="text" name="airline_code" maxlength="2" class="form-control form-control-sm text-uppercase" placeholder="PK" required>
                </div>
                <div class="col-md-2">
                    <label class="form-label small">Type</label>
                    <select name="fee_type" class="form-select form-select-sm">
                        <option value="service">Service fee</option>
                        <option value="ticketing">Ticketing fee</option>
                        <option value="other">Other</option>
                    </select>
                </div>
                <div class="col-md-2">
                    <label class="form-label small">Label</label>
                    <input type="text" name="label" class="form-control form-control-sm" required>
                </div>
                <div class="col-md-1">
                    <label class="form-label small">Mode</label>
                    <select name="mode" class="form-select form-select-sm">
                        <option value="fixed">Fixed</option>
                        <option value="percent">Percent</option>
                    </select>
                </div>
                <div class="col-md-1">
                    <label class="form-label small">Amount</label>
                    <input type="number" step="0.0001" name="amount" class="form-control form-control-sm" required>
                </div>
                <div class="col-md-1">
                    <label class="form-label small">Curr</label>
                    <input type="text" name="currency" value="USD" maxlength="3" class="form-control form-control-sm text-uppercase" required>
                </div>
                <div class="col-md-2">
                    <label class="form-label small">Agency (optional)</label>
                    <select name="tenant_id" class="form-select form-select-sm">
                        <option value="">All</option>
                        @foreach($tenants as $t)
                            <option value="{{ $t->id }}">{{ $t->name }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-2">
                    <label class="form-check small"><input type="checkbox" name="is_active" value="1" class="form-check-input" checked> Active</label>
                    <button class="btn btn-sm btn-primary d-block mt-1">Save fee</button>
                </div>
            </form>
        </div>
    </div>

    <div class="panel-surface panel-surface--flush">
        <div class="table-responsive">
            <table class="table table-striped mb-0">
                <thead>
                    <tr>
                        <th>Airline</th>
                        <th>Type</th>
                        <th>Label</th>
                        <th>Mode</th>
                        <th>Amount</th>
                        <th>Agency</th>
                        <th>Active</th>
                        <th></th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($fees as $fee)
                        <tr>
                            <td><code>{{ $fee->airline_code }}</code></td>
                            <td>{{ $fee->fee_type }}</td>
                            <td>{{ $fee->label }}</td>
                            <td>{{ $fee->mode }}</td>
                            <td>{{ $fee->amount }} {{ $fee->currency }}</td>
                            <td>{{ $fee->tenant?->name ?: 'All' }}</td>
                            <td>{{ $fee->is_active ? 'Yes' : 'No' }}</td>
                            <td>
                                <form method="POST" action="{{ route('admin.finance.airline-fees.destroy', $fee->id) }}" class="d-inline" data-swal-confirm data-swal-title="Delete fee?">
                                    @csrf @method('DELETE')
                                    <button class="btn btn-sm btn-outline-danger">Delete</button>
                                </form>
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>
    <div class="mt-3">{{ $fees->links() }}</div>
</div>
@endsection
