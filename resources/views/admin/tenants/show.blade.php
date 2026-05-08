@extends('admin.layouts.main')

@section('title', 'Agent Details')

@section('content')
<div class="admin-panel">
    <!-- ========== SIDEBAR START ========== -->
    @include('admin.layouts.sidebar')
    <!-- ========== SIDEBAR END ========== -->

    <main class="main-content flex-grow-1">
        <div class="container-fluid">
            <!-- Page Header -->
            <div class="mb-4">
                <div class="page-header">
                    <div>
                        <h1 class="dashboard-title">
                            <span class="title-icon">🏢</span> Agent Details
                        </h1>
                        <p class="dashboard-subtitle">View and manage agent information</p>
                    </div>
                    <a href="{{ route('admin.tenants.index') }}" class="btn btn-secondary">
                        <i class="fas fa-arrow-left me-2"></i>Back to Agents
                    </a>
                </div>
            </div>

            <!-- Agent Information Card -->
            <div class="card-modern mb-4">
                <div class="card-header-modern">
                    <h2 class="h4 mb-0">
                        <i class="fas fa-info-circle me-2"></i>Agent Information
                    </h2>
                </div>

                <div class="agent-info-grid">
                    <!-- Agent Name -->
                    <div class="info-item">
                        <label class="info-label">Agent Name</label>
                        <p class="info-value">
                            <span class="agent-badge">{{ strtoupper(substr($tenant->name, 0, 1)) }}</span>
                            {{ $tenant->name }}
                        </p>
                    </div>

                    <!-- Email -->
                    <div class="info-item">
                        <label class="info-label">Email Address</label>
                        <p class="info-value">
                            <a href="mailto:{{ $tenant->email }}" class="info-link">
                                <i class="fas fa-envelope me-1"></i>{{ $tenant->email ?? '-' }}
                            </a>
                        </p>
                    </div>

                    <!-- Phone -->
                    <div class="info-item">
                        <label class="info-label">Phone Number</label>
                        <p class="info-value">
                            <i class="fas fa-phone me-1"></i>{{ $tenant->phone ?? '-' }}
                        </p>
                    </div>

                    <!-- Status -->
                    <div class="info-item">
                        <label class="info-label">Status</label>
                        <p class="info-value">
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
                        </p>
                    </div>

                    <!-- Active Status -->
                    <div class="info-item">
                        <label class="info-label">Active Status</label>
                        <p class="info-value">
                            <span class="active-badge {{ $tenant->is_active ? 'active-yes' : 'active-no' }}">
                                {{ $tenant->is_active ? '✓ Active' : '✗ Inactive' }}
                            </span>
                        </p>
                    </div>

                    <!-- Approved At -->
                    <div class="info-item">
                        <label class="info-label">Approved At</label>
                        <p class="info-value">
                            <i class="fas fa-calendar me-1"></i>
                            {{ $tenant->approved_at ? $tenant->approved_at->format('M d, Y H:i A') : '-' }}
                        </p>
                    </div>
                </div>
            </div>

            <!-- Agent Admins Card -->
            <div class="card-modern mb-4">
                <div class="card-header-modern">
                    <div class="header-with-stats">
                        <h2 class="h4 mb-0">
                            <i class="fas fa-user-shield me-2"></i>Agent Admins
                        </h2>
                        <span class="stat-badge">{{ count($tenantAdmins) }} Admin(s)</span>
                    </div>
                </div>

                <div class="table-responsive">
                    <table class="detail-table">
                        <thead>
                            <tr>
                                <th>Name</th>
                                <th>Email</th>
                                <th>Designation</th>
                                <th>Status</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($tenantAdmins as $user)
                                <tr class="detail-row">
                                    <td class="user-name">
                                        <div class="user-avatar">
                                            {{ strtoupper(substr($user->first_name, 0, 1)) }}
                                        </div>
                                        <span>{{ $user->first_name }} {{ $user->last_name }}</span>
                                    </td>
                                    <td class="user-email">
                                        <a href="mailto:{{ $user->email }}">{{ $user->email }}</a>
                                    </td>
                                    <td class="user-designation">
                                        {{ $user->designation ?? '-' }}
                                    </td>
                                    <td>
                                        <span class="user-status {{ $user->is_active ? 'status-active' : 'status-inactive' }}">
                                            {{ $user->is_active ? '✓ Active' : '✗ Inactive' }}
                                        </span>
                                    </td>
                                </tr>
                            @empty
                                <tr class="empty-row">
                                    <td colspan="4" class="text-center py-4">
                                        <div class="empty-state">
                                            <i class="fas fa-user-slash fa-2x mb-2"></i>
                                            <p class="text-muted mb-0">No agent admins found</p>
                                        </div>
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>

            <!-- Sub Agents Card -->
            <div class="card-modern">
                <div class="card-header-modern">
                    <div class="header-with-stats">
                        <h2 class="h4 mb-0">
                            <i class="fas fa-users me-2"></i>Sub Agents
                        </h2>
                        <span class="stat-badge">{{ count($subAgents) }} Sub Agent(s)</span>
                    </div>
                </div>

                <div class="table-responsive">
                    <table class="detail-table">
                        <thead>
                            <tr>
                                <th>Name</th>
                                <th>Email</th>
                                <th>Role/Category</th>
                                <th>Designation</th>
                                <th>Status</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($subAgents as $agent)
                                <tr class="detail-row">
                                    <td class="user-name">
                                        <div class="user-avatar">
                                            {{ strtoupper(substr($agent->first_name, 0, 1)) }}
                                        </div>
                                        <span>{{ $agent->first_name }} {{ $agent->last_name }}</span>
                                    </td>
                                    <td class="user-email">
                                        <a href="mailto:{{ $agent->email }}">{{ $agent->email }}</a>
                                    </td>
                                    <td class="user-role">
                                        <span class="role-badge">{{ $agent->role->name ?? '-' }}</span>
                                    </td>
                                    <td class="user-designation">
                                        {{ $agent->designation ?? '-' }}
                                    </td>
                                    <td>
                                        <span class="user-status {{ $agent->is_active ? 'status-active' : 'status-inactive' }}">
                                            {{ $agent->is_active ? '✓ Active' : '✗ Inactive' }}
                                        </span>
                                    </td>
                                </tr>
                            @empty
                                <tr class="empty-row">
                                    <td colspan="5" class="text-center py-4">
                                        <div class="empty-state">
                                            <i class="fas fa-user-slash fa-2x mb-2"></i>
                                            <p class="text-muted mb-0">No sub agents found for this tenant</p>
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

<style>
    /* ==================== AGENT DETAILS PAGE STYLES ==================== */

    /* Page Header */
    .page-header {
        display: flex;
        justify-content: space-between;
        align-items: flex-start;
        gap: 2rem;
        margin-bottom: 1rem;
    }

    .page-header > div:first-child {
        flex: 1;
    }

    .btn-secondary {
        background: linear-gradient(135deg, rgba(107, 114, 128, 0.2), rgba(75, 85, 99, 0.2));
        color: #6b7280;
        border: 1px solid rgba(107, 114, 128, 0.3);
        padding: 0.625rem 1.25rem;
        border-radius: 0.75rem;
        font-weight: 600;
        transition: var(--transition);
        display: inline-flex;
        align-items: center;
        white-space: nowrap;
    }

    .btn-secondary:hover {
        background: linear-gradient(135deg, #6b7280, #4b5563);
        color: white;
        border-color: #6b7280;
    }

    html.dark-mode .btn-secondary {
        background: linear-gradient(135deg, rgba(107, 114, 128, 0.3), rgba(75, 85, 99, 0.3));
        color: #9ca3af;
    }

    html.dark-mode .btn-secondary:hover {
        background: linear-gradient(135deg, #6b7280, #4b5563);
        color: white;
    }

    /* Card Header */
    .card-header-modern {
        padding-bottom: 1.5rem;
        border-bottom: 2px solid var(--border-color);
        margin-bottom: 1.5rem;
    }

    .header-with-stats {
        display: flex;
        justify-content: space-between;
        align-items: center;
        gap: 1rem;
    }

    .stat-badge {
        background: linear-gradient(135deg, rgba(102, 126, 234, 0.1), rgba(118, 75, 162, 0.1));
        color: var(--primary-color);
        padding: 0.5rem 1rem;
        border-radius: 0.75rem;
        font-weight: 600;
        font-size: 0.85rem;
        white-space: nowrap;
    }

    /* Agent Info Grid */
    .agent-info-grid {
        display: grid;
        grid-template-columns: repeat(auto-fit, minmax(280px, 1fr));
        gap: 2rem;
        margin: 0;
    }

    .info-item {
        padding: 1.5rem;
        background: linear-gradient(135deg, rgba(102, 126, 234, 0.03), rgba(118, 75, 162, 0.03));
        border: 1px solid var(--border-color);
        border-radius: 0.75rem;
        transition: var(--transition);
    }

    .info-item:hover {
        border-color: var(--primary-color);
        box-shadow: 0 2px 8px rgba(102, 126, 234, 0.1);
    }

    html.dark-mode .info-item {
        background: linear-gradient(135deg, rgba(102, 126, 234, 0.08), rgba(118, 75, 162, 0.08));
    }

    .info-label {
        display: block;
        font-size: 0.85rem;
        font-weight: 600;
        color: var(--text-secondary);
        text-transform: uppercase;
        letter-spacing: 0.5px;
        margin-bottom: 0.75rem;
    }

    .info-value {
        font-size: 1rem;
        color: var(--text-primary);
        margin: 0;
        display: flex;
        align-items: center;
        gap: 0.5rem;
    }

    .info-link {
        color: var(--primary-color);
        text-decoration: none;
        transition: var(--transition);
    }

    .info-link:hover {
        color: var(--secondary-color);
        text-decoration: underline;
    }

    /* Agent Badge */
    .agent-badge {
        width: 40px;
        height: 40px;
        border-radius: 0.75rem;
        background: var(--primary-gradient);
        color: white;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        font-weight: 700;
        font-size: 0.9rem;
        flex-shrink: 0;
    }

    /* Detail Tables */
    .detail-table {
        margin-bottom: 0;
        width: 100%;
        border-collapse: collapse;
    }

    .detail-table thead th {
        background: linear-gradient(135deg, rgba(102, 126, 234, 0.05), rgba(118, 75, 162, 0.05));
        color: var(--text-primary);
        font-weight: 600;
        padding: 1rem;
        text-align: left;
        border-bottom: 2px solid var(--border-color);
        text-transform: uppercase;
        font-size: 0.85rem;
        letter-spacing: 0.5px;
    }

    .detail-table tbody tr {
        border-bottom: 1px solid var(--border-color);
        transition: var(--transition);
    }

    .detail-table tbody tr:hover {
        background: rgba(102, 126, 234, 0.05);
    }

    html.dark-mode .detail-table tbody tr:hover {
        background: rgba(102, 126, 234, 0.1);
    }

    .detail-table tbody td {
        padding: 1rem;
        color: var(--text-primary);
        vertical-align: middle;
    }

    .detail-row {
        transition: var(--transition);
    }

    /* User Name Cell */
    .user-name {
        display: flex;
        align-items: center;
        gap: 0.75rem;
        font-weight: 600;
    }

    .user-avatar {
        width: 40px;
        height: 40px;
        border-radius: 0.75rem;
        background: var(--primary-gradient);
        color: white;
        display: flex;
        align-items: center;
        justify-content: center;
        font-weight: 700;
        font-size: 0.9rem;
        flex-shrink: 0;
    }

    /* User Email Cell */
    .user-email a,
    .info-link {
        color: var(--primary-color);
        text-decoration: none;
        transition: var(--transition);
    }

    .user-email a:hover,
    .info-link:hover {
        color: var(--secondary-color);
        text-decoration: underline;
    }

    /* User Status */
    .user-status {
        display: inline-flex;
        align-items: center;
        gap: 0.25rem;
        padding: 0.5rem 0.875rem;
        border-radius: 0.5rem;
        font-weight: 600;
        font-size: 0.85rem;
        white-space: nowrap;
    }

    .status-active {
        background: linear-gradient(135deg, rgba(16, 185, 129, 0.2), rgba(5, 150, 136, 0.2));
        color: #10b981;
    }

    .status-inactive {
        background: linear-gradient(135deg, rgba(107, 114, 128, 0.2), rgba(75, 85, 99, 0.2));
        color: #6b7280;
    }

    /* Role Badge */
    .role-badge {
        display: inline-block;
        background: linear-gradient(135deg, rgba(139, 92, 246, 0.2), rgba(126, 34, 206, 0.2));
        color: #a855f7;
        padding: 0.375rem 0.75rem;
        border-radius: 0.5rem;
        font-weight: 600;
        font-size: 0.85rem;
        white-space: nowrap;
    }

    /* Empty State */
    .empty-row {
        border: none;
    }

    .empty-state {
        color: var(--text-secondary);
        padding: 2rem 0;
    }

    .empty-state i {
        opacity: 0.3;
        color: var(--text-secondary);
    }

    /* ==================== RESPONSIVE DESIGN ==================== */

    @media (max-width: 1024px) {
        .page-header {
            flex-direction: column;
            gap: 1rem;
        }

        .agent-info-grid {
            grid-template-columns: repeat(auto-fit, minmax(240px, 1fr));
            gap: 1.5rem;
        }

        .info-item {
            padding: 1.25rem;
        }
    }

    @media (max-width: 768px) {
        .agent-info-grid {
            grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
            gap: 1rem;
        }

        .info-item {
            padding: 1rem;
        }

        .header-with-stats {
            flex-direction: column;
            align-items: flex-start;
        }

        .detail-table {
            font-size: 0.9rem;
        }

        .detail-table thead th,
        .detail-table tbody td {
            padding: 0.75rem 0.5rem;
        }

        .user-name {
            flex-wrap: wrap;
        }

        .user-avatar {
            width: 36px;
            height: 36px;
            font-size: 0.8rem;
        }

        .agent-badge {
            width: 36px;
            height: 36px;
            font-size: 0.8rem;
        }

        .stat-badge {
            padding: 0.375rem 0.75rem;
            font-size: 0.8rem;
        }
    }

    @media (max-width: 576px) {
        .dashboard-title {
            font-size: 1.75rem;
        }

        .agent-info-grid {
            grid-template-columns: 1fr;
            gap: 1rem;
        }

        .info-item {
            padding: 0.875rem;
        }

        .detail-table {
            font-size: 0.85rem;
        }

        .detail-table thead th,
        .detail-table tbody td {
            padding: 0.5rem;
        }

        .user-name,
        .user-designation {
            flex-direction: column;
            gap: 0.25rem;
        }

        .user-avatar {
            width: 32px;
            height: 32px;
            font-size: 0.75rem;
        }

        .btn-secondary {
            width: 100%;
            justify-content: center;
        }

        .page-header {
            margin-bottom: 2rem;
        }
    }

    /* ==================== DARK MODE ==================== */

    html.dark-mode .card-header-modern {
        border-bottom-color: var(--border-color);
    }

    html.dark-mode .info-label {
        color: var(--text-secondary);
    }

    html.dark-mode .info-value {
        color: var(--text-primary);
    }

    html.dark-mode .detail-table thead th {
        background: linear-gradient(135deg, rgba(102, 126, 234, 0.15), rgba(118, 75, 162, 0.15));
        color: var(--text-primary);
        border-bottom-color: var(--border-color);
    }

    html.dark-mode .detail-table tbody tr {
        border-bottom-color: var(--border-color);
    }

    html.dark-mode .detail-table tbody td {
        color: var(--text-primary);
    }

    html.dark-mode .stat-badge {
        background: linear-gradient(135deg, rgba(102, 126, 234, 0.2), rgba(118, 75, 162, 0.2));
    }

    html.dark-mode .user-email a,
    html.dark-mode .info-link {
        color: var(--primary-color);
    }

    html.dark-mode .user-email a:hover,
    html.dark-mode .info-link:hover {
        color: var(--secondary-color);
    }

    html.dark-mode .empty-state {
        color: var(--text-secondary);
    }

    html.dark-mode .empty-state i {
        color: var(--text-secondary);
    }
</style>