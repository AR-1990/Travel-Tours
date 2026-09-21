@extends('admin.layouts.main')

@section('title', isset($manager) ? 'Edit Sub-Agent' : 'Add Sub-Agent')

@section('content')
@php
    $user = auth()->user();
    $panelPrefix = $user && $user->user_type === 'tenant_admin' ? 'agent' : ($user && $user->user_type === 'sub_agent' ? 'subagent' : 'admin');
    $isEdit = isset($manager);
@endphp

<div class="container-fluid panel-page">
    @include('admin.partials.page-header', [
        'title' => $isEdit ? 'Edit sub-agent' : 'Add sub-agent',
        'subtitle' => $isEdit ? 'Update account details and permissions.' : 'Create a sub-agent account with role and permissions.',
        'icon' => 'fas fa-user-tie',
        'actions' => '<a href="'.e(route($panelPrefix.'.managers')).'" class="btn btn-light btn-sm"><i class="fas fa-arrow-left me-1"></i> Back</a>',
    ])

    @include('admin.partials.flash')

    <div class="panel-surface">
        <div class="card-body">
            <form method="POST" action="{{ $isEdit ? route($panelPrefix . '.managers.update', $manager->id) : route($panelPrefix . '.managers.store') }}" enctype="multipart/form-data"
                data-swal-confirm
                data-swal-title="{{ $isEdit ? 'Save sub-agent changes?' : 'Create this sub-agent?' }}"
                data-swal-text="{{ $isEdit ? 'Updates will apply immediately.' : 'A new sub-agent account will be created.' }}"
                data-swal-icon="question"
                data-swal-confirm-text="{{ $isEdit ? 'Yes, save' : 'Yes, create' }}"
                data-swal-confirm-color="#053750">
                @csrf
                @if($isEdit)
                    @method('PUT')
                @endif

                <div class="row g-3 mb-3">
                    <div class="col-12">
                        <label class="form-label">Category / Role <span class="text-danger">*</span></label>
                        <select class="form-select" name="role_id" required>
                            <option value="">Select role</option>
                            @foreach(($roles ?? []) as $role)
                                <option value="{{ data_get($role, 'id') }}" @selected((string) old('role_id', data_get($manager ?? null, 'role_id', '')) === (string) data_get($role, 'id'))>
                                    {{ data_get($role, 'name') }}{{ data_get($role, 'category') ? ' (' . ucfirst(str_replace('-', ' ', data_get($role, 'category'))) . ')' : '' }}
                                </option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-md-6">
                        <label for="first_name" class="form-label">First Name <span class="text-danger">*</span></label>
                        <input type="text" class="form-control" id="first_name" name="first_name"
                            value="{{ old('first_name', $manager->first_name ?? '') }}" required>
                    </div>
                    <div class="col-md-6">
                        <label for="last_name" class="form-label">Last Name <span class="text-danger">*</span></label>
                        <input type="text" class="form-control" id="last_name" name="last_name"
                            value="{{ old('last_name', $manager->last_name ?? '') }}" required>
                    </div>
                    <div class="col-md-6">
                        <label for="email" class="form-label">Email <span class="text-danger">*</span></label>
                        <input type="email" class="form-control" id="email" name="email"
                            value="{{ old('email', $manager->email ?? '') }}" required>
                    </div>
                    <div class="col-md-6">
                        <label for="username" class="form-label">Username</label>
                        <input type="text" class="form-control" id="username" name="username" pattern="[a-zA-Z0-9._-]+"
                            value="{{ old('username', $manager->username ?? '') }}" placeholder="Leave blank to auto-generate from email">
                    </div>
                    <div class="col-md-6">
                        <label for="phone" class="form-label">Mobile</label>
                        <input type="text" class="form-control" id="phone" name="phone" value="{{ old('phone', $manager->phone ?? '') }}">
                    </div>
                    <div class="col-md-6">
                        <label for="country" class="form-label">Country</label>
                        <input type="text" class="form-control" id="country" name="country" value="{{ old('country', $manager->country ?? '') }}">
                    </div>
                    <div class="col-md-6">
                        <label for="password" class="form-label">Password {!! ! $isEdit ? '<span class="text-danger">*</span>' : '<small class="text-muted">(leave blank to keep)</small>' !!}</label>
                        <input type="password" class="form-control" id="password" name="password"
                            {{ ! $isEdit ? 'required' : '' }} minlength="8">
                    </div>
                    <div class="col-md-6">
                        <label for="password_confirmation" class="form-label">Confirm password {!! ! $isEdit ? '<span class="text-danger">*</span>' : '' !!}</label>
                        <input type="password" class="form-control" id="password_confirmation" name="password_confirmation"
                            {{ ! $isEdit ? 'required' : '' }} minlength="8">
                    </div>
                    <div class="col-md-6">
                        <label class="form-label">Profile picture</label>
                        <input type="file" name="photo" class="form-control" accept=".jpg,.jpeg,.png,.webp">
                        @if($isEdit && $manager->photo)
                            <small class="text-muted">Current: <a href="{{ asset('storage/' . $manager->photo) }}" target="_blank">view</a></small>
                        @endif
                    </div>
                    <div class="col-md-6">
                        <label class="form-label">Agent document</label>
                        <input type="file" name="agent_document" class="form-control" accept=".pdf,.jpg,.jpeg,.png">
                        @if($isEdit && $manager->agent_document)
                            <small class="text-muted">Current: <a href="{{ asset('storage/' . $manager->agent_document) }}" target="_blank">view</a></small>
                        @endif
                    </div>
                </div>

                <hr class="my-4">
                <h5 class="mb-2">Permissions</h5>
                <p class="text-muted mb-4">Select permissions for this sub-agent, grouped by admin section.</p>

                @if(isset($permissions) && ! empty($permissions))
                    @foreach($permissions as $group => $groupPermissions)
                        @if($group !== 'managers' && $group !== 'dashboard')
                            <div class="border rounded p-3 mb-3" data-group="{{ $group }}">
                                <div class="d-flex justify-content-between align-items-center mb-3">
                                    <h6 class="mb-0 fw-semibold">{{ ucfirst(str_replace('-', '/', $group)) }}</h6>
                                    <div class="form-check">
                                        <input class="form-check-input select-all-group" type="checkbox"
                                            id="select_all_{{ $group }}" data-group="{{ $group }}">
                                        <label class="form-check-label fw-semibold" for="select_all_{{ $group }}">Select all</label>
                                    </div>
                                </div>
                                <div class="row">
                                    @foreach($groupPermissions as $permission)
                                        <div class="col-md-4 mb-2">
                                            <div class="form-check">
                                                <input class="form-check-input permission-checkbox" type="checkbox"
                                                    name="permissions[]" value="{{ $permission->id }}"
                                                    id="permission_{{ $permission->id }}" data-group="{{ $group }}"
                                                    @checked((isset($managerPermissions) && in_array($permission->id, $managerPermissions)) || in_array($permission->id, old('permissions', [])))>
                                                <label class="form-check-label" for="permission_{{ $permission->id }}">
                                                    {{ $permission->name }}
                                                </label>
                                            </div>
                                        </div>
                                    @endforeach
                                </div>
                            </div>
                        @endif
                    @endforeach
                @else
                    <div class="alert alert-warning mb-0">No permissions available. Please run the permissions seeder.</div>
                @endif

                <div class="d-flex flex-wrap gap-2 mt-4">
                    <button type="submit" class="btn btn-primary">{{ $isEdit ? 'Update' : 'Create' }}</button>
                    <a href="{{ route($panelPrefix . '.managers') }}" class="btn btn-outline-secondary">Cancel</a>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
    $(function () {
        $('.select-all-group').on('change', function () {
            const group = $(this).data('group');
            $(`.permission-checkbox[data-group="${group}"]`).prop('checked', $(this).is(':checked'));
        });

        $('.permission-checkbox').on('change', function () {
            const group = $(this).data('group');
            const total = $(`.permission-checkbox[data-group="${group}"]`).length;
            const checked = $(`.permission-checkbox[data-group="${group}"]:checked`).length;
            $(`.select-all-group[data-group="${group}"]`).prop('checked', total > 0 && checked === total);
        });

        $('.select-all-group').each(function () {
            const group = $(this).data('group');
            const total = $(`.permission-checkbox[data-group="${group}"]`).length;
            const checked = $(`.permission-checkbox[data-group="${group}"]:checked`).length;
            if (total > 0 && checked === total) {
                $(this).prop('checked', true);
            }
        });
    });
</script>
@endpush
