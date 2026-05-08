@extends('admin.layouts.main')

@section('title', 'Roles Management')

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
                        <span class="title-icon">🛡️</span> Roles Management
                    </h1>
                    <p class="dashboard-subtitle">Manage system roles and their permissions</p>
                </div>
                <a href="{{ route($panelPrefix . '.roles.create') }}" class="btn btn-primary">
                    <i class="fas fa-plus me-2"></i>Add New Role
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

            <!-- Roles Grid -->
            <div class="row g-4">
                @forelse($roles as $role)
                    <div class="col-md-6 col-lg-4">
                        <div class="role-card card-modern h-100">

                            <!-- Card Header -->
                            <div class="role-card-header">
                                <div class="role-icon-wrap">
                                    <i class="fas fa-shield-alt"></i>
                                </div>
                                <div class="role-title-group">
                                    <h3 class="role-name">{{ $role->name }}</h3>
                                    <span class="role-slug">{{ $role->slug }}</span>
                                </div>
                                @if($role->id == 1)
                                    <span class="badge-protected">Protected</span>
                                @endif
                            </div>

                            <!-- Description -->
                            @if($role->description)
                                <p class="role-description">{{ $role->description }}</p>
                            @else
                                <p class="role-description role-description--empty">No description provided.</p>
                            @endif

                            <!-- Permission Count -->
                            <div class="role-meta">
                                <span class="role-meta-icon"><i class="fas fa-key"></i></span>
                                <span class="role-meta-label">{{ $role->permissions->count() }} Permission{{ $role->permissions->count() !== 1 ? 's' : '' }}</span>
                            </div>

                            <!-- Actions -->
                            <div class="role-actions">
                                <a href="{{ route($panelPrefix . '.roles.edit', $role->id) }}" class="btn-role-edit">
                                    <i class="fas fa-edit me-1"></i>Edit
                                </a>
                                @if($role->id != 1)
                                    <form action="{{ route($panelPrefix . '.roles.destroy', $role->id) }}" method="POST"
                                        onsubmit="return confirm('Are you sure you want to delete this role?');">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="btn-role-delete">
                                            <i class="fas fa-trash me-1"></i>Delete
                                        </button>
                                    </form>
                                @endif
                            </div>

                        </div>
                    </div>
                @empty
                    <div class="col-12">
                        <div class="card-modern empty-state">
                            <div class="empty-state-icon">
                                <i class="fas fa-shield-alt"></i>
                            </div>
                            <h4 class="empty-state-title">No Roles Found</h4>
                            <p class="empty-state-text">Get started by creating your first role.</p>
                            <a href="{{ route($panelPrefix . '.roles.create') }}" class="btn btn-primary mt-2">
                                <i class="fas fa-plus me-2"></i>Add New Role
                            </a>
                        </div>
                    </div>
                @endforelse
            </div>

        </div>
    </main>
</div>
@endsection

