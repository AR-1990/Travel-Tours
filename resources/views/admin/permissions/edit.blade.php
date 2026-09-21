@extends('admin.layouts.main')

@section('title', 'Edit Permission')

@section('content')
@php
    $user = auth()->user();
    $panelPrefix = $user && $user->user_type === 'tenant_admin' ? 'agent' : ($user && $user->user_type === 'sub_agent' ? 'subagent' : 'admin');
@endphp
<div class="container-fluid panel-page">
    @include('admin.partials.page-header', [
        'title' => 'Edit permission',
        'subtitle' => 'Update permission information.',
        'icon' => 'fas fa-key',
        'actions' => '<a href="'.e(route($panelPrefix.'.permissions')).'" class="btn btn-light btn-sm"><i class="fas fa-arrow-left me-1"></i> Back</a>',
    ])

    @include('admin.partials.flash')

    <div class="panel-surface">
        <div class="card-body">
            <form action="{{ route($panelPrefix . '.permissions.update', $permission->id) }}" method="POST">
                @csrf
                @method('PUT')

                <div class="mb-3">
                    <label for="name" class="form-label">Permission Name <span class="text-danger">*</span></label>
                    <input type="text" class="form-control @error('name') is-invalid @enderror"
                           id="name" name="name" value="{{ old('name', $permission->name) }}" required>
                    @error('name')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>

                <div class="mb-3">
                    <label for="group" class="form-label">Group <span class="text-danger">*</span></label>
                    <input type="text" class="form-control @error('group') is-invalid @enderror"
                           id="group" name="group" value="{{ old('group', $permission->group) }}" required>
                    @error('group')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>

                <div class="mb-3">
                    <label for="description" class="form-label">Description</label>
                    <textarea class="form-control @error('description') is-invalid @enderror"
                              id="description" name="description" rows="3">{{ old('description', $permission->description) }}</textarea>
                    @error('description')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>

                <div class="mb-3">
                    <label class="form-label">Slug</label>
                    <input type="text" class="form-control" value="{{ $permission->slug }}" disabled>
                    <small class="form-text text-muted">Slug is automatically generated from the name</small>
                </div>

                <div class="d-flex justify-content-end gap-2">
                    <a href="{{ route($panelPrefix . '.permissions') }}" class="btn btn-secondary">Cancel</a>
                    <button type="submit" class="btn btn-primary">
                        <i class="fas fa-save me-2"></i>Update Permission
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection
