@extends('admin.layouts.main')

@section('title', 'Create Permission')

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
                        <span class="title-icon">🔐</span> Create Permission
                    </h1>
                    <p class="dashboard-subtitle">Add a new permission to the system</p>
                </div>
                <a href="{{ route($panelPrefix . '.permissions') }}" class="btn btn-secondary">
                    <i class="fas fa-arrow-left me-2"></i>Back to Permissions
                </a>
            </div>

            <!-- Form Card -->
            <div class="card-modern form-card">
                <form action="{{ route($panelPrefix . '.permissions.store') }}" method="POST" class="perm-form">
                    @csrf

                    <!-- Basic Information Section -->
                    <div class="form-section">
                        <h5 class="form-section-title">
                            <i class="fas fa-info-circle"></i> Permission Details
                        </h5>

                        <div class="row g-3">

                            <!-- Name -->
                            <div class="col-lg-6">
                                <label for="name" class="form-label">
                                    Permission Name <span class="required-star">*</span>
                                </label>
                                <input type="text" id="name" name="name"
                                    class="form-control @error('name') is-invalid @enderror"
                                    value="{{ old('name') }}" required placeholder="e.g., View Users">
                                <small class="text-muted d-block mt-1">The display name for this permission</small>
                                @error('name')
                                    <div class="invalid-feedback d-block">{{ $message }}</div>
                                @enderror
                            </div>

                            <!-- Group -->
                            <div class="col-lg-6">
                                <label for="group" class="form-label">
                                    Group <span class="required-star">*</span>
                                </label>
                                <input type="text" id="group" name="group"
                                    class="form-control @error('group') is-invalid @enderror"
                                    value="{{ old('group') }}" required placeholder="e.g., users">
                                <small class="text-muted d-block mt-1">Module this permission belongs to (e.g., users, roles)</small>
                                @error('group')
                                    <div class="invalid-feedback d-block">{{ $message }}</div>
                                @enderror
                            </div>

                            <!-- Description -->
                            <div class="col-12">
                                <label for="description" class="form-label">Description</label>
                                <textarea id="description" name="description" rows="3"
                                    class="form-control @error('description') is-invalid @enderror"
                                    placeholder="Optional: briefly describe what this permission allows">{{ old('description') }}</textarea>
                                @error('description')
                                    <div class="invalid-feedback d-block">{{ $message }}</div>
                                @enderror
                            </div>

                        </div>
                    </div>

                    <!-- Form Actions -->
                    <div class="form-actions">
                        <button type="submit" class="btn btn-primary">
                            <i class="fas fa-plus me-2"></i>Create Permission
                        </button>
                        <a href="{{ route($panelPrefix . '.permissions') }}" class="btn btn-secondary">
                            <i class="fas fa-times me-2"></i>Cancel
                        </a>
                    </div>

                </form>
            </div>

        </div>
    </main>
</div>
@endsection
