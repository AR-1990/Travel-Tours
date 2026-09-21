@extends('admin.layouts.main')

@section('title', 'Create Permission')

@section('content')
@php
    $user = auth()->user();
    $panelPrefix = $user && $user->user_type === 'tenant_admin' ? 'agent' : ($user && $user->user_type === 'sub_agent' ? 'subagent' : 'admin');
@endphp
<div class="container-fluid panel-page">
    @include('admin.partials.page-header', [
        'title' => 'Create permission',
        'subtitle' => 'Add a new permission to the system.',
        'icon' => 'fas fa-key',
        'actions' => '<a href="'.e(route($panelPrefix.'.permissions')).'" class="btn btn-light btn-sm"><i class="fas fa-arrow-left me-1"></i> Back</a>',
    ])

    @include('admin.partials.flash')

    <div class="panel-surface">
        <div class="card-body">
            <form action="{{ route($panelPrefix . '.permissions.store') }}" method="POST">
                @csrf

                <div class="mb-3">
                    <label for="name" class="form-label">Permission Name <span class="text-danger">*</span></label>
                    <input type="text" class="form-control @error('name') is-invalid @enderror"
                           id="name" name="name" value="{{ old('name') }}" required placeholder="e.g., View Users">
                    <small class="form-text text-muted">The display name for this permission</small>
                    @error('name')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>

                <div class="mb-3">
                    <label for="group" class="form-label">Group <span class="text-danger">*</span></label>
                    <input type="text" class="form-control @error('group') is-invalid @enderror"
                           id="group" name="group" value="{{ old('group') }}" required placeholder="e.g., users">
                    <small class="form-text text-muted">Group this permission belongs to (e.g., users, roles, permissions)</small>
                    @error('group')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>

                <div class="mb-3">
                    <label for="description" class="form-label">Description</label>
                    <textarea class="form-control @error('description') is-invalid @enderror"
                              id="description" name="description" rows="3" placeholder="Optional description">{{ old('description') }}</textarea>
                    @error('description')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>

                <div class="d-flex justify-content-end gap-2">
                    <a href="{{ route($panelPrefix . '.permissions') }}" class="btn btn-secondary">Cancel</a>
                    <button type="submit" class="btn btn-primary">
                        <i class="fas fa-save me-2"></i>Create Permission
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection
