<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Users\User;
use App\Models\System\Role;
use Illuminate\Support\Facades\Auth;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

class AdminUserController extends Controller
{
    /**
     * Display a listing of users.
     */
    public function index(Request $request)
    {
        $user = Auth::user();
        $isAdmin = $user && $user->role_id == 1;
        
        if (!$isAdmin && (!$user || !$user->hasPermission('users.view'))) {
            abort(403, 'Unauthorized action.');
        }

        $filter = $request->get('filter', 'all');
        
        $query = User::where('role_id', 4)->with('role');
        
        if ($filter === 'deleted') {
            $query->onlyTrashed();
        }
        
        $users = $query->orderByDesc('id')->get();
        
        $counts = [
            'all' => User::where('role_id', 4)->whereNull('deleted_at')->count(),
            'deleted' => User::where('role_id', 4)->onlyTrashed()->count(),
        ];
        
        return view('admin.users.index', compact('users', 'counts', 'filter'));
    }

    /**
     * Show the form for creating a new user.
     */
    public function create()
    {
        $user = Auth::user();
        $isAdmin = $user && $user->role_id == 1;
        
        if (!$isAdmin && (!$user || !$user->hasPermission('users.create'))) {
            abort(403, 'Unauthorized action.');
        }

        $roles = Role::where('id', 4)->get(); // Only regular user role
        
        return view('admin.users.create', compact('roles'));
    }

    /**
     * Store a newly created user.
     */
    public function store(Request $request)
    {
        $user = Auth::user();
        $isAdmin = $user && $user->role_id == 1;
        
        if (!$isAdmin && (!$user || !$user->hasPermission('users.create'))) {
            abort(403, 'Unauthorized action.');
        }

        $request->validate([
            'first_name' => 'required|string|max:255',
            'last_name' => 'required|string|max:255',
            'email' => 'required|email|unique:users,email',
            'password' => 'required|string|min:8',
            'role_id' => 'required|exists:roles,id',
        ]);

        User::create([
            'first_name' => $request->first_name,
            'last_name' => $request->last_name,
            'email' => $request->email,
            'password' => Hash::make($request->password),
            'role_id' => $request->role_id,
            'email_verified_at' => now(),
        ]);

        return redirect()->route('admin.users')->with('success', 'User created successfully!');
    }

    /**
     * Display the specified user.
     */
    public function show($id)
    {
        $user = Auth::user();
        $isAdmin = $user && $user->role_id == 1;
        
        if (!$isAdmin && (!$user || !$user->hasPermission('users.view'))) {
            abort(403, 'Unauthorized action.');
        }

        $targetUser = User::with('role')->findOrFail($id);
        
        return view('admin.users.show', compact('targetUser'));
    }

    /**
     * Show the form for editing the specified user.
     */
    public function edit($id)
    {
        $user = Auth::user();
        $isAdmin = $user && $user->role_id == 1;
        
        if (!$isAdmin && (!$user || !$user->hasPermission('users.edit'))) {
            abort(403, 'Unauthorized action.');
        }

        $targetUser = User::findOrFail($id);
        $roles = Role::where('id', 4)->get();
        
        return view('admin.users.edit', compact('targetUser', 'roles'));
    }

    /**
     * Update the specified user.
     */
    public function update(Request $request, $id)
    {
        $user = Auth::user();
        $isAdmin = $user && $user->role_id == 1;
        
        if (!$isAdmin && (!$user || !$user->hasPermission('users.edit'))) {
            abort(403, 'Unauthorized action.');
        }

        $targetUser = User::findOrFail($id);

        $request->validate([
            'first_name' => 'required|string|max:255',
            'last_name' => 'required|string|max:255',
            'email' => 'required|email|unique:users,email,' . $id,
            'password' => 'nullable|string|min:8',
            'role_id' => 'required|exists:roles,id',
        ]);

        $data = [
            'first_name' => $request->first_name,
            'last_name' => $request->last_name,
            'email' => $request->email,
            'role_id' => $request->role_id,
        ];

        if ($request->filled('password')) {
            $data['password'] = Hash::make($request->password);
        }

        $targetUser->update($data);

        return redirect()->route('admin.users')->with('success', 'User updated successfully!');
    }

    /**
     * Remove the specified user.
     */
    public function destroy($id)
    {
        $user = Auth::user();
        $isAdmin = $user && $user->role_id == 1;
        
        if (!$isAdmin && (!$user || !$user->hasPermission('users.delete'))) {
            abort(403, 'Unauthorized action.');
        }

        $targetUser = User::findOrFail($id);
        $targetUser->delete();

        return redirect()->route('admin.users')->with('success', 'User deleted successfully!');
    }

    /**
     * Restore a soft-deleted user.
     */
    public function restore($id)
    {
        $user = Auth::user();
        $isAdmin = $user && $user->role_id == 1;
        
        if (!$isAdmin && (!$user || !$user->hasPermission('users.restore'))) {
            abort(403, 'Unauthorized action.');
        }

        $targetUser = User::onlyTrashed()->findOrFail($id);
        $targetUser->restore();

        return redirect()->route('admin.users')->with('success', 'User restored successfully!');
    }
}
