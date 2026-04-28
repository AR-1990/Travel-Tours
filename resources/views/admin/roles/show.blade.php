@extends('admin.layouts.main')

@section('title', 'View Role')

@section('content')
<div class="container-fluid">
    <div class="row">
        <div class="col-lg-10 mx-auto">
            <!-- Header -->
            <div class="d-flex justify-content-between align-items-center mb-4">
                <div>
                    <h1 class="h3 mb-1 text-gray-800">Role Details</h1>
                    <p class="text-gray-600 mb-0">View role information and permissions</p>
                </div>
                <div class="d-flex gap-2">
                    <a href="{{ route('admin.roles.edit', $role->id) }}" class="btn btn-primary">
                        <i class="fas fa-edit me-2"></i>Edit
                    </a>
                    <a href="{{ route('admin.roles') }}" class="btn btn-secondary">
                        <i class="fas fa-arrow-left me-2"></i>Back
                    </a>
                </div>
            </div>

            <!-- Role Info -->
            <div class="card-modern mb-4">
                <h3 class="h5 mb-3">{{ $role->name }}</h3>
                <div class="row">
                    <div class="col-md-6">
                        <p><strong>Slug:</strong> <span class="badge bg-info">{{ $role->slug }}</span></p>
                        @if($role->description)
                            <p><strong>Description:</strong> {{ $role->description }}</p>
                        @endif
                        <p><strong>Users with this role:</strong> {{ $role->users->count() }}</p>
                    </div>
                    <div class="col-md-6">
                        <p><strong>Total Permissions:</strong> {{ $role->permissions->count() }}</p>
                        <p><strong>Created:</strong> {{ $role->created_at->format('M d, Y') }}</p>
                        <p><strong>Updated:</strong> {{ $role->updated_at->format('M d, Y') }}</p>
                    </div>
                </div>
            </div>

            <!-- Permissions -->
            <div class="card-modern">
                <h4 class="h5 mb-3">Permissions</h4>
                @if($role->permissions->count() > 0)
                    <div class="row">
                        @foreach($role->permissions->groupBy('group') as $group => $groupPermissions)
                            <div class="col-md-6 mb-3">
                                <h6 class="fw-bold text-indigo-600">{{ ucfirst(str_replace('-', ' ', $group)) }}</h6>
                                <ul class="list-unstyled">
                                    @foreach($groupPermissions as $permission)
                                        <li class="mb-1">
                                            <i class="fas fa-check-circle text-success me-2"></i>
                                            {{ $permission->name }}
                                        </li>
                                    @endforeach
                                </ul>
                            </div>
                        @endforeach
                    </div>
                @else
                    <p class="text-gray-600 mb-0">No permissions assigned to this role.</p>
                @endif
            </div>
        </div>
    </div>
</div>
@endsection
