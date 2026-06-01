@extends('admin.layouts.main')

@section('title', isset($manager) ? 'Edit Sub-Agent' : 'Add Sub-Agent')

@section('content')
    <div class="container-fluid px-0">
        <div class="mb-4">
            <h2 class="h4 mb-1">{{ isset($manager) ? 'Edit Sub-Agent' : 'Add Sub-Agent' }}</h2>
            <p class="text-muted mb-0">Create team members and assign role-based access.</p>
        </div>

        @if($errors->any())
            <div class="alert alert-danger">
                <ul class="mb-0">
                    @foreach($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        @endif

        <form action="{{ isset($manager) ? route('agent.managers.update', $manager->id) : route('agent.managers.store') }}" method="POST" enctype="multipart/form-data" class="card-modern p-4"
            data-swal-confirm
            data-swal-title="{{ isset($manager) ? 'Save sub-agent changes?' : 'Create this sub-agent?' }}"
            data-swal-text="{{ isset($manager) ? 'Updates will apply immediately.' : 'A new sub-agent account will be created.' }}"
            data-swal-icon="question"
            data-swal-confirm-text="{{ isset($manager) ? 'Yes, save' : 'Yes, create' }}"
            data-swal-confirm-color="#0d6efd">
            @csrf
            @if(isset($manager))
                @method('PUT')
            @endif

            <div class="row g-3 mb-3">
                <div class="col-md-6">
                    <label class="form-label">First Name</label>
                    <input type="text" name="first_name" class="form-control" value="{{ old('first_name', data_get($manager ?? null, 'first_name', '')) }}" required>
                </div>
                <div class="col-md-6">
                    <label class="form-label">Last Name</label>
                    <input type="text" name="last_name" class="form-control" value="{{ old('last_name', data_get($manager ?? null, 'last_name', '')) }}" required>
                </div>
                <div class="col-md-6">
                    <label class="form-label">Email</label>
                    <input type="email" name="email" class="form-control" value="{{ old('email', data_get($manager ?? null, 'email', '')) }}" required>
                </div>
                <div class="col-md-6">
                    <label class="form-label">Username</label>
                    <input type="text" name="username" class="form-control" pattern="[a-zA-Z0-9._-]+" value="{{ old('username', data_get($manager ?? null, 'username', '')) }}" placeholder="Optional — auto from email if empty">
                </div>
                <div class="col-md-6">
                    <label class="form-label">Mobile</label>
                    <input type="text" name="phone" class="form-control" value="{{ old('phone', data_get($manager ?? null, 'phone', '')) }}">
                </div>
                <div class="col-md-6">
                    <label class="form-label">Country</label>
                    <input type="text" name="country" class="form-control" value="{{ old('country', data_get($manager ?? null, 'country', '')) }}">
                </div>
                <div class="col-md-6">
                    <label class="form-label">Role</label>
                    <select name="role_id" class="form-select" required>
                        <option value="">Select Role</option>
                        @foreach($roles as $role)
                            <option value="{{ $role->id }}" @selected(old('role_id', data_get($manager ?? null, 'role_id', '')) == $role->id)>
                                {{ $role->name }}
                            </option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-6">
                    <label class="form-label">Password {{ isset($manager) ? '(leave blank to keep current)' : '' }}</label>
                    <input type="password" name="password" class="form-control" {{ isset($manager) ? '' : 'required' }}>
                </div>
                <div class="col-md-6">
                    <label class="form-label">Confirm Password</label>
                    <input type="password" name="password_confirmation" class="form-control" {{ isset($manager) ? '' : 'required' }}>
                </div>
                <div class="col-md-6">
                    <label class="form-label">Profile picture</label>
                    <input type="file" name="photo" class="form-control" accept=".jpg,.jpeg,.png,.webp">
                    @if(isset($manager) && $manager->photo)
                        <small class="text-muted">Current: <a href="{{ asset('storage/' . $manager->photo) }}" target="_blank">view</a></small>
                    @endif
                </div>
                <div class="col-md-6">
                    <label class="form-label">Agent document</label>
                    <input type="file" name="agent_document" class="form-control" accept=".pdf,.jpg,.jpeg,.png">
                    @if(isset($manager) && $manager->agent_document)
                        <small class="text-muted">Current: <a href="{{ asset('storage/' . $manager->agent_document) }}" target="_blank">view</a></small>
                    @endif
                </div>
            </div>

            <p class="text-muted small mb-3">Sub-agents sign in with email or username.</p>

            <div class="d-flex justify-content-between align-items-center mt-3">
                <a href="{{ route('agent.managers') }}" class="btn btn-light">Back</a>
                <button type="submit" class="btn btn-primary">{{ isset($manager) ? 'Update Sub-Agent' : 'Create Sub-Agent' }}</button>
            </div>
        </form>
    </div>
@endsection
