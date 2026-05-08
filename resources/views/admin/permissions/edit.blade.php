@extends('admin.layouts.main')

@section('title', 'Edit Permission')

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
                        <span class="title-icon">✏️</span> Edit Permission
                    </h1>
                    <p class="dashboard-subtitle">Update permission information</p>
                </div>
                <a href="{{ route($panelPrefix . '.permissions') }}" class="btn btn-secondary">
                    <i class="fas fa-arrow-left me-2"></i>Back to Permissions
                </a>
            </div>

            <!-- Form Card -->
            <div class="card-modern form-card">
                <form action="{{ route($panelPrefix . '.permissions.update', $permission->id) }}" method="POST" class="perm-form">
                    @csrf
                    @method('PUT')

                    <!-- Permission Details Section -->
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
                                    value="{{ old('name', $permission->name) }}" required
                                    placeholder="e.g., View Users">
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
                                    value="{{ old('group', $permission->group) }}" required
                                    placeholder="e.g., users">
                                <small class="text-muted d-block mt-1">Module this permission belongs to</small>
                                @error('group')
                                    <div class="invalid-feedback d-block">{{ $message }}</div>
                                @enderror
                            </div>

                            <!-- Description -->
                            <div class="col-12">
                                <label for="description" class="form-label">Description</label>
                                <textarea id="description" name="description" rows="3"
                                    class="form-control @error('description') is-invalid @enderror"
                                    placeholder="Optional: briefly describe what this permission allows">{{ old('description', $permission->description) }}</textarea>
                                @error('description')
                                    <div class="invalid-feedback d-block">{{ $message }}</div>
                                @enderror
                            </div>

                        </div>
                    </div>

                    <!-- Slug Section -->
                    <div class="form-section">
                        <h5 class="form-section-title">
                            <i class="fas fa-tag"></i> Identifier
                        </h5>

                        <div class="row g-3">
                            <div class="col-lg-6">
                                <label class="form-label">Slug</label>
                                <div class="slug-display">
                                    <span class="slug-icon"><i class="fas fa-lock"></i></span>
                                    <span class="slug-value">{{ $permission->slug }}</span>
                                </div>
                                <small class="text-muted d-block mt-1">Auto-generated from the name — read only</small>
                            </div>
                        </div>
                    </div>

                    <!-- Form Actions -->
                    <div class="form-actions">
                        <button type="submit" class="btn btn-primary">
                            <i class="fas fa-save me-2"></i>Save Changes
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
