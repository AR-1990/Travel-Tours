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
    /**
     * Display a listing of roles.
     */
    public function index()
    {
        $user = Auth::user();
        $isAdmin = $user && $user->role_id == 1;
        
        if (!$isAdmin && (!$user || !$user->hasPermission('roles.view'))) {
            abort(403, 'Unauthorized action.');
        }

        $roles = Role::with('permissions')->orderBy('id')->get();
        
        return view('admin.roles.index', compact('roles'));
    }

    /**
     * Show the form for creating a new role.
     */
    public function create()
    {
        $user = Auth::user();
        $isAdmin = $user && $user->role_id == 1;
        
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
        $isAdmin = $user && $user->role_id == 1;
        
        if (!$isAdmin && (!$user || !$user->hasPermission('roles.create'))) {
            abort(403, 'Unauthorized action.');
        }

        $request->validate([
            'name' => 'required|string|max:255|unique:roles,name',
            'description' => 'nullable|string|max:500',
            'permissions' => 'nullable|array',
            'permissions.*' => 'exists:permissions,id',
        ]);

        $role = Role::create([
            'name' => $request->name,
            'slug' => Str::slug($request->name),
            'description' => $request->description,
        ]);

        if ($request->has('permissions')) {
            $role->permissions()->sync($request->permissions);
        }

        return redirect()->route('admin.roles')->with('success', 'Role created successfully!');
    }

    /**
     * Display the specified role.
     */
    public function show($id)
    {
        $user = Auth::user();
        $isAdmin = $user && $user->role_id == 1;
        
        if (!$isAdmin && (!$user || !$user->hasPermission('roles.view'))) {
            abort(403, 'Unauthorized action.');
        }

        $role = Role::with('permissions', 'users')->findOrFail($id);
        
        return view('admin.roles.show', compact('role'));
    }

    /**
     * Show the form for editing the specified role.
     */
    public function edit($id)
    {
        $user = Auth::user();
        $isAdmin = $user && $user->role_id == 1;
        
        if (!$isAdmin && (!$user || !$user->hasPermission('roles.edit'))) {
            abort(403, 'Unauthorized action.');
        }

        $role = Role::with('permissions')->findOrFail($id);
        $permissions = Permission::orderBy('group')->orderBy('name')->get()->groupBy('group');
        
        return view('admin.roles.edit', compact('role', 'permissions'));
    }

    /**
     * Update the specified role.
     */
    public function update(Request $request, $id)
    {
        $user = Auth::user();
        $isAdmin = $user && $user->role_id == 1;
        
        if (!$isAdmin && (!$user || !$user->hasPermission('roles.edit'))) {
            abort(403, 'Unauthorized action.');
        }

        $role = Role::findOrFail($id);

        $request->validate([
            'name' => 'required|string|max:255|unique:roles,name,' . $id,
            'description' => 'nullable|string|max:500',
            'permissions' => 'nullable|array',
            'permissions.*' => 'exists:permissions,id',
        ]);

        $role->update([
            'name' => $request->name,
            'slug' => Str::slug($request->name),
            'description' => $request->description,
        ]);

        if ($request->has('permissions')) {
            $role->permissions()->sync($request->permissions);
        } else {
            $role->permissions()->detach();
        }

        return redirect()->route('admin.roles')->with('success', 'Role updated successfully!');
    }

    /**
     * Remove the specified role.
     */
    public function destroy($id)
    {
        $user = Auth::user();
        $isAdmin = $user && $user->role_id == 1;
        
        if (!$isAdmin && (!$user || !$user->hasPermission('roles.delete'))) {
            abort(403, 'Unauthorized action.');
        }

        $role = Role::findOrFail($id);

        // Prevent deleting admin role
        if ($role->id == 1) {
            return redirect()->route('admin.roles')->with('error', 'Cannot delete the Admin role.');
        }

        $role->delete();

        return redirect()->route('admin.roles')->with('success', 'Role deleted successfully!');
    }

    /**
     * Update permissions for a role.
     */
    public function updatePermissions(Request $request, $id)
    {
        $user = Auth::user();
        $isAdmin = $user && $user->role_id == 1;
        
        if (!$isAdmin && (!$user || !$user->hasPermission('roles.edit'))) {
            abort(403, 'Unauthorized action.');
        }

        $role = Role::findOrFail($id);

        $request->validate([
            'permissions' => 'nullable|array',
            'permissions.*' => 'exists:permissions,id',
        ]);

        $role->permissions()->sync($request->permissions ?? []);

        return redirect()->route('admin.roles')->with('success', 'Permissions updated successfully!');
    }
}
