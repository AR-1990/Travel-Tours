@extends('admin.layouts.main')

@section('title', 'Agents')

@section('content')
<div class="container-fluid">
    <div class="card-modern mb-4">
        <h3 class="h4 mb-3">Create Agent (Super Admin)</h3>
        <form method="POST" action="{{ route('admin.tenants.store') }}" class="row g-3">
            @csrf
            <div class="col-md-4">
                <input type="text" name="tenant_name" class="form-control" placeholder="Agent Name" required>
            </div>
            <div class="col-md-4">
                <input type="email" name="tenant_email" class="form-control" placeholder="Agent Email">
            </div>
            <div class="col-md-4">
                <input type="text" name="tenant_phone" class="form-control" placeholder="Agent Phone">
            </div>
            <div class="col-md-3">
                <input type="text" name="admin_first_name" class="form-control" placeholder="Admin First Name" required>
            </div>
            <div class="col-md-3">
                <input type="text" name="admin_last_name" class="form-control" placeholder="Admin Last Name" required>
            </div>
            <div class="col-md-3">
                <input type="email" name="admin_email" class="form-control" placeholder="Admin Email" required>
            </div>
            <div class="col-md-3">
                <input type="password" name="admin_password" class="form-control" placeholder="Admin Password" required>
            </div>
            <div class="col-12">
                <button class="btn btn-primary">Create Agent</button>
            </div>
        </form>
    </div>

    <div class="card-modern">
        <h3 class="h4 mb-3">Agent Requests</h3>
        <div class="table-responsive">
            <table class="table table-striped">
                <thead>
                    <tr>
                        <th>Name</th>
                        <th>Email</th>
                        <th>Sub Agents</th>
                        <th>Status</th>
                        <th>Active</th>
                        <th>Action</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($tenants as $tenant)
                        <tr>
                            <td>{{ $tenant->name }}</td>
                            <td>{{ $tenant->email }}</td>
                            <td>{{ $tenant->sub_agents_count }}</td>
                            <td><span class="badge bg-{{ $tenant->status === 'approved' ? 'success' : ($tenant->status === 'rejected' ? 'danger' : 'warning') }}">{{ ucfirst($tenant->status) }}</span></td>
                            <td>{{ $tenant->is_active ? 'Yes' : 'No' }}</td>
                            <td class="d-flex gap-2">
                                <a href="{{ route('admin.tenants.show', $tenant->id) }}" class="btn btn-sm btn-primary">Details</a>
                                @if($tenant->status === 'pending' || $tenant->status === 'rejected')
                                    <form method="POST" action="{{ route('admin.tenants.approve', $tenant->id) }}">
                                        @csrf
                                        <button class="btn btn-sm btn-success">Approve</button>
                                    </form>
                                @endif
                                @if($tenant->status !== 'rejected')
                                    <form method="POST" action="{{ route('admin.tenants.reject', $tenant->id) }}">
                                        @csrf
                                        <button class="btn btn-sm btn-danger">Reject</button>
                                    </form>
                                @endif
                            </td>
                        </tr>
                    @empty
                        <tr><td colspan="6">No tenants found.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>
@endsection
