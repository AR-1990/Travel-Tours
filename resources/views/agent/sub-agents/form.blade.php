@extends('admin.layouts.main')

@section('title', isset($manager) ? 'Edit Sub-Agent' : 'Add Sub-Agent')

@section('content')
@php
    $isEdit = isset($manager);
@endphp
<div class="container-fluid panel-page">
    @include('admin.partials.page-header', [
        'title' => $isEdit ? 'Edit sub-agent' : 'Add sub-agent',
        'subtitle' => 'Create team members and assign role-based access.',
        'icon' => 'fas fa-user-tie',
        'actions' => '<a href="'.e(route('agent.managers')).'" class="btn btn-light btn-sm"><i class="fas fa-arrow-left me-1"></i> Back</a>',
    ])

    @include('admin.partials.flash')

    <div class="panel-surface">
        <div class="card-body">
            <form action="{{ $isEdit ? route('agent.managers.update', $manager->id) : route('agent.managers.store') }}" method="POST" enctype="multipart/form-data"
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
                        <label class="form-label">Password {{ $isEdit ? '(leave blank to keep current)' : '' }}</label>
                        <input type="password" name="password" class="form-control" {{ $isEdit ? '' : 'required' }}>
                    </div>
                    <div class="col-md-6">
                        <label class="form-label">Confirm Password</label>
                        <input type="password" name="password_confirmation" class="form-control" {{ $isEdit ? '' : 'required' }}>
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

                <p class="text-muted small mb-3">Sub-agents sign in with email or username.</p>

                <div class="d-flex flex-wrap gap-2">
                    <button type="submit" class="btn btn-primary">{{ $isEdit ? 'Update Sub-Agent' : 'Create Sub-Agent' }}</button>
                    <a href="{{ route('agent.managers') }}" class="btn btn-outline-secondary">Cancel</a>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection
