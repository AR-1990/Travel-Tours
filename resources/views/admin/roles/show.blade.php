@extends('admin.layouts.main')

@section('title', 'View Role')

@section('content')
@php
    $user = auth()->user();
    $panelPrefix = $user && $user->user_type === 'tenant_admin' ? 'agent' : ($user && $user->user_type === 'sub_agent' ? 'subagent' : 'admin');
@endphp
<div class="container-fluid panel-page">
    @include('admin.partials.page-header', [
        'title' => 'Role details',
        'subtitle' => 'View role information and permissions.',
        'icon' => 'fas fa-user-shield',
        'actions' => '<a href="'.e(route($panelPrefix.'.roles.edit', $role->id)).'" class="btn btn-primary btn-sm" data-swal-confirm data-swal-title="Edit this role?" data-swal-text="You will open the role editor." data-swal-icon="question" data-swal-confirm-text="Continue" data-swal-confirm-color="#053750"><i class="fas fa-edit me-1"></i> Edit</a>'
            .'<a href="'.e(route($panelPrefix.'.roles')).'" class="btn btn-light btn-sm"><i class="fas fa-arrow-left me-1"></i> Back</a>',
    ])

    @include('admin.partials.flash')

    <div class="panel-surface mb-4">
        <div class="card-body">
            <h3 class="h5 mb-3">{{ $role->name }}</h3>
            <div class="row">
                <div class="col-md-6">
                    <p><strong>Slug:</strong> <span class="badge bg-info">{{ $role->slug }}</span></p>
                    @if($role->description)
                        <p><strong>Description:</strong> {{ $role->description }}</p>
                    @endif
                    <p><strong>Users with this role:</strong> {{ $role->users->count() }}</p>
                </div>
                <div class="col-md-6">
                    <p><strong>Total Permissions:</strong> {{ $role->permissions->count() }}</p>
                    <p><strong>Created:</strong> {{ $role->created_at->format('M d, Y') }}</p>
                    <p><strong>Updated:</strong> {{ $role->updated_at->format('M d, Y') }}</p>
                </div>
            </div>
        </div>
    </div>

    <div class="panel-surface">
        <div class="card-body">
            <h4 class="h5 mb-3">Permissions</h4>
            @if($role->permissions->count() > 0)
                <div class="row">
                    @foreach($role->permissions->groupBy('group') as $group => $groupPermissions)
                        <div class="col-md-6 mb-3">
                            <h6 class="fw-bold">{{ ucfirst(str_replace('-', ' ', $group)) }}</h6>
                            <ul class="list-unstyled">
                                @foreach($groupPermissions as $permission)
                                    <li class="mb-1">
                                        <i class="fas fa-check-circle text-success me-2"></i>
                                        {{ $permission->name }}
                                    </li>
                                @endforeach
                            </ul>
                        </div>
                    @endforeach
                </div>
            @else
                <p class="text-muted mb-0">No permissions assigned to this role.</p>
            @endif
        </div>
    </div>
</div>
@endsection
