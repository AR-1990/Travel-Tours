@extends('admin.layouts.main')

@section('title', 'Agents')

@section('content')
<div class="admin-panel">
    <!-- ========== SIDEBAR START ========== -->
    @include('admin.layouts.sidebar')
    <!-- ========== SIDEBAR END ========== -->

    <main class="main-content flex-grow-1">
        <div class="container-fluid">
            <!-- Page Header -->
            <div class="mb-4">
                <h1 class="dashboard-title">
                    <span class="title-icon">🏢</span> Agents Management
                </h1>
                <p class="dashboard-subtitle">Create and manage agent accounts</p>
            </div>

            <!-- Create Agent Card -->
            <div class="card-modern mb-4">
                <div class="create-agent-header">
                    <h2 class="h4 mb-0">
                        <i class="fas fa-plus-circle me-2"></i>Create New Agent
                    </h2>
                </div>

                <form method="POST" action="{{ route('admin.tenants.store') }}" class="create-agent-form">
                    @csrf

                    <!-- Agent Information -->
                    <div class="form-section">
                        <h5 class="form-section-title">Agent Information</h5>
                        <div class="row g-3">
                            <div class="col-md-4">
                                <label class="form-label">Agent Name</label>
                                <input type="text" name="tenant_name" class="form-control" placeholder="Enter agent name" required>
                            </div>
                            <div class="col-md-4">
                                <label class="form-label">Agent Email</label>
                                <input type="email" name="tenant_email" class="form-control" placeholder="Enter agent email">
                            </div>
                            <div class="col-md-4">
                                <label class="form-label">Agent Phone</label>
                                <input type="tel" name="tenant_phone" class="form-control" placeholder="Enter phone number">
                            </div>
                        </div>
                    </div>

                    <!-- Admin Account Information -->
                    <div class="form-section">
                        <h5 class="form-section-title">Admin Account Details</h5>
                        <div class="row g-3">
                            <div class="col-md-3">
                                <label class="form-label">First Name</label>
                                <input type="text" name="admin_first_name" class="form-control" placeholder="Admin first name" required>
                            </div>
                            <div class="col-md-3">
                                <label class="form-label">Last Name</label>
                                <input type="text" name="admin_last_name" class="form-control" placeholder="Admin last name" required>
                            </div>
                            <div class="col-md-3">
                                <label class="form-label">Email</label>
                                <input type="email" name="admin_email" class="form-control" placeholder="Admin email" required>
                            </div>
                            <div class="col-md-3">
                                <label class="form-label">Password</label>
                                <input type="password" name="admin_password" class="form-control" placeholder="Set password" required>
                            </div>
                        </div>
                    </div>

                    <!-- Submit Button -->
                    <div class="form-actions">
                        <button type="submit" class="btn btn-primary">
                            <i class="fas fa-check-circle me-2"></i>Create Agent
                        </button>
                    </div>
                </form>
            </div>

            <!-- Agent Requests Table -->
            <div class="card-modern">
                <div class="table-header">
                    <h2 class="h4 mb-0">
                        <i class="fas fa-list me-2"></i>Agent Requests
                    </h2>
                    <div class="table-stats">
                        <span class="stat-badge">Total: {{ count($tenants) }}</span>
                    </div>
                </div>

                <div class="table-responsive">
                    <table class="detail-table">
                        <thead>
                            <tr>
                                <th>Agent Name</th>
                                <th>Email</th>
                                <th>Sub Agents</th>
                                <th>Status</th>
                                <th>Active</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($tenants as $tenant)
                                <tr class="agent-row">
                                    <td class="agent-name">
                                        <div class="agent-avatar">
                                            {{ strtoupper(substr($tenant->name, 0, 2)) }}
                                        </div>
                                        <span>{{ $tenant->name }}</span>
                                    </td>
                                    <td class="agent-email">
                                        <a href="mailto:{{ $tenant->email }}">{{ $tenant->email }}</a>
                                    </td>
                                    <td class="agent-count">
                                        <div class="badge-count">{{ $tenant->sub_agents_count }}</div>
                                    </td>
                                    <td>
                                        @if($tenant->status === 'approved')
                                            <span class="status-badge status-approved">
                                                <i class="fas fa-check-circle me-1"></i>Approved
                                            </span>
                                        @elseif($tenant->status === 'pending')
                                            <span class="status-badge status-pending">
                                                <i class="fas fa-hourglass-half me-1"></i>Pending
                                            </span>
                                        @elseif($tenant->status === 'rejected')
                                            <span class="status-badge status-rejected">
                                                <i class="fas fa-times-circle me-1"></i>Rejected
                                            </span>
                                        @endif
                                    </td>
                                    <td>
                                        <span class="active-badge {{ $tenant->is_active ? 'active-yes' : 'active-no' }}">
                                            {{ $tenant->is_active ? '✓ Yes' : '✗ No' }}
                                        </span>
                                    </td>
                                    <td class="actions-cell">
                                        <div class="action-buttons">
                                            <a href="{{ route('admin.tenants.show', $tenant->id) }}" class="btn-action btn-details" title="View Details">
                                                <i class="fas fa-eye"></i>
                                            </a>

                                            @if($tenant->status === 'pending' || $tenant->status === 'rejected')
                                                <form method="POST" action="{{ route('admin.tenants.approve', $tenant->id) }}" style="display: inline;">
                                                    @csrf
                                                    <button type="submit" class="btn-action btn-approve" title="Approve Agent">
                                                        <i class="fas fa-check"></i>
                                                    </button>
                                                </form>
                                            @endif

                                            @if($tenant->status !== 'rejected')
                                                <form method="POST" action="{{ route('admin.tenants.reject', $tenant->id) }}" style="display: inline;">
                                                    @csrf
                                                    <button type="submit" class="btn-action btn-reject" title="Reject Agent" onclick="return confirm('Are you sure?');">
                                                        <i class="fas fa-times"></i>
                                                    </button>
                                                </form>
                                            @endif
                                        </div>
                                    </td>
                                </tr>
                            @empty
                                <tr class="empty-row">
                                    <td colspan="6" class="text-center py-5">
                                        <div class="empty-state">
                                            <i class="fas fa-inbox fa-3x mb-3"></i>
                                            <p class="text-muted">No agents found</p>
                                        </div>
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </main>
</div>
@endsection
