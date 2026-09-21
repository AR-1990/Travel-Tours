@extends('admin.layouts.main')

@section('title', 'Sub Agents')

@section('content')
<div class="container-fluid panel-page">
    @include('admin.partials.page-header', [
        'title' => 'Sub Agents',
        'subtitle' => 'Manage your agent team from one table.',
        'icon' => 'fas fa-user-friends',
        'actions' => '<a href="'.e(route('agent.managers.create')).'" class="btn btn-primary btn-sm"><i class="fas fa-plus me-1"></i> Add Sub-Agent</a>',
    ])

    @include('admin.partials.flash')

    <div class="d-flex gap-2 mb-3">
        <span class="badge bg-secondary">All {{ $counts['all'] ?? 0 }}</span>
        <span class="badge bg-danger">Deleted {{ $counts['deleted'] ?? 0 }}</span>
    </div>

    <div class="panel-surface panel-surface--flush">
        <div class="table-responsive">
            <table id="subAgentsTable" class="table table-striped align-middle w-100 mb-0">
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
                                    <form action="{{ route('agent.managers.restore', $agent->id) }}" method="POST"
                                        data-swal-confirm
                                        data-swal-title="Restore this sub-agent?"
                                        data-swal-text="They will be able to sign in again."
                                        data-swal-icon="question"
                                        data-swal-confirm-text="Yes, restore"
                                        data-swal-confirm-color="#198754">
                                        @csrf
                                        <button type="submit" class="btn btn-sm btn-success">Restore</button>
                                    </form>
                                @else
                                    <a href="{{ route('agent.managers.edit', $agent->id) }}" class="btn btn-sm btn-primary"
                                        data-swal-confirm
                                        data-swal-title="Edit this sub-agent?"
                                        data-swal-text="You will open the edit form."
                                        data-swal-icon="question"
                                        data-swal-confirm-text="Continue"
                                        data-swal-confirm-color="#053750">Edit</a>
                                    <form action="{{ route('agent.managers.destroy', $agent->id) }}" method="POST"
                                        data-swal-confirm
                                        data-swal-title="Delete this sub-agent?"
                                        data-swal-text="This account will be soft-deleted."
                                        data-swal-icon="warning"
                                        data-swal-confirm-text="Yes, delete"
                                        data-swal-confirm-color="#dc3545">
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
@endsection
