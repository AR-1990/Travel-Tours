<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\System\Role;
use App\Models\System\Permission;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Str;

class RoleController extends Controller
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

    protected function roleQueryForUser($user)
    {
        $query = Role::with('permissions')->orderBy('id');

        if ($this->isSuperAdmin($user)) {
            // Super admin sees only platform/global roles.
            return $query->whereNull('tenant_id')
                ->where('slug', '!=', 'admin');
        }

        // Agent/Sub-agent sees only their own tenant roles.
        return $query->where('tenant_id', $user->tenant_id);
    }

    protected function authorizeRoleAccess($user, Role $role): void
    {
        if ($this->isSuperAdmin($user)) {
            if ($role->tenant_id !== null) {
                abort(403, 'Unauthorized action.');
            }
            return;
        }

        if ((int) $role->tenant_id !== (int) $user->tenant_id) {
            abort(403, 'Unauthorized action.');
        }
    }

    /**
     * Display a listing of roles.
     */
    public function index()
    {
        $user = Auth::user();
        /** @var \App\Models\Users\User $user */
        $isAdmin = $this->isSuperAdmin($user);
        
        if (!$isAdmin && (!$user || !$user->hasPermission('roles.view'))) {
            abort(403, 'Unauthorized action.');
        }

        $roles = $this->roleQueryForUser($user)->get();
        
        return view('admin.roles.index', compact('roles'));
    }

    /**
     * Show the form for creating a new role.
     */
    public function create()
    {
        $user = Auth::user();
        /** @var \App\Models\Users\User $user */
        $isAdmin = $this->isSuperAdmin($user);
        
        if (!$isAdmin && (!$user || !$user->hasPermission('roles.create'))) {
            abort(403, 'Unauthorized action.');
        }

        $permissions = Permission::orderBy('group')->orderBy('name')->get()->groupBy('group');
        
        return view('admin.roles.create', compact('permissions'));
    }

    /**
     * Store a newly created role.
     */
    public function store(Request $request)
    {
        $user = Auth::user();
        /** @var \App\Models\Users\User $user */
        $isAdmin = $this->isSuperAdmin($user);
        
        if (!$isAdmin && (!$user || !$user->hasPermission('roles.create'))) {
            abort(403, 'Unauthorized action.');
        }

        $request->validate([
            'name' => 'required|string|max:255',
            'description' => 'nullable|string|max:500',
            'category' => 'nullable|string|max:100',
            'permissions' => 'nullable|array',
            'permissions.*' => 'exists:permissions,id',
        ]);

        $slug = Str::slug($request->name);
        $tenantId = $isAdmin ? null : $user->tenant_id;
        $roleExists = Role::where('slug', $slug)->where('tenant_id', $tenantId)->exists();
        if ($roleExists) {
            return back()->withErrors(['name' => 'A role with this name already exists in this scope.'])->withInput();
        }

        $role = Role::create([
            'tenant_id' => $tenantId,
            'name' => $request->name,
            'slug' => $slug,
            'category' => $request->category,
            'description' => $request->description,
            'is_system' => false,
        ]);

        if ($request->has('permissions')) {
            $role->permissions()->sync($request->permissions);
        }

        return redirect()->route($this->routePrefixForUser($user) . '.roles')->with('success', 'Role created successfully!');
    }

    /**
     * Display the specified role.
     */
    public function show($id)
    {
        $user = Auth::user();
        /** @var \App\Models\Users\User $user */
        $isAdmin = $this->isSuperAdmin($user);
        
        if (!$isAdmin && (!$user || !$user->hasPermission('roles.view'))) {
            abort(403, 'Unauthorized action.');
        }

        $role = Role::with('permissions', 'users')->findOrFail($id);
        $this->authorizeRoleAccess($user, $role);
        
        return view('admin.roles.show', compact('role'));
    }

    /**
     * Show the form for editing the specified role.
     */
    public function edit($id)
    {
        $user = Auth::user();
        /** @var \App\Models\Users\User $user */
        $isAdmin = $this->isSuperAdmin($user);
        
        if (!$isAdmin && (!$user || !$user->hasPermission('roles.edit'))) {
            abort(403, 'Unauthorized action.');
        }

        $role = Role::with('permissions')->findOrFail($id);
        $this->authorizeRoleAccess($user, $role);
        $permissions = Permission::orderBy('group')->orderBy('name')->get()->groupBy('group');
        
        return view('admin.roles.edit', compact('role', 'permissions'));
    }

    /**
     * Update the specified role.
     */
    public function update(Request $request, $id)
    {
        $user = Auth::user();
        /** @var \App\Models\Users\User $user */
        $isAdmin = $this->isSuperAdmin($user);
        
        if (!$isAdmin && (!$user || !$user->hasPermission('roles.edit'))) {
            abort(403, 'Unauthorized action.');
        }

        $role = Role::findOrFail($id);
        $this->authorizeRoleAccess($user, $role);

        $request->validate([
            'name' => 'required|string|max:255',
            'description' => 'nullable|string|max:500',
            'category' => 'nullable|string|max:100',
            'permissions' => 'nullable|array',
            'permissions.*' => 'exists:permissions,id',
        ]);

        $slug = Str::slug($request->name);
        $tenantId = $role->tenant_id;
        $roleExists = Role::where('slug', $slug)
            ->where('tenant_id', $tenantId)
            ->where('id', '!=', $role->id)
            ->exists();
        if ($roleExists) {
            return back()->withErrors(['name' => 'A role with this name already exists in this scope.'])->withInput();
        }

        $role->update([
            'name' => $request->name,
            'slug' => $slug,
            'category' => $request->category,
            'description' => $request->description,
        ]);

        if ($request->has('permissions')) {
            $role->permissions()->sync($request->permissions);
        } else {
            $role->permissions()->detach();
        }

        return redirect()->route($this->routePrefixForUser($user) . '.roles')->with('success', 'Role updated successfully!');
    }

    /**
     * Remove the specified role.
     */
    public function destroy($id)
    {
        $user = Auth::user();
        /** @var \App\Models\Users\User $user */
        $isAdmin = $this->isSuperAdmin($user);
        
        if (!$isAdmin && (!$user || !$user->hasPermission('roles.delete'))) {
            abort(403, 'Unauthorized action.');
        }

        $role = Role::findOrFail($id);
        $this->authorizeRoleAccess($user, $role);

        // Prevent deleting protected/system roles
        if ($role->is_system) {
            return redirect()->route($this->routePrefixForUser($user) . '.roles')->with('error', 'Cannot delete a protected system role.');
        }

        $role->delete();

        return redirect()->route($this->routePrefixForUser($user) . '.roles')->with('success', 'Role deleted successfully!');
    }

    /**
     * Update permissions for a role.
     */
    public function updatePermissions(Request $request, $id)
    {
        $user = Auth::user();
        /** @var \App\Models\Users\User $user */
        $isAdmin = $this->isSuperAdmin($user);
        
        if (!$isAdmin && (!$user || !$user->hasPermission('roles.edit'))) {
            abort(403, 'Unauthorized action.');
        }

        $role = Role::findOrFail($id);
        $this->authorizeRoleAccess($user, $role);

        $request->validate([
            'permissions' => 'nullable|array',
            'permissions.*' => 'exists:permissions,id',
        ]);

        $role->permissions()->sync($request->permissions ?? []);

        return redirect()->route($this->routePrefixForUser($user) . '.roles')->with('success', 'Permissions updated successfully!');
    }
}
