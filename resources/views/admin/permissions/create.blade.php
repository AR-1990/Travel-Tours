@extends('admin.layouts.main')

@section('title', 'Create Permission')

@section('content')
<div class="container-fluid">
    <div class="row">
        <div class="col-lg-8 mx-auto">
            <!-- Header -->
            <div class="d-flex justify-content-between align-items-center mb-4">
                <div>
                    <h1 class="h3 mb-1 text-gray-800">Create New Permission</h1>
                    <p class="text-gray-600 mb-0">Add a new permission to the system</p>
                </div>
                <a href="{{ route('admin.permissions') }}" class="btn btn-secondary">
                    <i class="fas fa-arrow-left me-2"></i>Back to Permissions
                </a>
            </div>

            <!-- Form -->
            <div class="card-modern">
                <form action="{{ route('admin.permissions.store') }}" method="POST">
                    @csrf

                    <!-- Name -->
                    <div class="mb-3">
                        <label for="name" class="form-label">Permission Name <span class="text-danger">*</span></label>
                        <input type="text" class="form-control @error('name') is-invalid @enderror" 
                               id="name" name="name" value="{{ old('name') }}" required placeholder="e.g., View Users">
                        <small class="form-text text-muted">The display name for this permission</small>
                        @error('name')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    <!-- Group -->
                    <div class="mb-3">
                        <label for="group" class="form-label">Group <span class="text-danger">*</span></label>
                        <input type="text" class="form-control @error('group') is-invalid @enderror" 
                               id="group" name="group" value="{{ old('group') }}" required placeholder="e.g., users">
                        <small class="form-text text-muted">Group this permission belongs to (e.g., users, roles, permissions)</small>
                        @error('group')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    <!-- Description -->
                    <div class="mb-3">
                        <label for="description" class="form-label">Description</label>
                        <textarea class="form-control @error('description') is-invalid @enderror" 
                                  id="description" name="description" rows="3" placeholder="Optional description">{{ old('description') }}</textarea>
                        @error('description')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    <!-- Submit Buttons -->
                    <div class="d-flex justify-content-end gap-2">
                        <a href="{{ route('admin.permissions') }}" class="btn btn-secondary">Cancel</a>
                        <button type="submit" class="btn btn-primary">
                            <i class="fas fa-save me-2"></i>Create Permission
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>
@endsection
