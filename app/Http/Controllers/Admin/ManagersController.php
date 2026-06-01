<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\System\Role;
use App\Models\Users\User;
use App\Services\SubAgentService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class ManagersController extends Controller
{
    public function __construct(
        protected SubAgentService $subAgentService
    ) {}

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
        return $this->subAgentService->isSuperAdmin($user);
    }

    protected function baseSubAgentQuery($user)
    {
        $query = User::with('role')->where('user_type', 'sub_agent');

        if (! $this->isSuperAdmin($user)) {
            $query->where('tenant_id', $user->tenant_id);
        }

        return $query;
    }

    protected function authorizeManagersAccess($user, string $permission): void
    {
        if ($this->isSuperAdmin($user)) {
            abort(403, 'Sub-agent management is available under tenant admin scope.');
        }

        if ($user->hasPermission($permission)) {
            return;
        }

        abort(403, 'Unauthorized action.');
    }

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

    public function create()
    {
        $currentUser = Auth::user();
        $this->authorizeManagersAccess($currentUser, 'managers.create');

        $permissions = $this->subAgentService->buildOrderedPermissionsForForm();
        $roles = $this->subAgentService->roleOptionsForTenant($currentUser);

        return view('admin.managers.form', compact('permissions', 'roles'));
    }

    public function store(Request $request)
    {
        $currentUser = Auth::user();
        $this->authorizeManagersAccess($currentUser, 'managers.create');

        $request->validate([
            'role_id' => 'required|exists:roles,id',
            'first_name' => 'required|string|max:255',
            'last_name' => 'required|string|max:255',
            'email' => 'required|email|unique:users,email',
            'username' => 'nullable|string|max:50|regex:/^[a-zA-Z0-9._-]+$/|unique:users,username',
            'phone' => 'nullable|string|max:50',
            'country' => 'nullable|string|max:100',
            'password' => 'required|string|min:8|confirmed',
            'designation' => 'nullable|string|max:255',
            'permissions' => 'nullable|array',
            'permissions.*' => 'exists:permissions,id',
            'photo' => 'nullable|image|mimes:jpg,jpeg,png,webp|max:2048',
            'agent_document' => 'nullable|file|mimes:pdf,jpg,jpeg,png|max:5120',
        ]);

        $role = Role::findOrFail($request->role_id);
        if (! $this->isSuperAdmin($currentUser) && $role->tenant_id !== null && (int) $role->tenant_id !== (int) $currentUser->tenant_id) {
            abort(403, 'Unauthorized role selection.');
        }

        $this->subAgentService->createSubAgent($currentUser, $request, $role);

        return redirect()->route($this->routePrefixForUser($currentUser).'.managers')->with('success', 'Sub-agent created successfully!');
    }

    public function edit($id)
    {
        $currentUser = Auth::user();
        $this->authorizeManagersAccess($currentUser, 'managers.edit');

        $manager = $this->baseSubAgentQuery($currentUser)->with(['role', 'userPermissions'])->findOrFail($id);

        $permissions = $this->subAgentService->buildOrderedPermissionsForForm();
        $roles = $this->subAgentService->roleOptionsForTenant($currentUser);
        $managerPermissions = $manager->userPermissions->pluck('id')->toArray();

        return view('admin.managers.form', compact('manager', 'permissions', 'managerPermissions', 'roles'));
    }

    public function update(Request $request, $id)
    {
        $currentUser = Auth::user();
        $this->authorizeManagersAccess($currentUser, 'managers.edit');

        $manager = $this->baseSubAgentQuery($currentUser)->with('role')->findOrFail($id);

        $validationRules = [
            'role_id' => 'required|exists:roles,id',
            'first_name' => 'required|string|max:255',
            'last_name' => 'required|string|max:255',
            'email' => 'required|email|unique:users,email,'.$id,
            'username' => 'nullable|string|max:50|regex:/^[a-zA-Z0-9._-]+$/|unique:users,username,'.$id,
            'phone' => 'nullable|string|max:50',
            'country' => 'nullable|string|max:100',
            'designation' => 'nullable|string|max:255',
            'permissions' => 'nullable|array',
            'permissions.*' => 'exists:permissions,id',
            'photo' => 'nullable|image|mimes:jpg,jpeg,png,webp|max:2048',
            'agent_document' => 'nullable|file|mimes:pdf,jpg,jpeg,png|max:5120',
        ];

        if ($request->filled('password')) {
            $validationRules['password'] = 'required|string|min:8|confirmed';
        }

        $request->validate($validationRules);

        $role = Role::findOrFail($request->role_id);
        if (! $this->isSuperAdmin($currentUser) && $role->tenant_id !== null && (int) $role->tenant_id !== (int) $currentUser->tenant_id) {
            abort(403, 'Unauthorized role selection.');
        }

        $this->subAgentService->updateSubAgent($currentUser, $manager, $request, $role);

        return redirect()->route($this->routePrefixForUser($currentUser).'.managers')->with('success', 'Sub-agent updated successfully!');
    }

    public function updatePermissions(Request $request, $id)
    {
        $currentUser = Auth::user();
        $this->authorizeManagersAccess($currentUser, 'managers.edit');
        $manager = $this->baseSubAgentQuery($currentUser)->findOrFail($id);

        $request->validate([
            'permissions' => 'nullable|array',
            'permissions.*' => 'exists:permissions,id',
        ]);

        $this->subAgentService->syncDirectPermissions($manager, $request);

        return redirect()->route($this->routePrefixForUser($currentUser).'.managers')->with('success', 'Permissions updated successfully!');
    }

    public function destroy($id)
    {
        $currentUser = Auth::user();
        $this->authorizeManagersAccess($currentUser, 'managers.delete');
        $manager = $this->baseSubAgentQuery($currentUser)->findOrFail($id);
        $manager->delete();

        return redirect()->route($this->routePrefixForUser($currentUser).'.managers')->with('success', 'Sub-agent deleted successfully!');
    }

    public function restore($id)
    {
        $currentUser = Auth::user();
        $this->authorizeManagersAccess($currentUser, 'managers.restore');
        $manager = $this->baseSubAgentQuery($currentUser)->onlyTrashed()->findOrFail($id);
        $manager->restore();

        return redirect()->route($this->routePrefixForUser($currentUser).'.managers')->with('success', 'Sub-agent restored successfully!');
    }
}
