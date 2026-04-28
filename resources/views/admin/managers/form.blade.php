@extends('admin.layouts.main')
<style>
@import url('https://fonts.googleapis.com/css2?family=Bebas+Neue&family=Inter:ital,opsz,wght@0,14..32,100..900;1,14..32,100..900&family=Poppins:ital,wght@0,100;0,200;0,300;0,400;0,500;0,600;0,700;0,800;0,900;1,100;1,200;1,300;1,400;1,500;1,600;1,700;1,800;1,900&display=swap');
</style>
<style>
    body{
        background: #fafbff !important;
        font-family: "Inter" !important;
    }
    .card-header:first-child {
        background: #fafbff !important;
        border: none;
    }
    .card-body {
        background: #fafbff !important;
    }
    .card-title {
        border-radius: 0;
        color: #333 !important;
        margin: 0;
        font-size: 28px;
        font-family: "Inter";
        font-weight: 500;
    }
    .form-label {
        font-family: "Inter";
        font-weight: 500;
        color: #333;
        margin-bottom: 8px;
    }
    .form-control, .form-select {
        font-family: "Inter";
        border: 1px solid #E9EBF0;
        border-radius: 10px;
        padding: 12px 15px;
    }
    .permission-group {
        margin-bottom: 30px;
        padding: 20px;
        background: white;
        border-radius: 10px;
        border: 1px solid #E9EBF0;
    }
    .permission-group h5 {
        font-family: "Inter";
        font-weight: 600;
        color: #333;
        margin-bottom: 15px;
        padding-bottom: 10px;
        /* border-bottom: 2px solid #E9EBF0; */
    }
    .permission-item {
        margin-bottom: 10px;
    }
    .permission-item label {
        font-family: "Inter";
        font-weight: 400;
        color: #666;
        margin-left: 8px;
        cursor: pointer;
    }
    .form-check-input {
        cursor: pointer;
    }
 
    .btn {
        border-radius: 9.77px !important;
        padding: 15px 56px !important;
        font-family: "Poppins", sans-serif !important;
        font-weight: 500 !important;
        font-size: 22px !important;
        line-height: 100% !important;
        letter-spacing: 0px !important;
        text-align: center !important;
    }

    .btn:hover{
        color: #000;
    }

    .card .card-header .card-title a img {
        width: 14px !important;
        margin-top: -6px;
        margin-right: 16px;
        border: none !important;
    }

    .form-check-input:checked {
        background-color: #273572 !important;
        border-color: #273572 !important;
    }

    @media (max-width: 768px) {
        .row.mt-4 .col-12 {
            display: flex;
            gap: 10px;
            flex-direction: column;
        }

        .btn {
            padding: 15px 40px;
            font-size: 16px !important;
        }
    }
</style>
@section('content')
<main class="main-content">
    <div class="container">
        <div class="row">
            <div class="col-12">
                <div class="card" style="border: none;">
                    <div class="card-header">
                        <!-- <h4 class="card-title">{{ isset($manager) ? 'Edit Manager/Editor' : 'Add Manager/Editor' }}</h4> -->
                        <h4 class="card-title">
                                <a href="{{ url('/admin/managers') }}"><img src=" {{ asset('assets/images/dashboard/dashboardBackChevron.svg') }}" alt=""></a>
                                    {{ isset($manager) ? 'Edit Manager/Editor' : 'Add Manager/Editor' }}
                            </h4>
                    </div>
                    <div class="card-body">
                        @if(session('success'))
                            <div class="alert alert-success alert-dismissible fade show" role="alert">
                                {{ session('success') }}
                                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                            </div>
                        @endif

                        @if ($errors->any())
                            <div class="alert alert-danger">
                                <ul class="mb-0">
                                    @foreach ($errors->all() as $error)
                                        <li>{{ $error }}</li>
                                    @endforeach
                                </ul>
                            </div>
                        @endif

                        <form method="POST" action="{{ isset($manager) ? route('admin.update.manager', $manager->id) : route('admin.create.manager') }}">
                            @csrf
                            @if(isset($manager))
                                @method('PUT')
                            @endif

                            <div class="row mb-4">
                                <div class="col-md-12">
                                    <label class="form-label">Role <span class="text-danger">*</span></label>
                                    <div class="d-flex gap-4">
                                        <div class="form-check">
                                            <input class="form-check-input" type="radio" name="role_id" id="role_manager" value="2" 
                                                {{ (isset($manager) && $manager->role_id == 2) || old('role_id') == 2 ? 'checked' : '' }} required>
                                            <label class="form-check-label" for="role_manager">
                                                Manager
                                            </label>
                                        </div>
                                        <div class="form-check">
                                            <input class="form-check-input" type="radio" name="role_id" id="role_editor" value="3"
                                                {{ (isset($manager) && $manager->role_id == 3) || old('role_id') == 3 ? 'checked' : '' }}>
                                            <label class="form-check-label" for="role_editor">
                                                Editor
                                            </label>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <div class="row mb-3">
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
                            </div>

                            <div class="row mb-3">
                                <div class="col-md-6">
                                    <label for="email" class="form-label">Email <span class="text-danger">*</span></label>
                                    <input type="email" class="form-control" id="email" name="email" 
                                        value="{{ old('email', $manager->email ?? '') }}" required>
                                </div>
                                <div class="col-md-6">
                                    <label for="password" class="form-label">Password {!! !isset($manager) ? '<span class="text-danger">*</span>' : '<small class="text-muted">(Leave blank to keep current)</small>' !!}</label>
                                    <input type="password" class="form-control" id="password" name="password" 
                                        {{ !isset($manager) ? 'required' : '' }} minlength="8">
                                    @if(isset($manager))
                                        <small class="text-muted">Leave blank if you don't want to change the password</small>
                                    @endif
                                </div>
                            </div>

                            <hr class="my-4">

                            <h5 class="mb-4" style="font-family: 'Inter'; font-weight: 600; color: #333;">Permissions</h5>
                            <p class="text-muted mb-4">Select the permissions for this Manager/Editor. Permissions are grouped by admin sections.</p>

                            @if(isset($permissions) && !empty($permissions))
                                @foreach($permissions as $group => $groupPermissions)
                                @if($group !== 'managers' && $group !== 'dashboard') {{-- Don't show Managers and Dashboard tab permissions --}}
                                <div class="permission-group" data-group="{{ $group }}">
                                    <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 15px;">
                                        <h5 style="margin: 0;">{{ ucfirst(str_replace('-', '/', $group)) }}</h5>
                                        <div class="form-check">
                                            <input class="form-check-input select-all-group" 
                                                   type="checkbox" 
                                                   id="select_all_{{ $group }}"
                                                   data-group="{{ $group }}">
                                            <label class="form-check-label" for="select_all_{{ $group }}" style="font-weight: 600; cursor: pointer;">
                                                Select All
                                            </label>
                                        </div>
                                    </div>
                                    <div class="row">
                                        @foreach($groupPermissions as $permission)
                                            <div class="col-md-4 permission-item">
                                                <div class="form-check">
                                                    <input class="form-check-input permission-checkbox" 
                                                           type="checkbox" 
                                                           name="permissions[]" 
                                                           value="{{ $permission->id }}" 
                                                           id="permission_{{ $permission->id }}"
                                                           data-group="{{ $group }}"
                                                           {{ (isset($managerPermissions) && in_array($permission->id, $managerPermissions)) || in_array($permission->id, old('permissions', [])) ? 'checked' : '' }}>
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
                                <div class="alert alert-warning">
                                    <p class="mb-0">No permissions available. Please run the permissions seeder.</p>
                                </div>
                            @endif

                            <div class="row mt-4">
                                <div class="col-12">
                                    <button type="submit" class="btn btn-primary">{{ isset($manager) ? 'Update' : 'Create' }}</button>
                                    <a href="{{ route('admin.managers') }}" class="btn btn-secondary">Cancel</a>
                                </div>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>
</main>
@endsection

@section('scripts')
<script src="https://code.jquery.com/jquery-3.7.0.js"></script>
<script>
    $(document).ready(function() {
        // Handle "Select All" checkbox for each permission group
        $('.select-all-group').on('change', function() {
            const group = $(this).data('group');
            const isChecked = $(this).is(':checked');
            
            // Select/deselect all checkboxes in this group
            $(`.permission-checkbox[data-group="${group}"]`).prop('checked', isChecked);
        });

        // Handle individual permission checkbox changes
        $('.permission-checkbox').on('change', function() {
            const group = $(this).data('group');
            const totalCheckboxes = $(`.permission-checkbox[data-group="${group}"]`).length;
            const checkedCheckboxes = $(`.permission-checkbox[data-group="${group}"]:checked`).length;
            
            // Update "Select All" checkbox state
            const selectAllCheckbox = $(`.select-all-group[data-group="${group}"]`);
            if (checkedCheckboxes === totalCheckboxes) {
                selectAllCheckbox.prop('checked', true);
            } else {
                selectAllCheckbox.prop('checked', false);
            }
        });

        // Initialize "Select All" checkboxes state on page load
        $('.select-all-group').each(function() {
            const group = $(this).data('group');
            const totalCheckboxes = $(`.permission-checkbox[data-group="${group}"]`).length;
            const checkedCheckboxes = $(`.permission-checkbox[data-group="${group}"]:checked`).length;
            
            if (checkedCheckboxes === totalCheckboxes && totalCheckboxes > 0) {
                $(this).prop('checked', true);
            }
        });
    });
</script>
@endsection

