@extends('admin.layouts.main')

@section('title', 'Agents')

@section('content')
<div class="container-fluid">
    @include('admin.tenants._agency-create-form')

    <div class="card-modern">
        <h3 class="h4 mb-3">Agent requests &amp; agencies</h3>
        <div class="table-responsive">
            <table class="table table-striped">
                <thead>
                    <tr>
                        <th>Name</th>
                        <th>Agency code</th>
                        <th>Agent code</th>
                        <th>Office type</th>
                        <th>Email</th>
                        <th>Sub agents</th>
                        <th>Status</th>
                        <th>Active</th>
                        <th>Action</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($tenants as $tenant)
                        <tr>
                            <td>{{ $tenant->name }}</td>
                            <td><code>{{ $tenant->agency_code }}</code></td>
                            <td><code>{{ $tenant->agent_code }}</code></td>
                            <td>{{ str_replace('_', ' ', $tenant->office_type ?? 'b2b_agent') }}</td>
                            <td>{{ $tenant->email }}</td>
                            <td>{{ $tenant->sub_agents_count }}</td>
                            <td><span class="badge bg-{{ $tenant->status === 'approved' ? 'success' : ($tenant->status === 'rejected' ? 'danger' : 'warning') }}">{{ ucfirst($tenant->status) }}</span></td>
                            <td>{{ $tenant->is_active ? 'Yes' : 'No' }}</td>
                            <td class="d-flex flex-wrap gap-2">
                                <a href="{{ route('admin.tenants.show', $tenant->id) }}" class="btn btn-sm btn-primary">Details</a>
                                @if($tenant->status === 'pending' || $tenant->status === 'rejected')
                                    <form method="POST" action="{{ route('admin.tenants.approve', $tenant->id) }}"
                                        data-swal-confirm
                                        data-swal-title="Approve this agency?"
                                        data-swal-text="The agency and its admins will be activated."
                                        data-swal-icon="question"
                                        data-swal-confirm-text="Yes, approve"
                                        data-swal-confirm-color="#198754">
                                        @csrf
                                        <button class="btn btn-sm btn-success" type="submit">Approve</button>
                                    </form>
                                @endif
                                @if($tenant->status !== 'rejected')
                                    <form method="POST" action="{{ route('admin.tenants.reject', $tenant->id) }}"
                                        data-swal-confirm
                                        data-swal-title="Reject this agency?"
                                        data-swal-text="The agency will be marked rejected and deactivated."
                                        data-swal-icon="warning"
                                        data-swal-confirm-text="Yes, reject"
                                        data-swal-confirm-color="#dc3545">
                                        @csrf
                                        <button class="btn btn-sm btn-danger" type="submit">Reject</button>
                                    </form>
                                @endif
                            </td>
                        </tr>
                    @empty
                        <tr><td colspan="9">No tenants found.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>
@endsection
