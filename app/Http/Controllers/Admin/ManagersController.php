<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Users\User;
use App\Models\System\Permission;
use App\Models\System\Role;
use Illuminate\Support\Facades\Auth;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;

class ManagersController extends Controller
{
    protected function routePrefixForUser($user): string
    {
        return match ($user?->user_type) {
            'super_admin' => 'admin',
            'tenant_admin' => 'agent',
            'sub_agent' => 'subagent',
            default => 'admin',
        };
    }

    protected function isSuperAdmin($user): bool
    {
        return (bool) ($user && $user->user_type === 'super_admin');
    }

    protected function baseSubAgentQuery($user)
    {
        $query = User::with('role')->where('user_type', 'sub_agent');

        if (!$this->isSuperAdmin($user)) {
            $query->where('tenant_id', $user->tenant_id);
        }

        return $query;
    }

    protected function roleOptionsForUser($user)
    {
        return Role::where('tenant_id', $user->tenant_id)
            ->orderBy('name')
            ->get();
    }

    protected function authorizeManagersAccess($user, string $permission): void
    {
        // Sub-agents are tenant-level management and should not be managed from super-admin area.
        if ($this->isSuperAdmin($user)) {
            abort(403, 'Sub-agent management is available under tenant admin scope.');
        }

        if ($user->hasPermission($permission)) {
            return;
        }

        abort(403, 'Unauthorized action.');
    }

    /**
     * Show all managers and editors
     */
    public function index(Request $request)
    {
        $user = Auth::user();
        $this->authorizeManagersAccess($user, 'managers.view');

        $baseQuery = $this->baseSubAgentQuery($user)->withTrashed();
        $managers = $baseQuery->orderBy('role_id')->orderBy('id')->get();

        $counts = [
            'all' => (clone $this->baseSubAgentQuery($user))->count(),
            'deleted' => (clone $this->baseSubAgentQuery($user))->onlyTrashed()->count(),
        ];

        return view('admin.managers.index', compact('managers', 'counts'));
    }

    /**
     * Show form to add new manager/editor
     */
    public function create()
    {
        $currentUser = Auth::user();
        $this->authorizeManagersAccess($currentUser, 'managers.create');

        // Exclude dashboard and managers permissions - dashboard is accessible by default to all admin roles
        // Order by sidebar order: Users, Subscriptions, Products/Services, Blogs, Events, Ads, Scheduler Logs
        $groupOrder = ['users' => 1, 'subscriptions' => 2, 'products-services' => 3, 'blogs' => 4, 'events' => 5, 'ads' => 6, 'scheduler-logs' => 7];
        
        // Get all permissions grouped by group
        $allPermissions = Permission::where('group', '!=', 'dashboard')
                                  ->where('group', '!=', 'managers')
                                  ->where('slug', '!=', 'dashboard.view')
                                  ->get()
                                  ->groupBy('group');
        
        // Helper function to get CRUD priority for sorting: Add (1) -> View (2) -> Edit (3) -> Delete (4) -> Restore (5)
        $getCrudPriority = function ($permission) {
            $slug = strtolower($permission->slug);
            $name = strtolower($permission->name);
            
            // Priority 1: Add/Create
            if (strpos($slug, '.create') !== false || preg_match('/\badd\s+/', $name)) {
                return 1;
            }
            
            // Priority 2: View/Filter (Filter comes after View)
            if (strpos($slug, 'filter') !== false || strpos($name, 'filter') !== false) {
                return 2.5;
            }
            if (strpos($slug, '.view') !== false || preg_match('/\bview\s+/', $name) || strpos($name, 'view/') !== false) {
                return 2;
            }
            
            // Priority 3: Edit (and related actions like Profile, Company, Password Reset)
            if (strpos($slug, '.edit') !== false || strpos($slug, '.profile') !== false || 
                strpos($slug, '.company') !== false || strpos($slug, '.password') !== false ||
                preg_match('/\bedit\s+/', $name) || strpos($name, 'profile') !== false || 
                strpos($name, 'company') !== false || strpos($name, 'password') !== false) {
                return 3;
            }
            
            // Priority 4: Delete
            if (strpos($slug, '.delete') !== false || preg_match('/\bdelete\s+/', $name)) {
                return 4;
            }
            
            // Priority 5: Restore
            if (strpos($slug, '.restore') !== false || preg_match('/\brestore\s+/', $name)) {
                return 5;
            }
            
            // Default to end if no pattern matches
            return 999;
        };
        
        // Reorder groups to match sidebar order and sort permissions within each group by CRUD order
        $orderedPermissions = [];
        foreach ($groupOrder as $group => $order) {
            if (isset($allPermissions[$group])) {
                $groupPerms = $allPermissions[$group]->sortBy($getCrudPriority)->values();
                $orderedPermissions[$group] = $groupPerms;
            }
        }
        
        // Also include any groups that are not in $groupOrder but exist in permissions
        foreach ($allPermissions as $group => $perms) {
            if (!isset($orderedPermissions[$group])) {
                $orderedPermissions[$group] = $perms->sortBy($getCrudPriority)->values();
            }
        }
        
        $permissions = $orderedPermissions;
        $roles = $this->roleOptionsForUser($currentUser);
        return view('admin.managers.form', compact('permissions', 'roles'));
    }

    /**
     * Store new manager/editor
     */
    public function store(Request $request)
    {
        $currentUser = Auth::user();
        $this->authorizeManagersAccess($currentUser, 'managers.create');

        $request->validate([
            'role_id' => 'required|exists:roles,id',
            'first_name' => 'required|string|max:255',
            'last_name' => 'required|string|max:255',
            'email' => 'required|email|unique:users,email',
            'password' => 'required|string|min:8',
            'designation' => 'nullable|string|max:255',
            'permissions' => 'nullable|array',
            'permissions.*' => 'exists:permissions,id',
        ]);

        $role = Role::findOrFail($request->role_id);
        if (!$this->isSuperAdmin($currentUser) && $role->tenant_id !== null && (int) $role->tenant_id !== (int) $currentUser->tenant_id) {
            abort(403, 'Unauthorized role selection.');
        }

        $user = User::create([
            'first_name' => $request->first_name,
            'last_name' => $request->last_name,
            'email' => $request->email,
            'password' => Hash::make($request->password),
            'role_id' => $request->role_id,
            'tenant_id' => $this->isSuperAdmin($currentUser) ? $role->tenant_id : $currentUser->tenant_id,
            'designation' => $request->designation,
            'user_type' => 'sub_agent',
            'email_verified_at' => now(), // Auto-verify for managers/editors
        ]);

        // Assign permissions directly to the user (user-specific permissions)
        if ($request->has('permissions')) {
            $user->userPermissions()->sync($request->permissions ?? []);
        }

        return redirect()->route($this->routePrefixForUser($currentUser) . '.managers')->with('success', 'Sub-agent created successfully!');
    }

    /**
     * Show form to edit manager/editor
     */
    public function edit($id)
    {
        $currentUser = Auth::user();
        $this->authorizeManagersAccess($currentUser, 'managers.edit');

        $manager = $this->baseSubAgentQuery($currentUser)->with(['role', 'userPermissions'])->findOrFail($id);
        // Exclude dashboard and managers permissions - dashboard is accessible by default to all admin roles
        // Order by sidebar order: Users, Subscriptions, Products/Services, Blogs, Events, Ads, Scheduler Logs
        $groupOrder = ['users' => 1, 'subscriptions' => 2, 'products-services' => 3, 'blogs' => 4, 'events' => 5, 'ads' => 6, 'scheduler-logs' => 7];
        
        // Get all permissions grouped by group
        $allPermissions = Permission::where('group', '!=', 'dashboard')
                                  ->where('group', '!=', 'managers')
                                  ->where('slug', '!=', 'dashboard.view')
                                  ->get()
                                  ->groupBy('group');
        
        // Helper function to get CRUD priority for sorting: Add (1) -> View (2) -> Edit (3) -> Delete (4) -> Restore (5)
        $getCrudPriority = function ($permission) {
            $slug = strtolower($permission->slug);
            $name = strtolower($permission->name);
            
            // Priority 1: Add/Create
            if (strpos($slug, '.create') !== false || preg_match('/\badd\s+/', $name)) {
                return 1;
            }
            
            // Priority 2: View/Filter (Filter comes after View)
            if (strpos($slug, 'filter') !== false || strpos($name, 'filter') !== false) {
                return 2.5;
            }
            if (strpos($slug, '.view') !== false || preg_match('/\bview\s+/', $name) || strpos($name, 'view/') !== false) {
                return 2;
            }
            
            // Priority 3: Edit (and related actions like Profile, Company, Password Reset)
            if (strpos($slug, '.edit') !== false || strpos($slug, '.profile') !== false || 
                strpos($slug, '.company') !== false || strpos($slug, '.password') !== false ||
                preg_match('/\bedit\s+/', $name) || strpos($name, 'profile') !== false || 
                strpos($name, 'company') !== false || strpos($name, 'password') !== false) {
                return 3;
            }
            
            // Priority 4: Delete
            if (strpos($slug, '.delete') !== false || preg_match('/\bdelete\s+/', $name)) {
                return 4;
            }
            
            // Priority 5: Restore
            if (strpos($slug, '.restore') !== false || preg_match('/\brestore\s+/', $name)) {
                return 5;
            }
            
            // Default to end if no pattern matches
            return 999;
        };
        
        // Reorder groups to match sidebar order and sort permissions within each group by CRUD order
        $orderedPermissions = [];
        foreach ($groupOrder as $group => $order) {
            if (isset($allPermissions[$group])) {
                $groupPerms = $allPermissions[$group]->sortBy($getCrudPriority)->values();
                $orderedPermissions[$group] = $groupPerms;
            }
        }
        
        // Also include any groups that are not in $groupOrder but exist in permissions
        foreach ($allPermissions as $group => $perms) {
            if (!isset($orderedPermissions[$group])) {
                $orderedPermissions[$group] = $perms->sortBy($getCrudPriority)->values();
            }
        }
        
        $permissions = $orderedPermissions;
        $roles = $this->roleOptionsForUser($currentUser);
        // Get user-specific permissions (not role permissions)
        $managerPermissions = $manager->userPermissions->pluck('id')->toArray();
        
        return view('admin.managers.form', compact('manager', 'permissions', 'managerPermissions', 'roles'));
    }

    /**
     * Update manager/editor
     */
    public function update(Request $request, $id)
    {
        $currentUser = Auth::user();
        $this->authorizeManagersAccess($currentUser, 'managers.edit');

        $manager = $this->baseSubAgentQuery($currentUser)->with('role')->findOrFail($id);

        $validationRules = [
            'role_id' => 'required|exists:roles,id',
            'first_name' => 'required|string|max:255',
            'last_name' => 'required|string|max:255',
            'email' => 'required|email|unique:users,email,' . $id,
            'designation' => 'nullable|string|max:255',
            'permissions' => 'nullable|array',
            'permissions.*' => 'exists:permissions,id',
        ];

        if ($request->filled('password')) {
            $validationRules['password'] = 'required|string|min:8';
        }

        $request->validate($validationRules);

        $role = Role::findOrFail($request->role_id);
        if (!$this->isSuperAdmin($currentUser) && $role->tenant_id !== null && (int) $role->tenant_id !== (int) $currentUser->tenant_id) {
            abort(403, 'Unauthorized role selection.');
        }

        $newRoleId = $request->role_id;

        $manager->update([
            'first_name' => $request->first_name,
            'last_name' => $request->last_name,
            'email' => $request->email,
            'designation' => $request->designation,
            'role_id' => $newRoleId,
        ]);

        if ($request->filled('password')) {
            $manager->update([
                'password' => Hash::make($request->password),
            ]);
        }

        // Update user-specific permissions (not role permissions)
        if ($request->has('permissions')) {
            $manager->userPermissions()->sync($request->permissions ?? []);
        }

        return redirect()->route($this->routePrefixForUser($currentUser) . '.managers')->with('success', 'Sub-agent updated successfully!');
    }

    /**
     * Update manager/editor permissions only
     */
    public function updatePermissions(Request $request, $id)
    {
        $currentUser = Auth::user();
        $this->authorizeManagersAccess($currentUser, 'managers.edit');
        $manager = $this->baseSubAgentQuery($currentUser)->findOrFail($id);

        $request->validate([
            'permissions' => 'nullable|array',
            'permissions.*' => 'exists:permissions,id',
        ]);

        // Update user-specific permissions (not role permissions)
        $manager->userPermissions()->sync($request->permissions ?? []);

        return redirect()->route($this->routePrefixForUser($currentUser) . '.managers')->with('success', 'Permissions updated successfully!');
    }

    /**
     * Delete manager/editor (soft delete)
     */
    public function destroy($id)
    {
        $currentUser = Auth::user();
        $this->authorizeManagersAccess($currentUser, 'managers.delete');
        $manager = $this->baseSubAgentQuery($currentUser)->findOrFail($id);
        $manager->delete();

        return redirect()->route($this->routePrefixForUser($currentUser) . '.managers')->with('success', 'Sub-agent deleted successfully!');
    }

    /**
     * Restore manager/editor
     */
    public function restore($id)
    {
        $currentUser = Auth::user();
        $this->authorizeManagersAccess($currentUser, 'managers.restore');
        $manager = $this->baseSubAgentQuery($currentUser)->onlyTrashed()->findOrFail($id);
        $manager->restore();

        return redirect()->route($this->routePrefixForUser($currentUser) . '.managers')->with('success', 'Sub-agent restored successfully!');
    }
}
