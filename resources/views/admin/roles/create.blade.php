@extends('admin.layouts.main')

@section('title', 'Create Role')

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
                        <span class="title-icon">🛡️</span> Create Role
                    </h1>
                    <p class="dashboard-subtitle">Add a new role and assign permissions</p>
                </div>
                <a href="{{ route($panelPrefix . '.roles') }}" class="btn btn-secondary">
                    <i class="fas fa-arrow-left me-2"></i>Back to Roles
                </a>
            </div>

            <!-- Form Card -->
            <div class="card-modern">
                <form action="{{ route($panelPrefix . '.roles.store') }}" method="POST" class="role-form">
                    @csrf

                    <!-- Role Details Section -->
                    <div class="form-section">
                        <h5 class="form-section-title">
                            <i class="fas fa-info-circle"></i> Role Details
                        </h5>

                        <div class="row g-3">
                            <!-- Name -->
                            <div class="col-lg-6">
                                <label for="name" class="form-label">
                                    Role Name <span class="required-star">*</span>
                                </label>
                                <input type="text" id="name" name="name"
                                    class="form-control @error('name') is-invalid @enderror"
                                    value="{{ old('name') }}" required placeholder="e.g., Manager">
                                <small class="text-muted d-block mt-1">A unique, descriptive name for this role</small>
                                @error('name')
                                    <div class="invalid-feedback d-block">{{ $message }}</div>
                                @enderror
                            </div>

                            <!-- Description -->
                            <div class="col-lg-6">
                                <label for="description" class="form-label">Description</label>
                                <input type="text" id="description" name="description"
                                    class="form-control @error('description') is-invalid @enderror"
                                    value="{{ old('description') }}" placeholder="Brief description of this role">
                                <small class="text-muted d-block mt-1">Optional: what this role is responsible for</small>
                                @error('description')
                                    <div class="invalid-feedback d-block">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>
                    </div>

                    <!-- Permissions Section -->
                    <div class="form-section">
                        <div class="perm-section-header">
                            <h5 class="form-section-title mb-0">
                                <i class="fas fa-key"></i> Assign Permissions
                            </h5>
                            <div class="perm-bulk-actions">
                                <button type="button" class="btn-bulk" id="selectAll">
                                    <i class="fas fa-check-double me-1"></i>Select All
                                </button>
                                <button type="button" class="btn-bulk btn-bulk--clear" id="clearAll">
                                    <i class="fas fa-times me-1"></i>Clear All
                                </button>
                            </div>
                        </div>
                        <p class="perm-hint">Choose which permissions this role should have access to.</p>

                        <div class="perm-groups-wrapper">
                            @foreach($permissions as $group => $groupPermissions)
                                <div class="perm-group-block">
                                    <!-- Group Header -->
                                    <div class="perm-group-label">
                                        <label class="perm-group-check">
                                            <input type="checkbox" class="group-toggle" data-group="{{ $loop->index }}">
                                            <span class="perm-group-name">
                                                <i class="fas fa-layer-group"></i>
                                                {{ ucfirst(str_replace('-', ' ', $group)) }}
                                            </span>
                                        </label>
                                        <span class="perm-group-badge">{{ count($groupPermissions) }}</span>
                                    </div>

                                    <!-- Permissions Grid -->
                                    <div class="perm-grid" data-group-index="{{ $loop->index }}">
                                        @foreach($groupPermissions as $permission)
                                            <label class="perm-item" for="perm_{{ $permission->id }}">
                                                <input type="checkbox" class="perm-checkbox perm-group-{{ $loop->parent->index }}"
                                                    name="permissions[]" value="{{ $permission->id }}"
                                                    id="perm_{{ $permission->id }}"
                                                    {{ in_array($permission->id, old('permissions', [])) ? 'checked' : '' }}>
                                                <span class="perm-item-content">
                                                    <span class="perm-item-name">{{ $permission->name }}</span>
                                                    @if($permission->description)
                                                        <span class="perm-item-desc">{{ $permission->description }}</span>
                                                    @endif
                                                </span>
                                                <span class="perm-item-check"><i class="fas fa-check"></i></span>
                                            </label>
                                        @endforeach
                                    </div>
                                </div>
                            @endforeach
                        </div>
                    </div>

                    <!-- Form Actions -->
                    <div class="form-actions">
                        <button type="submit" class="btn btn-primary">
                            <i class="fas fa-plus me-2"></i>Create Role
                        </button>
                        <a href="{{ route($panelPrefix . '.roles') }}" class="btn btn-secondary">
                            <i class="fas fa-times me-2"></i>Cancel
                        </a>
                    </div>

                </form>
            </div>

        </div>
    </main>
</div>
@endsection

@section('scripts')
<script>
    // Select / Clear All
    document.getElementById('selectAll').addEventListener('click', function () {
        document.querySelectorAll('.perm-checkbox, .group-toggle').forEach(cb => cb.checked = true);
    });

    document.getElementById('clearAll').addEventListener('click', function () {
        document.querySelectorAll('.perm-checkbox, .group-toggle').forEach(cb => cb.checked = false);
    });

    // Group toggle — check/uncheck all in group
    document.querySelectorAll('.group-toggle').forEach(function (toggle) {
        toggle.addEventListener('change', function () {
            const groupIndex = this.dataset.group;
            document.querySelectorAll('.perm-group-' + groupIndex).forEach(cb => cb.checked = this.checked);
        });
    });

    // Auto-update group toggle state when individual items change
    document.querySelectorAll('.perm-checkbox').forEach(function (cb) {
        cb.addEventListener('change', function () {
            const classes = [...this.classList];
            const groupClass = classes.find(c => c.startsWith('perm-group-') && c !== 'perm-checkbox');
            if (!groupClass) return;
            const groupIndex = groupClass.replace('perm-group-', '');
            const all = document.querySelectorAll('.perm-group-' + groupIndex);
            const checked = document.querySelectorAll('.perm-group-' + groupIndex + ':checked');
            const toggle = document.querySelector('.group-toggle[data-group="' + groupIndex + '"]');
            if (toggle) toggle.checked = all.length === checked.length;
        });
    });
</script>
@endsection
