@extends('admin.layouts.main')

@section('title', 'Edit User')

@section('content')
<div class="container-fluid panel-page">
    @include('admin.partials.page-header', [
        'title' => 'Edit user',
        'subtitle' => 'Display name vs login: user signs in with email or username.',
        'icon' => 'fas fa-user-edit',
        'actions' => '<a href="'.e(route('admin.users.show', $targetUser->id)).'" class="btn btn-outline-secondary btn-sm"><i class="fas fa-eye me-1"></i> View</a>'
            .'<a href="'.e(route('admin.users')).'" class="btn btn-light btn-sm"><i class="fas fa-arrow-left me-1"></i> Back</a>',
    ])

    @include('admin.partials.flash')

    <div class="panel-surface">
        <div class="card-body">
            <form action="{{ route('admin.users.update', $targetUser->id) }}" method="POST" enctype="multipart/form-data"
                data-swal-confirm
                data-swal-title="Save changes?"
                data-swal-text="User details will be updated."
                data-swal-icon="question"
                data-swal-confirm-text="Yes, save"
                data-swal-confirm-color="#053750">
                @csrf
                @method('PUT')

                <div class="row g-3 mb-3">
                    <div class="col-md-6">
                        <label class="form-label">First name <span class="text-danger">*</span></label>
                        <input type="text" name="first_name" class="form-control @error('first_name') is-invalid @enderror" value="{{ old('first_name', $targetUser->first_name) }}" required>
                        @error('first_name')<div class="invalid-feedback">{{ $message }}</div>@enderror
                    </div>
                    <div class="col-md-6">
                        <label class="form-label">Last name <span class="text-danger">*</span></label>
                        <input type="text" name="last_name" class="form-control @error('last_name') is-invalid @enderror" value="{{ old('last_name', $targetUser->last_name) }}" required>
                        @error('last_name')<div class="invalid-feedback">{{ $message }}</div>@enderror
                    </div>
                    <div class="col-md-6">
                        <label class="form-label">Email <span class="text-danger">*</span></label>
                        <input type="email" name="email" class="form-control @error('email') is-invalid @enderror" value="{{ old('email', $targetUser->email) }}" required>
                        @error('email')<div class="invalid-feedback">{{ $message }}</div>@enderror
                    </div>
                    <div class="col-md-6">
                        <label class="form-label">Username <span class="text-danger">*</span></label>
                        <input type="text" name="username" class="form-control @error('username') is-invalid @enderror" value="{{ old('username', $targetUser->username) }}" required pattern="[a-zA-Z0-9._-]+">
                        @error('username')<div class="invalid-feedback">{{ $message }}</div>@enderror
                    </div>
                    <div class="col-md-6">
                        <label class="form-label">User type <span class="text-danger">*</span></label>
                        <select name="user_type" class="form-select @error('user_type') is-invalid @enderror" required>
                            @foreach($userTypes as $value => $label)
                                <option value="{{ $value }}" @selected(old('user_type', $targetUser->user_type) === $value)>{{ $label }}</option>
                            @endforeach
                        </select>
                        @error('user_type')<div class="invalid-feedback">{{ $message }}</div>@enderror
                    </div>
                    <div class="col-md-6">
                        <label class="form-label">Agency</label>
                        <select name="tenant_id" class="form-select @error('tenant_id') is-invalid @enderror">
                            <option value="">— Public user (no agency) —</option>
                            @foreach($tenants as $t)
                                <option value="{{ $t->id }}" @selected(old('tenant_id', $targetUser->tenant_id) == $t->id)>{{ $t->name }} ({{ $t->agency_code }})</option>
                            @endforeach
                        </select>
                        @error('tenant_id')<div class="invalid-feedback">{{ $message }}</div>@enderror
                    </div>
                    <div class="col-md-6">
                        <label class="form-label">Mobile</label>
                        <input type="text" name="phone" class="form-control" value="{{ old('phone', $targetUser->phone) }}">
                    </div>
                    <div class="col-md-6">
                        <label class="form-label">Country</label>
                        <input type="text" name="country" class="form-control" value="{{ old('country', $targetUser->country) }}">
                    </div>
                    <div class="col-md-6">
                        <label class="form-label">Role <span class="text-danger">*</span></label>
                        <select name="role_id" class="form-select @error('role_id') is-invalid @enderror" required>
                            <option value="">Select role</option>
                            @foreach($roles as $role)
                                <option value="{{ $role->id }}" @selected(old('role_id', $targetUser->role_id) == $role->id)>
                                    @if($role->tenant_id)
                                        [Agency #{{ $role->tenant_id }}] {{ $role->name }}
                                    @else
                                        [Platform] {{ $role->name }}
                                    @endif
                                </option>
                            @endforeach
                        </select>
                        @error('role_id')<div class="invalid-feedback">{{ $message }}</div>@enderror
                    </div>
                    <div class="col-md-6">
                        <label class="form-label">New password</label>
                        <input type="password" name="password" class="form-control @error('password') is-invalid @enderror" minlength="8">
                        <div class="form-text">Leave blank to keep current password.</div>
                        @error('password')<div class="invalid-feedback">{{ $message }}</div>@enderror
                    </div>
                    <div class="col-md-6">
                        <label class="form-label">Confirm new password</label>
                        <input type="password" name="password_confirmation" class="form-control" minlength="8">
                    </div>
                    <div class="col-md-6">
                        <label class="form-label">Profile picture</label>
                        <input type="file" name="photo" class="form-control" accept=".jpg,.jpeg,.png,.webp">
                        @if($targetUser->photo)
                            <div class="form-text">Current: <a href="{{ asset('storage/' . $targetUser->photo) }}" target="_blank">view</a></div>
                        @endif
                    </div>
                    <div class="col-md-6">
                        <label class="form-label">Agent document</label>
                        <input type="file" name="agent_document" class="form-control" accept=".pdf,.jpg,.jpeg,.png">
                        @if($targetUser->agent_document)
                            <div class="form-text">Current: <a href="{{ asset('storage/' . $targetUser->agent_document) }}" target="_blank">view</a></div>
                        @endif
                    </div>
                </div>

                <div class="d-flex justify-content-end gap-2">
                    <a href="{{ route('admin.users') }}" class="btn btn-secondary">Cancel</a>
                    <button type="submit" class="btn btn-primary"><i class="fas fa-save me-2"></i>Update user</button>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection
