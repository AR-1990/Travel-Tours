@extends('admin.layouts.main')

@section('title', 'Sub Agents')

@section('content')
    <div class="container-fluid sub-agents-page px-0">
        <div class="d-flex justify-content-between align-items-center mb-4">
            <div>
                <h2 class="h4 mb-1">Sub Agents</h2>
                <p class="text-muted mb-0">Manage your agent team from one table.</p>
            </div>
            <a href="{{ route('agent.managers.create') }}" class="btn btn-primary">Add Sub-Agent</a>
        </div>

        @if(session('success'))
            <div class="alert alert-success">{{ session('success') }}</div>
        @endif

        <div class="d-flex gap-2 mb-3">
            <span class="badge bg-secondary">All {{ $counts['all'] ?? 0 }}</span>
            <span class="badge bg-danger">Deleted {{ $counts['deleted'] ?? 0 }}</span>
        </div>

        <div class="card-modern shadow-sm">
            <div class="table-responsive">
                <table id="subAgentsTable" class="table table-striped align-middle w-100">
                    <thead>
                        <tr>
                            <th>#</th>
                            <th>Role</th>
                            <th>First Name</th>
                            <th>Last Name</th>
                            <th>Email</th>
                            <th>Status</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($subAgents as $agent)
                            <tr>
                                <td>{{ $agent->id }}</td>
                                <td>{{ $agent->role->name ?? '-' }}</td>
                                <td>{{ $agent->first_name }}</td>
                                <td>{{ $agent->last_name }}</td>
                                <td>{{ $agent->email }}</td>
                                <td>
                                    @if(method_exists($agent, 'trashed') && $agent->trashed())
                                        <span class="badge bg-danger">Deleted</span>
                                    @else
                                        <span class="badge bg-success">Active</span>
                                    @endif
                                </td>
                                <td class="d-flex gap-2">
                                    @if(method_exists($agent, 'trashed') && $agent->trashed())
                                        <form action="{{ route('agent.managers.restore', $agent->id) }}" method="POST">
                                            @csrf
                                            <button type="submit" class="btn btn-sm btn-success">Restore</button>
                                        </form>
                                    @else
                                        <a href="{{ route('agent.managers.edit', $agent->id) }}" class="btn btn-sm btn-primary">Edit</a>
                                        <form action="{{ route('agent.managers.destroy', $agent->id) }}" method="POST" onsubmit="return confirm('Delete this sub-agent?')">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit" class="btn btn-sm btn-danger">Delete</button>
                                        </form>
                                    @endif
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="7" class="text-center">No sub-agents found</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>
@endsection

@section('scripts')
<script>
    $(function () {
        $('#subAgentsTable').DataTable({
            responsive: true,
            autoWidth: false,
            pageLength: 10,
        });
    });
</script>
<style>
    .sub-agents-page {
        max-width: 100%;
        padding-left: 0.5rem;
        padding-right: 0.5rem;
    }
    .sub-agents-page .card-modern {
        border-radius: 14px;
    }
    .sub-agents-page .table th {
        font-weight: 600;
        white-space: nowrap;
    }
    .sub-agents-page .table td {
        vertical-align: middle;
    }
    .sub-agents-page .dataTables_wrapper .dataTables_length,
    .sub-agents-page .dataTables_wrapper .dataTables_filter,
    .sub-agents-page .dataTables_wrapper .dataTables_info,
    .sub-agents-page .dataTables_wrapper .dataTables_paginate {
        padding: 0.75rem 0;
    }
    .sub-agents-page .badge {
        font-size: 0.85rem;
        padding: 0.5rem 0.75rem;
    }
</style>
@endsection
