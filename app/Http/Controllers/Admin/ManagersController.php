<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Users\User;
use App\Models\System\Permission;
use Illuminate\Support\Facades\Auth;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;

class ManagersController extends Controller
{
    /**
     * Show all managers and editors
     */
    public function index(Request $request)
    {
        $user = Auth::user();
        $isAdmin = $user && $user->role_id == 1;
        
        // Check if user has view permission
        if (!$isAdmin && (!$user || !$user->hasPermission('managers.view'))) {
            abort(403, 'Unauthorized action.');
        }
        
        $filter = $request->get('filter', 'all'); // all, manager, editor, deleted
        
        $allQuery = User::whereIn('role_id', [2, 3])->with('role');
        $managerQuery = User::where('role_id', 2)->with('role');
        $editorQuery = User::where('role_id', 3)->with('role');
        $deletedQuery = User::onlyTrashed()->whereIn('role_id', [2, 3])->with('role');
        
        $allCount = $allQuery->count();
        $managerCount = $managerQuery->count();
        $editorCount = $editorQuery->count();
        $deletedCount = $deletedQuery->count();
        
        $counts = [
            'all' => $allCount,
            'manager' => $managerCount,
            'editor' => $editorCount,
            'deleted' => $deletedCount,
        ];
        
        // Get data based on filter
        if ($filter === 'manager') {
            $managers = $managerQuery->orderBy('id')->get();
        } elseif ($filter === 'editor') {
            $managers = $editorQuery->orderBy('id')->get();
        } elseif ($filter === 'deleted') {
            $managers = $deletedQuery->orderBy('id')->get();
        } else {
            $managers = $allQuery->orderBy('role_id')->orderBy('id')->get();
        }
        
        return view('admin.managers.index', compact('managers', 'counts', 'filter'));
    }

    /**
     * Show form to add new manager/editor
     */
    public function create()
    {
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
        return view('admin.managers.form', compact('permissions'));
    }

    /**
     * Store new manager/editor
     */
    public function store(Request $request)
    {
        $request->validate([
            'role_id' => 'required|in:2,3', // Only Manager (2) or Editor (3)
            'first_name' => 'required|string|max:255',
            'last_name' => 'required|string|max:255',
            'email' => 'required|email|unique:users,email',
            'password' => 'required|string|min:8',
            'permissions' => 'nullable|array',
            'permissions.*' => 'exists:permissions,id',
        ]);

        $user = User::create([
            'first_name' => $request->first_name,
            'last_name' => $request->last_name,
            'email' => $request->email,
            'password' => Hash::make($request->password),
            'role_id' => $request->role_id,
            'email_verified_at' => now(), // Auto-verify for managers/editors
        ]);

        // Assign permissions directly to the user (user-specific permissions)
        if ($request->has('permissions')) {
            $user->userPermissions()->sync($request->permissions ?? []);
        }

        // Redirect to managers page only if current user is Admin (role_id = 1)
        $currentUser = Auth::user();
        if ($currentUser && $currentUser->role_id == 1) {
            return redirect()->route('admin.managers')->with('success', 'Manager/Editor created successfully!');
        }
        
        return redirect()->route('admin.dashboard')->with('success', 'Manager/Editor created successfully!');
    }

    /**
     * Show form to edit manager/editor
     */
    public function edit($id)
    {
        $manager = User::whereIn('role_id', [2, 3])->with(['role', 'userPermissions'])->findOrFail($id);
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
        // Get user-specific permissions (not role permissions)
        $managerPermissions = $manager->userPermissions->pluck('id')->toArray();
        
        return view('admin.managers.form', compact('manager', 'permissions', 'managerPermissions'));
    }

    /**
     * Update manager/editor
     */
    public function update(Request $request, $id)
    {
        $manager = User::whereIn('role_id', [2, 3])->with('role')->findOrFail($id);

        $validationRules = [
            'role_id' => 'required|in:2,3',
            'first_name' => 'required|string|max:255',
            'last_name' => 'required|string|max:255',
            'email' => 'required|email|unique:users,email,' . $id,
            'permissions' => 'nullable|array',
            'permissions.*' => 'exists:permissions,id',
        ];

        if ($request->filled('password')) {
            $validationRules['password'] = 'required|string|min:8';
        }

        $request->validate($validationRules);

        $oldRoleId = $manager->role_id;
        $newRoleId = $request->role_id;

        $manager->update([
            'first_name' => $request->first_name,
            'last_name' => $request->last_name,
            'email' => $request->email,
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

        // Redirect to managers page only if current user is Admin (role_id = 1)
        $currentUser = Auth::user();
        if ($currentUser && $currentUser->role_id == 1) {
            return redirect()->route('admin.managers')->with('success', 'Manager/Editor updated successfully!');
        }
        
        return redirect()->route('admin.dashboard')->with('success', 'Manager/Editor updated successfully!');
    }

    /**
     * Update manager/editor permissions only
     */
    public function updatePermissions(Request $request, $id)
    {
        $manager = User::whereIn('role_id', [2, 3])->findOrFail($id);

        $request->validate([
            'permissions' => 'nullable|array',
            'permissions.*' => 'exists:permissions,id',
        ]);

        // Update user-specific permissions (not role permissions)
        $manager->userPermissions()->sync($request->permissions ?? []);

        // Redirect to managers page only if current user is Admin (role_id = 1)
        $currentUser = Auth::user();
        if ($currentUser && $currentUser->role_id == 1) {
            return redirect()->route('admin.managers')->with('success', 'Permissions updated successfully!');
        }
        
        return redirect()->route('admin.dashboard')->with('success', 'Permissions updated successfully!');
    }

    /**
     * Delete manager/editor (soft delete)
     */
    public function destroy($id)
    {
        $manager = User::whereIn('role_id', [2, 3])->findOrFail($id);
        $manager->delete();

        // Redirect to managers page only if current user is Admin (role_id = 1)
        $currentUser = Auth::user();
        if ($currentUser && $currentUser->role_id == 1) {
            return redirect()->route('admin.managers')->with('success', 'Manager/Editor deleted successfully!');
        }
        
        return redirect()->route('admin.dashboard')->with('success', 'Manager/Editor deleted successfully!');
    }

    /**
     * Restore manager/editor
     */
    public function restore($id)
    {
        $manager = User::onlyTrashed()->whereIn('role_id', [2, 3])->findOrFail($id);
        $manager->restore();

        // Redirect to managers page only if current user is Admin (role_id = 1)
        $currentUser = Auth::user();
        if ($currentUser && $currentUser->role_id == 1) {
            return redirect()->route('admin.managers', ['filter' => 'deleted'])->with('success', 'Manager/Editor restored successfully!');
        }
        
        return redirect()->route('admin.dashboard')->with('success', 'Manager/Editor restored successfully!');
    }
}
