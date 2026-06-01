@extends('admin.layouts.main')

@section('title', 'Create User')

@section('content')
<div class="container-fluid">
    <div class="row">
        <div class="col-lg-10 mx-auto">
            <div class="d-flex justify-content-between align-items-center mb-4">
                <div>
                    <h1 class="h3 mb-1 text-gray-800">Create user</h1>
                    <p class="text-gray-600 mb-0">First and last name are display only. User signs in with <strong>email</strong> or <strong>username</strong>.</p>
                </div>
                <a href="{{ route('admin.users') }}" class="btn btn-secondary">
                    <i class="fas fa-arrow-left me-2"></i>Back
                </a>
            </div>

            <div class="card-modern">
                <form action="{{ route('admin.users.store') }}" method="POST" enctype="multipart/form-data"
                    data-swal-confirm
                    data-swal-title="Create this user?"
                    data-swal-text="The account will be created with the details you entered."
                    data-swal-icon="question"
                    data-swal-confirm-text="Yes, create"
                    data-swal-confirm-color="#0d6efd">
                    @csrf
                    <div class="row g-3 mb-3">
                        <div class="col-md-6">
                            <label class="form-label">First name <span class="text-danger">*</span></label>
                            <input type="text" name="first_name" class="form-control @error('first_name') is-invalid @enderror" value="{{ old('first_name') }}" required>
                            @error('first_name')<div class="invalid-feedback">{{ $message }}</div>@enderror
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Last name <span class="text-danger">*</span></label>
                            <input type="text" name="last_name" class="form-control @error('last_name') is-invalid @enderror" value="{{ old('last_name') }}" required>
                            @error('last_name')<div class="invalid-feedback">{{ $message }}</div>@enderror
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Email <span class="text-danger">*</span></label>
                            <input type="email" name="email" class="form-control @error('email') is-invalid @enderror" value="{{ old('email') }}" required>
                            @error('email')<div class="invalid-feedback">{{ $message }}</div>@enderror
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Username <span class="text-danger">*</span></label>
                            <input type="text" name="username" class="form-control @error('username') is-invalid @enderror" value="{{ old('username') }}" required pattern="[a-zA-Z0-9._-]+">
                            @error('username')<div class="invalid-feedback">{{ $message }}</div>@enderror
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">User type <span class="text-danger">*</span></label>
                            <select name="user_type" class="form-select @error('user_type') is-invalid @enderror" required>
                                @foreach($userTypes as $value => $label)
                                    <option value="{{ $value }}" @selected(old('user_type', 'public') === $value)>{{ $label }}</option>
                                @endforeach
                            </select>
                            @error('user_type')<div class="invalid-feedback">{{ $message }}</div>@enderror
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Agency</label>
                            <select name="tenant_id" class="form-select @error('tenant_id') is-invalid @enderror">
                                <option value="">— Public user (no agency) —</option>
                                @foreach($tenants as $t)
                                    <option value="{{ $t->id }}" @selected(old('tenant_id') == $t->id)>{{ $t->name }} ({{ $t->agency_code }})</option>
                                @endforeach
                            </select>
                            @error('tenant_id')<div class="invalid-feedback">{{ $message }}</div>@enderror
                            <div class="form-text">Required for agent admin and sub agent.</div>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Mobile</label>
                            <input type="text" name="phone" class="form-control" value="{{ old('phone') }}">
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Country</label>
                            <input type="text" name="country" class="form-control" value="{{ old('country') }}">
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Role <span class="text-danger">*</span></label>
                            <select name="role_id" class="form-select @error('role_id') is-invalid @enderror" required>
                                <option value="">Select role</option>
                                @foreach($roles as $role)
                                    <option value="{{ $role->id }}" @selected(old('role_id') == $role->id)>
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
                            <label class="form-label">Password <span class="text-danger">*</span></label>
                            <input type="password" name="password" class="form-control @error('password') is-invalid @enderror" required minlength="8">
                            @error('password')<div class="invalid-feedback">{{ $message }}</div>@enderror
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Confirm password <span class="text-danger">*</span></label>
                            <input type="password" name="password_confirmation" class="form-control" required minlength="8">
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Profile picture</label>
                            <input type="file" name="photo" class="form-control" accept=".jpg,.jpeg,.png,.webp">
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Agent document</label>
                            <input type="file" name="agent_document" class="form-control" accept=".pdf,.jpg,.jpeg,.png">
                        </div>
                    </div>
                    <div class="d-flex justify-content-end gap-2">
                        <a href="{{ route('admin.users') }}" class="btn btn-secondary">Cancel</a>
                        <button type="submit" class="btn btn-primary"><i class="fas fa-save me-2"></i>Create user</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>
@endsection
