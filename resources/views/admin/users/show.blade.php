@extends('admin.layouts.main')

@section('title', 'User details')

@section('content')
<div class="container-fluid">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h1 class="h3 mb-1 text-gray-800">User #{{ $targetUser->id }}</h1>
            <p class="text-gray-600 mb-0">{{ $targetUser->first_name }} {{ $targetUser->last_name }}</p>
        </div>
        <div class="d-flex gap-2">
            <a href="{{ route('admin.users.edit', $targetUser->id) }}" class="btn btn-primary"
                data-swal-confirm
                data-swal-title="Edit this user?"
                data-swal-text="You will leave this page and open the edit form."
                data-swal-icon="question"
                data-swal-confirm-text="Continue"
                data-swal-confirm-color="#0d6efd"><i class="fas fa-edit me-2"></i>Edit</a>
            <a href="{{ route('admin.users') }}" class="btn btn-secondary"><i class="fas fa-arrow-left me-2"></i>Back</a>
        </div>
    </div>

    <div class="row g-4">
        <div class="col-lg-8">
            <div class="card-modern">
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
        <div class="col-lg-4">
            <div class="card-modern mb-3">
                <h5 class="mb-3">Profile picture</h5>
                @if($targetUser->photo)
                    <img src="{{ asset('storage/' . $targetUser->photo) }}" alt="Profile" class="img-fluid rounded border" style="max-height: 220px;">
                @else
                    <p class="text-muted mb-0">No picture uploaded.</p>
                @endif
            </div>
            <div class="card-modern">
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
@endsection
