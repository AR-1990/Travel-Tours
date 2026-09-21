@extends('admin.layouts.main')

@section('title', 'Sub Agents')

@section('content')
@php
    $counts = $counts ?? [];
    $user = Auth::user();
    $isAdmin = $user && $user->user_type === 'super_admin';
    $panelPrefix = $user && $user->user_type === 'tenant_admin' ? 'agent' : ($user && $user->user_type === 'sub_agent' ? 'subagent' : 'admin');
    $canView = $isAdmin || ($user && $user->hasPermission('managers.view'));
    $canCreate = $isAdmin || ($user && $user->hasPermission('managers.create'));
    $canEdit = $isAdmin || ($user && $user->hasPermission('managers.edit'));
    $canDelete = $isAdmin || ($user && $user->hasPermission('managers.delete'));
    $canRestore = $isAdmin || ($user && $user->hasPermission('managers.restore'));
@endphp
@if(! $canView)
    @php abort(403, 'Unauthorized action.'); @endphp
@endif

<div class="container-fluid panel-page">
    @include('admin.partials.page-header', [
        'title' => 'Sub agents',
        'subtitle' => 'Manage sub-agent accounts for your agency.',
        'icon' => 'fas fa-user-tie',
        'actions' => $canCreate
            ? '<a href="'.e(route($panelPrefix.'.managers.create')).'" class="btn btn-primary btn-sm"><i class="fas fa-plus me-1"></i> Add Sub-Agent</a>'
            : null,
    ])

    @include('admin.partials.flash')

    <div class="d-flex flex-wrap gap-2 mb-3">
        <span class="badge text-bg-secondary">All {{ $counts['all'] ?? 0 }}</span>
        <span class="badge text-bg-danger">Deleted {{ $counts['deleted'] ?? 0 }}</span>
    </div>

    <div class="panel-surface panel-surface--flush">
        <div class="table-responsive p-0">
            <table id="managersTable" class="table table-striped table-hover mb-0">
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
                    @forelse($managers as $manager)
                        <tr>
                            <td>{{ $manager->id }}</td>
                            <td>
                                <span class="badge bg-{{ $manager->role_id == 2 ? 'primary' : 'info' }}">
                                    {{ $manager->role->name ?? 'N/A' }}
                                </span>
                            </td>
                            <td>{{ $manager->first_name }}</td>
                            <td>{{ $manager->last_name }}</td>
                            <td>{{ $manager->email }}</td>
                            <td>
                                @if(method_exists($manager, 'trashed') && $manager->trashed())
                                    <span class="badge bg-danger">Deleted</span>
                                @else
                                    <span class="badge bg-success">Active</span>
                                @endif
                            </td>
                            <td class="text-nowrap">
                                @if(method_exists($manager, 'trashed') && $manager->trashed())
                                    @if($canRestore)
                                        <form action="{{ route($panelPrefix . '.managers.restore', $manager->id) }}" method="POST" class="d-inline"
                                            data-swal-confirm
                                            data-swal-title="Restore this sub-agent?"
                                            data-swal-text="They will be active again and can sign in."
                                            data-swal-icon="question"
                                            data-swal-confirm-text="Yes, restore"
                                            data-swal-confirm-color="#198754">
                                            @csrf
                                            <button type="submit" class="btn btn-success btn-sm">Restore</button>
                                        </form>
                                    @endif
                                @else
                                    @if($canEdit)
                                        <a href="{{ route($panelPrefix . '.managers.edit', $manager->id) }}" class="btn btn-outline-primary btn-sm">Edit</a>
                                    @endif
                                    @if($canDelete)
                                        <form action="{{ route($panelPrefix . '.managers.destroy', $manager->id) }}" method="POST" class="d-inline"
                                            data-swal-confirm
                                            data-swal-title="Delete this sub-agent?"
                                            data-swal-text="This account will be soft-deleted."
                                            data-swal-icon="warning"
                                            data-swal-confirm-text="Yes, delete"
                                            data-swal-confirm-color="#dc3545">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit" class="btn btn-outline-danger btn-sm">Delete</button>
                                        </form>
                                    @endif
                                @endif
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="7" class="text-center text-muted py-4">No sub-agents found</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script src="https://cdn.datatables.net/1.13.6/js/jquery.dataTables.min.js"></script>
<script src="https://cdn.datatables.net/1.13.6/js/dataTables.bootstrap5.min.js"></script>
<script>
    $(function () {
        if ($.fn.DataTable && $('#managersTable tbody tr').length && !$('#managersTable tbody tr td[colspan]').length) {
            $('#managersTable').DataTable({ pageLength: 25, order: [[0, 'desc']] });
        }
    });
</script>
@endpush
