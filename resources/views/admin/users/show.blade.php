@extends('admin.layouts.main')

@section('title', 'User details')

@section('content')
<div class="container-fluid panel-page">
    @include('admin.partials.page-header', [
        'title' => 'User #'.$targetUser->id,
        'subtitle' => trim($targetUser->first_name.' '.$targetUser->last_name),
        'icon' => 'fas fa-user',
        'actions' => '<a href="'.e(route('admin.users.edit', $targetUser->id)).'" class="btn btn-primary btn-sm" data-swal-confirm data-swal-title="Edit this user?" data-swal-text="You will leave this page and open the edit form." data-swal-icon="question" data-swal-confirm-text="Continue" data-swal-confirm-color="#053750"><i class="fas fa-edit me-1"></i> Edit</a>'
            .'<a href="'.e(route('admin.users')).'" class="btn btn-light btn-sm"><i class="fas fa-arrow-left me-1"></i> Back</a>',
    ])

    @include('admin.partials.flash')

    <div class="row g-4">
        <div class="col-lg-8">
            <div class="panel-surface">
                <div class="card-body">
                    <h5 class="mb-3">Account</h5>
                    <dl class="row mb-0">
                        <dt class="col-sm-4">Email</dt>
                        <dd class="col-sm-8">{{ $targetUser->email }}</dd>
                        <dt class="col-sm-4">Username</dt>
                        <dd class="col-sm-8"><code>{{ $targetUser->username }}</code></dd>
                        <dt class="col-sm-4">User type</dt>
                        <dd class="col-sm-8">
                            @if($targetUser->user_type === 'tenant_admin')
                                Agent admin
                            @elseif($targetUser->user_type === 'sub_agent')
                                Sub agent
                            @else
                                Public user
                            @endif
                        </dd>
                        <dt class="col-sm-4">Role</dt>
                        <dd class="col-sm-8">{{ $targetUser->role->name ?? '—' }}</dd>
                        <dt class="col-sm-4">Agency</dt>
                        <dd class="col-sm-8">
                            @if($targetUser->tenant)
                                {{ $targetUser->tenant->name }} ({{ $targetUser->tenant->agency_code }})
                            @else
                                —
                            @endif
                        </dd>
                        <dt class="col-sm-4">Mobile</dt>
                        <dd class="col-sm-8">{{ $targetUser->phone ?: '—' }}</dd>
                        <dt class="col-sm-4">Country</dt>
                        <dd class="col-sm-8">{{ $targetUser->country ?: '—' }}</dd>
                        <dt class="col-sm-4">Status</dt>
                        <dd class="col-sm-8">
                            @if($targetUser->is_active)
                                <span class="badge bg-success">Active</span>
                            @else
                                <span class="badge bg-secondary">Inactive</span>
                            @endif
                        </dd>
                    </dl>
                </div>
            </div>
        </div>
        <div class="col-lg-4">
            <div class="panel-surface mb-3">
                <div class="card-body">
                    <h5 class="mb-3">Profile picture</h5>
                    @if($targetUser->photo)
                        <img src="{{ asset('storage/' . $targetUser->photo) }}" alt="Profile" class="img-fluid rounded border" style="max-height: 220px;">
                    @else
                        <p class="text-muted mb-0">No picture uploaded.</p>
                    @endif
                </div>
            </div>
            <div class="panel-surface">
                <div class="card-body">
                    <h5 class="mb-3">Agent document</h5>
                    @if($targetUser->agent_document)
                        <a href="{{ asset('storage/' . $targetUser->agent_document) }}" target="_blank" class="btn btn-outline-primary btn-sm">
                            <i class="fas fa-file-download me-1"></i>View file
                        </a>
                    @else
                        <p class="text-muted mb-0">No document uploaded.</p>
                    @endif
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
