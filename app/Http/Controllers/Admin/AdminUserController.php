<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\System\Role;
use App\Models\System\Tenant;
use App\Models\Users\User;
use App\Services\AdminUserService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class AdminUserController extends Controller
{
    public function __construct(
        protected AdminUserService $adminUserService
    ) {}

    protected function ensureSuperAdmin(): void
    {
        $user = Auth::user();
        if (! $user || $user->user_type !== 'super_admin') {
            abort(403, 'Only super admin can manage users here.');
        }
    }

    public function index(Request $request)
    {
        $this->ensureSuperAdmin();

        $filter = $request->get('filter', 'all');
        $query = User::with(['role', 'tenant'])->orderByDesc('id');

        if ($filter === 'deleted') {
            $query->onlyTrashed();
        }

        $users = $query->get();

        $counts = [
            'all' => User::count(),
            'deleted' => User::onlyTrashed()->count(),
        ];

        return view('admin.users.index', compact('users', 'filter', 'counts'));
    }

    public function create()
    {
        $this->ensureSuperAdmin();

        $roles = Role::with('tenant')->orderBy('tenant_id')->orderBy('name')->get();
        $tenants = Tenant::where('status', 'approved')->orderBy('name')->get();
        $userTypes = [
            'public' => 'Public user',
            'tenant_admin' => 'Agent admin',
            'sub_agent' => 'Sub agent',
        ];

        return view('admin.users.create', compact('roles', 'tenants', 'userTypes'));
    }

    public function store(Request $request)
    {
        $this->ensureSuperAdmin();

        $request->validate([
            'first_name' => 'required|string|max:255',
            'last_name' => 'required|string|max:255',
            'email' => 'required|email|unique:users,email',
            'username' => 'required|string|max:50|regex:/^[a-zA-Z0-9._-]+$/|unique:users,username',
            'user_type' => 'required|in:public,tenant_admin,sub_agent',
            'phone' => 'nullable|string|max:50',
            'country' => 'nullable|string|max:100',
            'tenant_id' => 'nullable|exists:tenants,id',
            'role_id' => 'required|exists:roles,id',
            'password' => 'required|string|min:8|confirmed',
            'photo' => 'nullable|image|mimes:jpg,jpeg,png,webp|max:2048',
            'agent_document' => 'nullable|file|mimes:pdf,jpg,jpeg,png|max:5120',
        ]);

        $this->adminUserService->createFromAdminRequest($request);

        return redirect()->route('admin.users')->with('success', 'User created successfully.');
    }

    public function show($id)
    {
        $this->ensureSuperAdmin();

        $targetUser = User::with(['role', 'tenant'])->findOrFail($id);

        return view('admin.users.show', compact('targetUser'));
    }

    public function edit($id)
    {
        $this->ensureSuperAdmin();

        $targetUser = User::findOrFail($id);
        $roles = Role::with('tenant')->orderBy('tenant_id')->orderBy('name')->get();
        $tenants = Tenant::where('status', 'approved')->orderBy('name')->get();
        $userTypes = [
            'public' => 'Public user',
            'tenant_admin' => 'Agent admin',
            'sub_agent' => 'Sub agent',
        ];

        return view('admin.users.edit', compact('targetUser', 'roles', 'tenants', 'userTypes'));
    }

    public function update(Request $request, $id)
    {
        $this->ensureSuperAdmin();

        $targetUser = User::findOrFail($id);

        $request->validate([
            'first_name' => 'required|string|max:255',
            'last_name' => 'required|string|max:255',
            'email' => 'required|email|unique:users,email,'.$targetUser->id,
            'username' => 'required|string|max:50|regex:/^[a-zA-Z0-9._-]+$/|unique:users,username,'.$targetUser->id,
            'user_type' => 'required|in:public,tenant_admin,sub_agent',
            'phone' => 'nullable|string|max:50',
            'country' => 'nullable|string|max:100',
            'tenant_id' => 'nullable|exists:tenants,id',
            'role_id' => 'required|exists:roles,id',
            'password' => 'nullable|string|min:8|confirmed',
            'photo' => 'nullable|image|mimes:jpg,jpeg,png,webp|max:2048',
            'agent_document' => 'nullable|file|mimes:pdf,jpg,jpeg,png|max:5120',
        ]);

        $this->adminUserService->updateFromAdminRequest($targetUser, $request);

        return redirect()->route('admin.users')->with('success', 'User updated successfully.');
    }

    public function destroy($id)
    {
        $this->ensureSuperAdmin();

        $targetUser = User::findOrFail($id);

        if ($targetUser->id === Auth::id()) {
            return redirect()->route('admin.users')->with('error', 'You cannot delete your own account.');
        }

        $targetUser->delete();

        return redirect()->route('admin.users')->with('success', 'User deleted successfully.');
    }

    public function restore($id)
    {
        $this->ensureSuperAdmin();

        $targetUser = User::onlyTrashed()->findOrFail($id);
        $targetUser->restore();

        return redirect()->route('admin.users', ['filter' => 'deleted'])->with('success', 'User restored successfully.');
    }
}
