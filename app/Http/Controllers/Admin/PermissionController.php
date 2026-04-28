<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\System\Permission;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Str;

class PermissionController extends Controller
{
    /**
     * Display a listing of permissions.
     */
    public function index()
    {
        $user = Auth::user();
        $isAdmin = $user && $user->role_id == 1;
        
        if (!$isAdmin && (!$user || !$user->hasPermission('permissions.view'))) {
            abort(403, 'Unauthorized action.');
        }

        $permissions = Permission::orderBy('group')->orderBy('name')->get()->groupBy('group');
        
        return view('admin.permissions.index', compact('permissions'));
    }

    /**
     * Show the form for creating a new permission.
     */
    public function create()
    {
        $user = Auth::user();
        $isAdmin = $user && $user->role_id == 1;
        
        if (!$isAdmin && (!$user || !$user->hasPermission('permissions.create'))) {
            abort(403, 'Unauthorized action.');
        }

        return view('admin.permissions.create');
    }

    /**
     * Store a newly created permission.
     */
    public function store(Request $request)
    {
        $user = Auth::user();
        $isAdmin = $user && $user->role_id == 1;
        
        if (!$isAdmin && (!$user || !$user->hasPermission('permissions.create'))) {
            abort(403, 'Unauthorized action.');
        }

        $request->validate([
            'name' => 'required|string|max:255',
            'group' => 'required|string|max:255',
            'description' => 'nullable|string|max:500',
        ]);

        Permission::create([
            'name' => $request->name,
            'slug' => Str::slug($request->name),
            'group' => $request->group,
            'description' => $request->description,
        ]);

        return redirect()->route('admin.permissions')->with('success', 'Permission created successfully!');
    }

    /**
     * Show the form for editing the specified permission.
     */
    public function edit($id)
    {
        $user = Auth::user();
        $isAdmin = $user && $user->role_id == 1;
        
        if (!$isAdmin && (!$user || !$user->hasPermission('permissions.edit'))) {
            abort(403, 'Unauthorized action.');
        }

        $permission = Permission::findOrFail($id);
        
        return view('admin.permissions.edit', compact('permission'));
    }

    /**
     * Update the specified permission.
     */
    public function update(Request $request, $id)
    {
        $user = Auth::user();
        $isAdmin = $user && $user->role_id == 1;
        
        if (!$isAdmin && (!$user || !$user->hasPermission('permissions.edit'))) {
            abort(403, 'Unauthorized action.');
        }

        $permission = Permission::findOrFail($id);

        $request->validate([
            'name' => 'required|string|max:255',
            'group' => 'required|string|max:255',
            'description' => 'nullable|string|max:500',
        ]);

        $permission->update([
            'name' => $request->name,
            'slug' => Str::slug($request->name),
            'group' => $request->group,
            'description' => $request->description,
        ]);

        return redirect()->route('admin.permissions')->with('success', 'Permission updated successfully!');
    }

    /**
     * Remove the specified permission.
     */
    public function destroy($id)
    {
        $user = Auth::user();
        $isAdmin = $user && $user->role_id == 1;
        
        if (!$isAdmin && (!$user || !$user->hasPermission('permissions.delete'))) {
            abort(403, 'Unauthorized action.');
        }

        $permission = Permission::findOrFail($id);
        $permission->delete();

        return redirect()->route('admin.permissions')->with('success', 'Permission deleted successfully!');
    }
}
