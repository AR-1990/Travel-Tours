@extends('admin.layouts.main')

@section('title', 'Edit Role')

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
                        <span class="title-icon">✏️</span> Edit Role
                    </h1>
                    <p class="dashboard-subtitle">Update role information and permissions</p>
                </div>
                <a href="{{ route($panelPrefix . '.roles') }}" class="btn btn-secondary">
                    <i class="fas fa-arrow-left me-2"></i>Back to Roles
                </a>
            </div>

            <!-- Form Card -->
            <div class="card-modern">
                <form action="{{ route($panelPrefix . '.roles.update', $role->id) }}" method="POST" class="role-form">
                    @csrf
                    @method('PUT')

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
                                    value="{{ old('name', $role->name) }}" required
                                    placeholder="e.g., Manager">
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
                                    value="{{ old('description', $role->description) }}"
                                    placeholder="Brief description of this role">
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
                                @php
                                    $groupIndex = $loop->index;
                                    $groupCheckedCount = $groupPermissions->filter(fn($p) => $role->permissions->contains($p->id))->count();
                                    $groupAllChecked = $groupCheckedCount === count($groupPermissions);
                                @endphp
                                <div class="perm-group-block">

                                    <!-- Group Header -->
                                    <div class="perm-group-label">
                                        <label class="perm-group-check">
                                            <input type="checkbox" class="group-toggle"
                                                data-group="{{ $groupIndex }}"
                                                {{ $groupAllChecked ? 'checked' : '' }}>
                                            <span class="perm-group-name">
                                                <i class="fas fa-layer-group"></i>
                                                {{ ucfirst(str_replace('-', ' ', $group)) }}
                                            </span>
                                        </label>
                                        <span class="perm-group-badge">{{ count($groupPermissions) }}</span>
                                    </div>

                                    <!-- Permissions Grid -->
                                    <div class="perm-grid" data-group-index="{{ $groupIndex }}">
                                        @foreach($groupPermissions as $permission)
                                            <label class="perm-item" for="perm_{{ $permission->id }}">
                                                <input type="checkbox"
                                                    class="perm-checkbox perm-group-{{ $groupIndex }}"
                                                    name="permissions[]"
                                                    value="{{ $permission->id }}"
                                                    id="perm_{{ $permission->id }}"
                                                    {{ $role->permissions->contains($permission->id) || in_array($permission->id, old('permissions', [])) ? 'checked' : '' }}>
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
                            <i class="fas fa-save me-2"></i>Save Changes
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
    // Select All / Clear All
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

    // Auto-update group toggle when individual items change
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

<style>
    /* ==================== EDIT ROLE PAGE STYLES ==================== */

    /* Page Header */
    .page-header {
        display: flex;
        justify-content: space-between;
        align-items: flex-start;
        gap: 2rem;
    }

    .page-header > div:first-child {
        flex: 1;
    }

    /* Buttons */
    .btn-primary {
        background: var(--primary-gradient);
        color: white;
        border: none;
        padding: 0.75rem 1.75rem;
        border-radius: 0.75rem;
        font-weight: 600;
        transition: var(--transition);
        display: inline-flex;
        align-items: center;
        white-space: nowrap;
        text-decoration: none;
        cursor: pointer;
    }

    .btn-primary:hover {
        transform: translateY(-2px);
        box-shadow: 0 10px 20px rgba(102, 126, 234, 0.3);
        color: white;
    }

    .btn-secondary {
        background: linear-gradient(135deg, rgba(107, 114, 128, 0.2), rgba(75, 85, 99, 0.2));
        color: #6b7280;
        border: 1px solid rgba(107, 114, 128, 0.3);
        padding: 0.625rem 1.25rem;
        border-radius: 0.75rem;
        font-weight: 600;
        transition: var(--transition);
        display: inline-flex;
        align-items: center;
        white-space: nowrap;
        text-decoration: none;
    }

    .btn-secondary:hover {
        background: linear-gradient(135deg, #6b7280, #4b5563);
        color: white;
        border-color: #6b7280;
    }

    html.dark-mode .btn-secondary {
        background: linear-gradient(135deg, rgba(107, 114, 128, 0.3), rgba(75, 85, 99, 0.3));
        color: #9ca3af;
    }

    html.dark-mode .btn-secondary:hover {
        background: linear-gradient(135deg, #6b7280, #4b5563);
        color: white;
    }

    /* Role Form */
    .role-form {
        padding: 0;
    }

    /* Form Sections */
    .form-section {
        margin-bottom: 2.5rem;
        padding-bottom: 2rem;
        border-bottom: 2px solid var(--border-color);
    }

    .form-section:last-of-type {
        border-bottom: none;
        margin-bottom: 0;
    }

    .form-section-title {
        font-family: 'Poppins', sans-serif;
        font-weight: 600;
        color: var(--text-primary);
        font-size: 0.95rem;
        text-transform: uppercase;
        letter-spacing: 0.5px;
        margin-bottom: 1.5rem;
        display: flex;
        align-items: center;
        gap: 0.75rem;
    }

    .form-section-title i {
        color: var(--primary-color);
        font-size: 1.1rem;
    }

    /* Form Labels & Controls */
    .form-label {
        font-weight: 600;
        color: var(--text-primary);
        font-size: 0.9rem;
        margin-bottom: 0.625rem;
        display: block;
    }

    .required-star {
        color: #ef4444;
        margin-left: 0.1rem;
    }

    .form-control {
        border: 1px solid var(--border-color);
        border-radius: 0.75rem;
        padding: 0.75rem 1rem;
        transition: var(--transition);
        background: var(--bg-primary);
        color: var(--text-primary);
        font-size: 0.95rem;
        font-family: inherit;
        width: 100%;
    }

    .form-control:focus {
        border-color: var(--primary-color);
        box-shadow: 0 0 0 3px rgba(102, 126, 234, 0.1);
        outline: none;
        background: var(--bg-primary);
        color: var(--text-primary);
    }

    .form-control::placeholder {
        color: var(--text-secondary);
        opacity: 0.7;
    }

    .form-control.is-invalid {
        border-color: #ef4444;
    }

    .form-control.is-invalid:focus {
        box-shadow: 0 0 0 3px rgba(239, 68, 68, 0.1);
    }

    html.dark-mode .form-control {
        background: var(--bg-secondary);
        color: var(--text-primary);
    }

    html.dark-mode .form-control:focus {
        background: var(--bg-secondary);
        color: var(--text-primary);
    }

    /* Help Text */
    small.text-muted {
        color: var(--text-secondary);
        font-size: 0.82rem;
    }

    /* Invalid Feedback */
    .invalid-feedback {
        color: #ef4444;
        font-size: 0.85rem;
        margin-top: 0.375rem;
    }

    /* ==================== PERMISSIONS SECTION ==================== */

    .perm-section-header {
        display: flex;
        align-items: center;
        justify-content: space-between;
        gap: 1rem;
        margin-bottom: 0.5rem;
    }

    .perm-hint {
        font-size: 0.875rem;
        color: var(--text-secondary);
        margin-bottom: 1.5rem;
    }

    .perm-bulk-actions {
        display: flex;
        gap: 0.5rem;
        flex-shrink: 0;
    }

    .btn-bulk {
        font-size: 0.8rem;
        font-weight: 600;
        padding: 0.35rem 0.875rem;
        border-radius: 0.5rem;
        border: 1px solid rgba(102, 126, 234, 0.25);
        background: linear-gradient(135deg, rgba(102, 126, 234, 0.08), rgba(118, 75, 162, 0.08));
        color: var(--primary-color);
        cursor: pointer;
        transition: var(--transition);
        display: inline-flex;
        align-items: center;
    }

    .btn-bulk:hover {
        background: var(--primary-gradient);
        color: white;
        border-color: transparent;
    }

    .btn-bulk--clear {
        border-color: rgba(239, 68, 68, 0.2);
        background: linear-gradient(135deg, rgba(239, 68, 68, 0.07), rgba(220, 38, 38, 0.05));
        color: #dc2626;
    }

    .btn-bulk--clear:hover {
        background: linear-gradient(135deg, #ef4444, #dc2626);
        color: white;
        border-color: transparent;
    }

    html.dark-mode .btn-bulk {
        background: linear-gradient(135deg, rgba(102, 126, 234, 0.15), rgba(118, 75, 162, 0.15));
        color: #a5b4fc;
        border-color: rgba(102, 126, 234, 0.3);
    }

    html.dark-mode .btn-bulk--clear {
        background: linear-gradient(135deg, rgba(239, 68, 68, 0.15), rgba(220, 38, 38, 0.12));
        color: #fca5a5;
        border-color: rgba(239, 68, 68, 0.25);
    }

    /* Permission Groups Wrapper */
    .perm-groups-wrapper {
        display: flex;
        flex-direction: column;
        gap: 1rem;
        max-height: 520px;
        overflow-y: auto;
        padding-right: 0.25rem;
        scrollbar-width: thin;
        scrollbar-color: var(--primary-color) transparent;
    }

    .perm-groups-wrapper::-webkit-scrollbar {
        width: 4px;
    }

    .perm-groups-wrapper::-webkit-scrollbar-track {
        background: transparent;
    }

    .perm-groups-wrapper::-webkit-scrollbar-thumb {
        background: rgba(102, 126, 234, 0.4);
        border-radius: 4px;
    }

    /* Permission Group Block */
    .perm-group-block {
        border: 1px solid var(--border-color);
        border-radius: 0.875rem;
        overflow: hidden;
        transition: var(--transition);
    }

    .perm-group-block:hover {
        border-color: rgba(102, 126, 234, 0.3);
        box-shadow: 0 4px 16px rgba(102, 126, 234, 0.08);
    }

    /* Group Label Row */
    .perm-group-label {
        display: flex;
        align-items: center;
        justify-content: space-between;
        padding: 0.875rem 1.125rem;
        background: linear-gradient(135deg, rgba(102, 126, 234, 0.05), rgba(118, 75, 162, 0.05));
        border-bottom: 1px solid var(--border-color);
        cursor: pointer;
    }

    .perm-group-check {
        display: flex;
        align-items: center;
        gap: 0.625rem;
        cursor: pointer;
        margin: 0;
        flex: 1;
    }

    .perm-group-check input[type="checkbox"] {
        width: 1rem;
        height: 1rem;
        accent-color: var(--primary-color);
        cursor: pointer;
        flex-shrink: 0;
    }

    .perm-group-name {
        font-family: 'Poppins', sans-serif;
        font-weight: 700;
        font-size: 0.88rem;
        color: var(--text-primary);
        text-transform: capitalize;
        display: flex;
        align-items: center;
        gap: 0.5rem;
    }

    .perm-group-name i {
        color: var(--primary-color);
        font-size: 0.8rem;
    }

    .perm-group-badge {
        font-size: 0.72rem;
        font-weight: 700;
        color: var(--primary-color);
        background: linear-gradient(135deg, rgba(102, 126, 234, 0.12), rgba(118, 75, 162, 0.12));
        padding: 0.15rem 0.55rem;
        border-radius: 999px;
        flex-shrink: 0;
    }

    html.dark-mode .perm-group-label {
        background: linear-gradient(135deg, rgba(102, 126, 234, 0.1), rgba(118, 75, 162, 0.1));
        border-bottom-color: var(--border-color);
    }

    /* Permissions Grid */
    .perm-grid {
        display: grid;
        grid-template-columns: repeat(auto-fill, minmax(220px, 1fr));
        gap: 0.5rem;
        padding: 1rem 1.125rem;
        background: var(--bg-primary);
    }

    html.dark-mode .perm-grid {
        background: var(--bg-secondary);
    }

    /* Individual Permission Item */
    .perm-item {
        display: flex;
        align-items: flex-start;
        gap: 0.625rem;
        padding: 0.625rem 0.75rem;
        border: 1px solid transparent;
        border-radius: 0.625rem;
        cursor: pointer;
        transition: var(--transition);
        position: relative;
    }

    .perm-item:hover {
        background: linear-gradient(135deg, rgba(102, 126, 234, 0.05), rgba(118, 75, 162, 0.05));
        border-color: rgba(102, 126, 234, 0.15);
    }

    .perm-item:has(.perm-checkbox:checked) {
        background: linear-gradient(135deg, rgba(102, 126, 234, 0.08), rgba(118, 75, 162, 0.08));
        border-color: rgba(102, 126, 234, 0.25);
    }

    .perm-checkbox {
        width: 1rem;
        height: 1rem;
        accent-color: var(--primary-color);
        cursor: pointer;
        flex-shrink: 0;
        margin-top: 0.1rem;
    }

    .perm-item-content {
        flex: 1;
        min-width: 0;
    }

    .perm-item-name {
        display: block;
        font-size: 0.875rem;
        font-weight: 600;
        color: var(--text-primary);
        line-height: 1.3;
    }

    .perm-item-desc {
        display: block;
        font-size: 0.775rem;
        color: var(--text-secondary);
        margin-top: 0.15rem;
        line-height: 1.4;
    }

    .perm-item-check {
        display: none;
        width: 1.25rem;
        height: 1.25rem;
        border-radius: 50%;
        background: var(--primary-gradient);
        align-items: center;
        justify-content: center;
        flex-shrink: 0;
    }

    .perm-item-check i {
        font-size: 0.6rem;
        color: white;
    }

    .perm-item:has(.perm-checkbox:checked) .perm-item-check {
        display: flex;
    }

    html.dark-mode .perm-item:has(.perm-checkbox:checked) {
        background: linear-gradient(135deg, rgba(102, 126, 234, 0.15), rgba(118, 75, 162, 0.15));
        border-color: rgba(102, 126, 234, 0.3);
    }

    /* Form Actions */
    .form-actions {
        display: flex;
        gap: 1rem;
        margin-top: 2.5rem;
        padding-top: 2rem;
        border-top: 2px solid var(--border-color);
    }

    /* ==================== RESPONSIVE ==================== */

    @media (max-width: 1024px) {
        .page-header {
            flex-direction: column;
            gap: 1rem;
        }

        .perm-section-header {
            flex-direction: column;
            align-items: flex-start;
        }
    }

    @media (max-width: 768px) {
        .form-label {
            font-size: 0.88rem;
        }

        .form-control {
            padding: 0.625rem 0.875rem;
            font-size: 0.9rem;
        }

        .perm-grid {
            grid-template-columns: 1fr;
        }

        .perm-bulk-actions {
            width: 100%;
        }

        .btn-bulk {
            flex: 1;
            justify-content: center;
        }

        .form-actions {
            flex-direction: column;
            gap: 0.75rem;
        }

        .form-actions .btn-primary,
        .form-actions .btn-secondary {
            width: 100%;
            justify-content: center;
        }
    }

    @media (max-width: 576px) {
        .dashboard-title {
            font-size: 1.5rem;
        }

        .form-section-title {
            font-size: 0.88rem;
        }

        .perm-groups-wrapper {
            max-height: 400px;
        }
    }

    /* ==================== DARK MODE ==================== */

    html.dark-mode .form-section {
        border-bottom-color: var(--border-color);
    }

    html.dark-mode .form-actions {
        border-top-color: var(--border-color);
    }

    html.dark-mode .perm-group-block {
        border-color: var(--border-color);
    }
</style>