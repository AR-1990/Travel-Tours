@extends('admin.layouts.main')

@section('title', 'Agent Details')

@section('content')
<div class="container-fluid">
    <div class="card-modern mb-4">
        <div class="d-flex justify-content-between align-items-center">
            <h3 class="h4 mb-0">Agent Details</h3>
            <a href="{{ route('admin.tenants.index') }}" class="btn btn-secondary btn-sm">Back</a>
        </div>
        <hr>
        <div class="row g-3">
            <div class="col-md-4"><strong>Name:</strong> {{ $tenant->name }}</div>
            <div class="col-md-4"><strong>Email:</strong> {{ $tenant->email ?? '-' }}</div>
            <div class="col-md-4"><strong>Phone:</strong> {{ $tenant->phone ?? '-' }}</div>
            <div class="col-md-4"><strong>Status:</strong> {{ ucfirst($tenant->status) }}</div>
            <div class="col-md-4"><strong>Active:</strong> {{ $tenant->is_active ? 'Yes' : 'No' }}</div>
            <div class="col-md-4"><strong>Approved At:</strong> {{ $tenant->approved_at ? $tenant->approved_at->format('Y-m-d H:i') : '-' }}</div>
        </div>
    </div>

    <div class="card-modern mb-4">
        <h4 class="h5 mb-3">Agent Admins</h4>
        <div class="table-responsive">
            <table class="table table-bordered table-striped">
                <thead>
                    <tr>
                        <th>Name</th>
                        <th>Email</th>
                        <th>Designation</th>
                        <th>Status</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($tenantAdmins as $user)
                        <tr>
                            <td>{{ $user->first_name }} {{ $user->last_name }}</td>
                            <td>{{ $user->email }}</td>
                            <td>{{ $user->designation ?? '-' }}</td>
                            <td>{{ $user->is_active ? 'Active' : 'Inactive' }}</td>
                        </tr>
                    @empty
                        <tr><td colspan="4">No agent admin found.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    <div class="card-modern">
        <h4 class="h5 mb-3">Sub Agents (Table)</h4>
        <div class="table-responsive">
            <table class="table table-bordered table-striped">
                <thead>
                    <tr>
                        <th>Name</th>
                        <th>Email</th>
                        <th>Role/Category</th>
                        <th>Designation</th>
                        <th>Status</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($subAgents as $agent)
                        <tr>
                            <td>{{ $agent->first_name }} {{ $agent->last_name }}</td>
                            <td>{{ $agent->email }}</td>
                            <td>{{ $agent->role->name ?? '-' }}</td>
                            <td>{{ $agent->designation ?? '-' }}</td>
                            <td>{{ $agent->is_active ? 'Active' : 'Inactive' }}</td>
                        </tr>
                    @empty
                        <tr><td colspan="5">No sub agents found for this tenant.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>
@endsection
