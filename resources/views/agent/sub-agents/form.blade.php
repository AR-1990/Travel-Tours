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

        <form action="{{ isset($manager) ? route('agent.managers.update', $manager->id) : route('agent.managers.store') }}" method="POST" class="card-modern p-4">
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
            </div>

            <div class="d-flex justify-content-between align-items-center mt-3">
                <a href="{{ route('agent.managers') }}" class="btn btn-light">Back</a>
                <button type="submit" class="btn btn-primary">{{ isset($manager) ? 'Update Sub-Agent' : 'Create Sub-Agent' }}</button>
            </div>
        </form>
    </div>
@endsection
