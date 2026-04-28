@extends('admin.layouts.main')

@section('title', 'Permissions Management')

@section('content')
<div class="container-fluid">
    <!-- Header -->
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h1 class="h3 mb-1 text-gray-800">Permissions Management</h1>
            <p class="text-gray-600 mb-0">Manage system permissions</p>
        </div>
        <a href="{{ route('admin.permissions.create') }}" class="btn btn-primary">
            <i class="fas fa-plus me-2"></i>Add New Permission
        </a>
    </div>

    <!-- Alerts -->
    @if (session('success'))
        <div class="alert alert-success alert-dismissible fade show" role="alert">
            {{ session('success') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    @endif

    <!-- Permissions by Group -->
    @foreach($permissions as $group => $groupPermissions)
        <div class="card-modern mb-4">
            <h3 class="h5 mb-3 text-indigo-600">{{ ucfirst(str_replace('-', ' ', $group)) }}</h3>
            <div class="table-responsive">
                <table class="table table-hover">
                    <thead>
                        <tr>
                            <th>ID</th>
                            <th>Name</th>
                            <th>Slug</th>
                            <th>Description</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($groupPermissions as $permission)
                            <tr>
                                <td>{{ $permission->id }}</td>
                                <td><strong>{{ $permission->name }}</strong></td>
                                <td><code class="text-muted">{{ $permission->slug }}</code></td>
                                <td>{{ $permission->description ?? '-' }}</td>
                                <td>
                                    <div class="d-flex gap-2">
                                        <a href="{{ route('admin.permissions.edit', $permission->id) }}" class="btn btn-sm btn-primary">
                                            <i class="fas fa-edit"></i>
                                        </a>
                                        <form action="{{ route('admin.permissions.destroy', $permission->id) }}" method="POST" onsubmit="return confirm('Are you sure you want to delete this permission?');">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit" class="btn btn-sm btn-danger">
                                                <i class="fas fa-trash"></i>
                                            </button>
                                        </form>
                                    </div>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>
    @endforeach

    @if($permissions->isEmpty())
        <div class="card-modern text-center py-5">
            <i class="fas fa-key fa-3x text-gray-400 mb-3"></i>
            <p class="text-gray-600 mb-0">No permissions found.</p>
        </div>
    @endif
</div>
@endsection
