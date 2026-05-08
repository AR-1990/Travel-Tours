@extends('admin.layouts.main')

@section('title', 'Permissions Management')

@section('content')
@php
    $user = auth()->user();
    $panelPrefix = $user && $user->user_type === 'tenant_admin' ? 'agent' : ($user && $user->user_type === 'sub_agent' ? 'subagent' : 'admin');
@endphp

<div class="admin-panel">
    <!-- ========== SIDEBAR START ========== -->
    @include('admin.layouts.sidebar')
    <!-- ========== SIDEBAR END ========== -->

    <main class="main-content flex-grow-1">
        <div class="container-fluid">

            <!-- Page Header -->
            <div class="page-header mb-4">
                <div>
                    <h1 class="dashboard-title">
                        <span class="title-icon">🔑</span> Permissions Management
                    </h1>
                    <p class="dashboard-subtitle">Manage system permissions grouped by module</p>
                </div>
                <a href="{{ route($panelPrefix . '.permissions.create') }}" class="btn btn-primary">
                    <i class="fas fa-plus me-2"></i>Add New Permission
                </a>
            </div>

            <!-- Alerts -->
            @if (session('success'))
                <div class="alert alert-success alert-dismissible fade show mb-4" role="alert">
                    <i class="fas fa-check-circle me-2"></i>{{ session('success') }}
                    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                </div>
            @endif

            @if (session('error'))
                <div class="alert alert-danger alert-dismissible fade show mb-4" role="alert">
                    <i class="fas fa-exclamation-circle me-2"></i>{{ session('error') }}
                    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                </div>
            @endif

            <!-- Permissions by Group -->
            @forelse($permissions as $group => $groupPermissions)
                <div class="perm-group card-modern mb-4">

                    <!-- Group Header -->
                    <div class="perm-group-header">
                        <div class="perm-group-icon">
                            <i class="fas fa-layer-group"></i>
                        </div>
                        <div>
                            <h3 class="perm-group-title">{{ ucfirst(str_replace('-', ' ', $group)) }}</h3>
                            <span class="perm-group-count">{{ count($groupPermissions) }} permission{{ count($groupPermissions) !== 1 ? 's' : '' }}</span>
                        </div>
                    </div>

                    <!-- Table -->
                    <div class="table-responsive">
                        <table class="perm-table">
                            <thead>
                                <tr>
                                    <th>#</th>
                                    <th>Name</th>
                                    <th>Slug</th>
                                    <th>Description</th>
                                    <th>Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($groupPermissions as $permission)
                                    <tr>
                                        <td class="perm-id">{{ $permission->id }}</td>
                                        <td class="perm-name">{{ $permission->name }}</td>
                                        <td><span class="perm-slug">{{ $permission->slug }}</span></td>
                                        <td class="perm-desc">{{ $permission->description ?? '—' }}</td>
                                        <td>
                                            <div class="perm-actions">
                                                <a href="{{ route($panelPrefix . '.permissions.edit', $permission->id) }}" class="btn-perm-edit" title="Edit">
                                                    <i class="fas fa-edit"></i>
                                                </a>
                                                <form action="{{ route($panelPrefix . '.permissions.destroy', $permission->id) }}" method="POST"
                                                    onsubmit="return confirm('Are you sure you want to delete this permission?');">
                                                    @csrf
                                                    @method('DELETE')
                                                    <button type="submit" class="btn-perm-delete" title="Delete">
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
            @empty
                <div class="card-modern empty-state">
                    <div class="empty-state-icon">
                        <i class="fas fa-key"></i>
                    </div>
                    <h4 class="empty-state-title">No Permissions Found</h4>
                    <p class="empty-state-text">Get started by creating your first permission.</p>
                    <a href="{{ route($panelPrefix . '.permissions.create') }}" class="btn btn-primary mt-2">
                        <i class="fas fa-plus me-2"></i>Add New Permission
                    </a>
                </div>
            @endforelse

        </div>
    </main>
</div>
@endsection

