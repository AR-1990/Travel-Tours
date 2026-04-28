@extends('admin.layouts.main')

@section('title', 'Roles Management')

@section('content')
<div class="container-fluid">
    <!-- Header -->
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h1 class="h3 mb-1 text-gray-800">Roles Management</h1>
            <p class="text-gray-600 mb-0">Manage system roles and their permissions</p>
        </div>
        <a href="{{ route('admin.roles.create') }}" class="btn btn-primary">
            <i class="fas fa-plus me-2"></i>Add New Role
        </a>
    </div>

    <!-- Alerts -->
    @if (session('success'))
        <div class="alert alert-success alert-dismissible fade show" role="alert">
            {{ session('success') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    @endif

    @if (session('error'))
        <div class="alert alert-danger alert-dismissible fade show" role="alert">
            {{ session('error') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    @endif

    <!-- Roles Grid -->
    <div class="row g-4">
        @forelse($roles as $role)
            <div class="col-md-6 col-lg-4">
                <div class="card-modern h-100">
                    <div class="d-flex justify-content-between align-items-start mb-3">
                        <div>
                            <h3 class="h5 mb-1 text-gray-800">{{ $role->name }}</h3>
                            <span class="badge bg-info">{{ $role->slug }}</span>
                        </div>
                        @if($role->id == 1)
                            <span class="badge bg-danger">Protected</span>
                        @endif
                    </div>
                    
                    @if($role->description)
                        <p class="text-gray-600 mb-3">{{ $role->description }}</p>
                    @endif

                    <div class="mb-3">
                        <small class="text-gray-500">Permissions: {{ $role->permissions->count() }}</small>
                    </div>

                    <div class="d-flex gap-2">
                        <a href="{{ route('admin.roles.edit', $role->id) }}" class="btn btn-sm btn-primary flex-fill">
                            <i class="fas fa-edit me-1"></i>Edit
                        </a>
                        @if($role->id != 1)
                            <form action="{{ route('admin.roles.destroy', $role->id) }}" method="POST" class="flex-fill" onsubmit="return confirm('Are you sure you want to delete this role?');">
                                @csrf
                                @method('DELETE')
                                <button type="submit" class="btn btn-sm btn-danger w-100">
                                    <i class="fas fa-trash me-1"></i>Delete
                                </button>
                            </form>
                        @endif
                    </div>
                </div>
            </div>
        @empty
            <div class="col-12">
                <div class="card-modern text-center py-5">
                    <i class="fas fa-shield-alt fa-3x text-gray-400 mb-3"></i>
                    <p class="text-gray-600 mb-0">No roles found.</p>
                </div>
            </div>
        @endforelse
    </div>
</div>
@endsection
