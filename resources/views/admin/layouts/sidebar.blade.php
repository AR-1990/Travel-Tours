<!-- SIDEBAR START -->
<aside id="dashboardSidebar" class="sidebar">
    <ul class="sidebar-menu">
        @php
            $user = auth()->user();
            if ($user) {
                $user->load(['userPermissions', 'role']);
            }
            $isSuperAdmin = $user && $user->user_type === 'super_admin';
            $dashboardRoute = 'admin.dashboard';
            $panelPrefix = 'admin';
            if ($user && $user->user_type === 'tenant_admin') {
                $dashboardRoute = 'agent.dashboard';
                $panelPrefix = 'agent';
            } elseif ($user && $user->user_type === 'sub_agent') {
                $dashboardRoute = 'subagent.dashboard';
                $panelPrefix = 'subagent';
            }
        @endphp

        <li>
            <a href="{{ route($dashboardRoute) }}" class="{{ request()->routeIs('admin.dashboard') || request()->routeIs('agent.dashboard') || request()->routeIs('subagent.dashboard') ? 'active' : '' }}">
                <svg fill="currentColor" viewBox="0 0 24 24">
                    <path d="M3 13h8V3H3v10zm0 8h8v-6H3v6zm10 0h8V11h-8v10zm0-18v6h8V3h-8z"/>
                </svg>
                <span class="menu-text">{{ $isSuperAdmin ? 'Super Admin' : 'Dashboard' }}</span>
            </a>
        </li>

        @if($isSuperAdmin)
        <li>
            <a href="{{ route('admin.tenants.index') }}" class="{{ request()->routeIs('admin.tenants*') ? 'active' : '' }}">
                <svg fill="currentColor" viewBox="0 0 24 24">
                    <path d="M12 2c-5.33 4.55-8 8.48-8 11.8 0 4.98 3.8 8.2 8 8.2s8-3.22 8-8.2c0-3.32-2.67-7.25-8-11.8zm0 18c-3.35 0-6-2.57-6-6.2 0-2.34 1.95-5.44 6-9.14 4.05 3.7 6 6.79 6 9.14 0 3.63-2.65 6.2-6 6.2zm3-6.2c0 1.66-1.34 3-3 3s-3-1.34-3-3 1.34-3 3-3 3 1.34 3 3z"/>
                </svg>
                <span class="menu-text">Agents</span>
            </a>
        </li>

        <li>
            <a href="{{ route('admin.blogs.index') }}" class="{{ request()->routeIs('admin.blogs*') ? 'active' : '' }}">
                <svg fill="currentColor" viewBox="0 0 24 24">
                    <path d="M19 3H5c-1.1 0-2 .9-2 2v14c0 1.1.9 2 2 2h14c1.1 0 2-.9 2-2V5c0-1.1-.9-2-2-2zm-5 9.5h5v2h-5v-2zm0 4h5v2h-5v-2zM4 5h14v8H4V5z"/>
                </svg>
                <span class="menu-text">Blogs</span>
            </a>
        </li>
        @endif

        @if($isSuperAdmin || ($user && $user->hasPermission('roles.view')))
        <li>
            <a href="{{ route($panelPrefix . '.roles') }}" class="{{ request()->routeIs('admin.roles*') || request()->routeIs('agent.roles*') || request()->routeIs('subagent.roles*') ? 'active' : '' }}">
                <svg fill="currentColor" viewBox="0 0 24 24">
                    <path d="M12 2c5.523 0 10 4.477 10 10s-4.477 10-10 10S2 17.523 2 12 6.477 2 12 2zm0 18c4.418 0 8-3.582 8-8s-3.582-8-8-8-8 3.582-8 8 3.582 8 8 8zm3.5-9c.829 0 1.5.671 1.5 1.5s-.671 1.5-1.5 1.5-1.5-.671-1.5-1.5.671-1.5 1.5-1.5zm-7 0c.829 0 1.5.671 1.5 1.5s-.671 1.5-1.5 1.5-1.5-.671-1.5-1.5.671-1.5 1.5-1.5z"/>
                </svg>
                <span class="menu-text">Roles</span>
            </a>
        </li>
        @endif

        @if($isSuperAdmin || ($user && $user->hasPermission('permissions.view')))
        <li>
            <a href="{{ route($panelPrefix . '.permissions') }}" class="{{ request()->routeIs('admin.permissions*') || request()->routeIs('agent.permissions*') || request()->routeIs('subagent.permissions*') ? 'active' : '' }}">
                <svg fill="currentColor" viewBox="0 0 24 24">
                    <path d="M12 1l-5.633 9H6v8h12v-8h-.367L12 1zm1 18h-2v-2h2v2zm3-4H8v-6h8v6z"/>
                </svg>
                <span class="menu-text">Permissions</span>
            </a>
        </li>
        @endif

        @if(!$isSuperAdmin && ($user && $user->hasPermission('managers.view')))
        <li>
            <a href="{{ route($panelPrefix . '.managers') }}" class="{{ request()->routeIs('admin.managers*') || request()->routeIs('agent.managers*') || request()->routeIs('subagent.managers*') ? 'active' : '' }}">
                <svg fill="currentColor" viewBox="0 0 24 24">
                    <path d="M16 11c1.66 0 2.99-1.34 2.99-3S17.66 5 16 5s-3 1.34-3 3 1.34 3 3 3zm-8 0c1.66 0 2.99-1.34 2.99-3S9.66 5 8 5C6.34 5 5 6.34 5 8s1.34 3 3 3zm0 2c-2.33 0-7 1.17-7 3.5V19h14v-2.5c0-2.33-4.67-3.5-7-3.5zm8 0c-.29 0-.62.02-.97.05 1.16.84 1.97 1.97 1.97 3.45V19h6v-2.5c0-2.33-4.67-3.5-7-3.5z"/>
                </svg>
                <span class="menu-text">Sub Agents</span>
            </a>
        </li>
        @endif
    </ul>
</aside>
<!-- SIDEBAR END -->