@extends('admin.layouts.main')

@section('title', 'Users Management')

@section('content')
<div class="container-fluid">
    <!-- Header -->
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h1 class="h3 mb-1 text-gray-800">Users Management</h1>
            <p class="text-gray-600 mb-0">Platform users (public, agent admin, sub agent). Login uses email or username.</p>
        </div>
        <a href="{{ route('admin.users.create') }}" class="btn btn-primary">
            <i class="fas fa-plus me-2"></i>Add New User
        </a>
    </div>

    <!-- Alerts -->
    @if (session('success'))
        <div class="alert alert-success alert-dismissible fade show" role="alert">
            {{ session('success') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    @endif

    <!-- Filter Tabs -->
    <div class="card-modern mb-4">
        <div class="d-flex gap-2 border-bottom pb-3 mb-3">
            <a href="{{ route('admin.users', ['filter' => 'all']) }}" 
               class="btn {{ $filter === 'all' ? 'btn-primary' : 'btn-outline-secondary' }}">
                All Users ({{ $counts['all'] }})
            </a>
            <a href="{{ route('admin.users', ['filter' => 'deleted']) }}" 
               class="btn {{ $filter === 'deleted' ? 'btn-primary' : 'btn-outline-secondary' }}">
                Deleted Users ({{ $counts['deleted'] }})
            </a>
        </div>

        <!-- Users Table -->
        <div class="table-responsive">
            <table class="table table-hover" id="usersTable">
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>Name</th>
                        <th>Username</th>
                        <th>Email</th>
                        <th>Type</th>
                        <th>Role</th>
                        <th>Created</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($users as $user)
                        <tr>
                            <td>{{ $user->id }}</td>
                            <td>
                                <div class="d-flex align-items-center">
                                    <div class="w-10 h-10 bg-gradient-to-br from-indigo-500 to-purple-600 rounded-circle d-flex align-items-center justify-content-center text-white fw-bold me-2">
                                        {{ strtoupper(substr($user->first_name, 0, 1) . substr($user->last_name, 0, 1)) }}
                                    </div>
                                    <div>
                                        <div class="fw-semibold">{{ $user->first_name }} {{ $user->last_name }}</div>
                                    </div>
                                </div>
                            </td>
                            <td><code class="small">{{ $user->username }}</code></td>
                            <td>{{ $user->email }}</td>
                            <td>
                                @if($user->user_type === 'tenant_admin')
                                    <span class="badge bg-primary">Agent admin</span>
                                @elseif($user->user_type === 'sub_agent')
                                    <span class="badge bg-secondary">Sub agent</span>
                                @else
                                    <span class="badge bg-light text-dark">Public</span>
                                @endif
                            </td>
                            <td>
                                <span class="badge bg-info">{{ $user->role->name ?? '—' }}</span>
                            </td>
                            <td>{{ $user->created_at->format('M d, Y') }}</td>
                            <td>
                                <div class="d-flex gap-2">
                                    @if($filter === 'deleted')
                                        <form action="{{ route('admin.users.restore', $user->id) }}" method="POST" class="d-inline"
                                            data-swal-confirm
                                            data-swal-title="Restore this user?"
                                            data-swal-text="They will be able to sign in again if their account is active."
                                            data-swal-icon="question"
                                            data-swal-confirm-text="Yes, restore"
                                            data-swal-confirm-color="#198754">
                                            @csrf
                                            <button type="submit" class="btn btn-sm btn-success" title="Restore">
                                                <i class="fas fa-undo"></i>
                                            </button>
                                        </form>
                                    @else
                                        <a href="{{ route('admin.users.show', $user->id) }}" class="btn btn-sm btn-outline-secondary" title="View">
                                            <i class="fas fa-eye"></i>
                                        </a>
                                        <a href="{{ route('admin.users.edit', $user->id) }}" class="btn btn-sm btn-primary" title="Edit"
                                            data-swal-confirm
                                            data-swal-title="Edit this user?"
                                            data-swal-text="You will open the edit form. Unsaved changes elsewhere may be lost."
                                            data-swal-icon="question"
                                            data-swal-confirm-text="Continue"
                                            data-swal-confirm-color="#0d6efd">
                                            <i class="fas fa-edit"></i>
                                        </a>
                                        <form action="{{ route('admin.users.destroy', $user->id) }}" method="POST" class="d-inline"
                                            data-swal-confirm
                                            data-swal-title="Delete this user?"
                                            data-swal-text="This account will be soft-deleted. You can restore it later from the Deleted tab."
                                            data-swal-icon="warning"
                                            data-swal-confirm-text="Yes, delete"
                                            data-swal-confirm-color="#dc3545">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit" class="btn btn-sm btn-danger" title="Delete">
                                                <i class="fas fa-trash"></i>
                                            </button>
                                        </form>
                                    @endif
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="8" class="text-center py-4">
                                <div class="text-gray-500">
                                    <i class="fas fa-users fa-3x mb-3 d-block"></i>
                                    <p class="mb-0">No users found.</p>
                                </div>
                            </td>
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
    $(document).ready(function() {
        $('#usersTable').DataTable({
            order: [[0, 'desc']],
            pageLength: 25,
            language: {
                search: "Search:",
                lengthMenu: "Show _MENU_ entries",
                info: "Showing _START_ to _END_ of _TOTAL_ entries",
                infoEmpty: "No entries found",
                infoFiltered: "(filtered from _MAX_ total entries)"
            }
        });
    });
</script>
@endsection
