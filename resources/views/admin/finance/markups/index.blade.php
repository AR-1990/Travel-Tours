@extends('admin.layouts.main')

@section('title', 'Ticket fee rules')

@section('content')
<div class="container-fluid panel-page">
    @include('admin.partials.page-header', [
        'title' => 'Ticket fee rules',
        'subtitle' => 'Rules by airline, provider, or agent — percent or fixed. Applied as ticket fee on booking.',
        'icon' => 'fas fa-percent',
    ])
    @include('admin.partials.flash')

    <div class="card border-0 shadow-sm mb-4">
        <div class="card-body">
            <h2 class="h6 mb-3">Add rule</h2>
            <form method="POST" action="{{ route('admin.finance.markups.store') }}" class="row g-2 align-items-end">
                @csrf
                <div class="col-md-2">
                    <label class="form-label small">Scope</label>
                    <select name="scope" class="form-select form-select-sm" required>
                        <option value="global">Global</option>
                        <option value="provider">Provider</option>
                        <option value="airline">Airline</option>
                        <option value="agent">Agent</option>
                    </select>
                </div>
                <div class="col-md-2">
                    <label class="form-label small">Scope key</label>
                    <input type="text" name="scope_key" class="form-control form-control-sm" placeholder="EY / travelport">
                </div>
                <div class="col-md-2">
                    <label class="form-label small">Agency (optional)</label>
                    <select name="tenant_id" class="form-select form-select-sm">
                        <option value="">All / global</option>
                        @foreach($tenants as $t)
                            <option value="{{ $t->id }}">{{ $t->name }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-2">
                    <label class="form-label small">Mode</label>
                    <select name="mode" class="form-select form-select-sm">
                        <option value="percent">Percent</option>
                        <option value="fixed">Fixed</option>
                    </select>
                </div>
                <div class="col-md-1">
                    <label class="form-label small">Value</label>
                    <input type="number" step="0.0001" name="value" class="form-control form-control-sm" required>
                </div>
                <div class="col-md-1">
                    <label class="form-label small">Priority</label>
                    <input type="number" name="priority" value="100" class="form-control form-control-sm">
                </div>
                <div class="col-md-2">
                    <label class="form-check small">
                        <input type="checkbox" name="is_active" value="1" class="form-check-input" checked> Active
                    </label>
                    <button class="btn btn-sm btn-primary d-block mt-1">Save</button>
                </div>
            </form>
        </div>
    </div>

    <div class="panel-surface panel-surface--flush">
        <div class="table-responsive">
            <table class="table table-striped mb-0">
                <thead>
                    <tr>
                        <th>Label</th>
                        <th>Agency</th>
                        <th>Mode</th>
                        <th>Value</th>
                        <th>Priority</th>
                        <th>Active</th>
                        <th></th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($markups as $m)
                        <tr>
                            <td>{{ $m->label() }} <code class="small">{{ $m->scope_key }}</code></td>
                            <td>{{ $m->tenant?->name ?: '—' }}</td>
                            <td>{{ $m->mode }}</td>
                            <td>{{ $m->value }}</td>
                            <td>{{ $m->priority }}</td>
                            <td>{{ $m->is_active ? 'Yes' : 'No' }}</td>
                            <td class="text-nowrap">
                                <form method="POST" action="{{ route('admin.finance.markups.update', $m->id) }}" class="d-inline-flex gap-1 align-items-center">
                                    @csrf @method('PUT')
                                    <select name="mode" class="form-select form-select-sm" style="width:6rem">
                                        <option value="percent" @selected($m->mode==='percent')>%</option>
                                        <option value="fixed" @selected($m->mode==='fixed')>fix</option>
                                    </select>
                                    <input type="number" step="0.0001" name="value" value="{{ $m->value }}" class="form-control form-control-sm" style="width:5rem">
                                    <input type="hidden" name="is_active" value="{{ $m->is_active ? 1 : 0 }}">
                                    <button class="btn btn-sm btn-outline-primary">Update</button>
                                </form>
                                <form method="POST" action="{{ route('admin.finance.markups.destroy', $m->id) }}" class="d-inline"
                                    data-swal-confirm data-swal-title="Delete markup?" data-swal-icon="warning">
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
    <div class="mt-3">{{ $markups->links() }}</div>
</div>
@endsection
