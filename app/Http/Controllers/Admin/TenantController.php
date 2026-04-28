<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\System\Role;
use App\Models\System\Tenant;
use App\Models\Users\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

class TenantController extends Controller
{
    protected function authorizeSuperAdmin(): User
    {
        /** @var User $user */
        $user = Auth::user();
        if (!$user || $user->user_type !== 'super_admin') {
            abort(403, 'Only super admin can manage tenants.');
        }

        return $user;
    }

    public function index()
    {
        $this->authorizeSuperAdmin();
        $tenants = Tenant::withCount([
            'users as sub_agents_count' => function ($q) {
                $q->where('user_type', 'sub_agent');
            },
        ])->latest()->get();

        return view('admin.tenants.index', compact('tenants'));
    }

    public function show($tenantId)
    {
        $this->authorizeSuperAdmin();

        $tenant = Tenant::with(['users.role'])->findOrFail($tenantId);

        $tenantAdmins = $tenant->users
            ->where('user_type', 'tenant_admin')
            ->values();

        $subAgents = $tenant->users
            ->where('user_type', 'sub_agent')
            ->values();

        return view('admin.tenants.show', compact('tenant', 'tenantAdmins', 'subAgents'));
    }

    public function store(Request $request)
    {
        $this->authorizeSuperAdmin();

        $request->validate([
            'tenant_name' => 'required|string|max:255',
            'tenant_email' => 'nullable|email|max:255',
            'tenant_phone' => 'nullable|string|max:50',
            'admin_first_name' => 'required|string|max:255',
            'admin_last_name' => 'required|string|max:255',
            'admin_email' => 'required|email|unique:users,email',
            'admin_password' => 'required|string|min:8',
        ]);

        $tenantSlug = Str::slug($request->tenant_name) . '-' . Str::lower(Str::random(5));

        $tenant = Tenant::create([
            'name' => $request->tenant_name,
            'slug' => $tenantSlug,
            'email' => $request->tenant_email,
            'phone' => $request->tenant_phone,
            'is_active' => true,
            'status' => 'approved',
            'approved_at' => now(),
            'approved_by' => Auth::id(),
        ]);

        $adminRole = Role::firstOrCreate(
            ['slug' => 'admin', 'tenant_id' => $tenant->id],
            [
                'name' => 'Admin',
                'category' => 'admin',
                'description' => 'Tenant admin role',
                'is_system' => true,
            ]
        );

        User::create([
            'first_name' => $request->admin_first_name,
            'last_name' => $request->admin_last_name,
            'slug' => Str::slug($request->admin_first_name . ' ' . $request->admin_last_name),
            'email' => $request->admin_email,
            'password' => Hash::make($request->admin_password),
            'role_id' => $adminRole->id,
            'tenant_id' => $tenant->id,
            'designation' => 'Tenant Administrator',
            'user_type' => 'tenant_admin',
            'is_active' => true,
            'email_verified_at' => now(),
        ]);

        return redirect()->route('admin.tenants.index')->with('success', 'Tenant and tenant admin created successfully.');
    }

    public function approve($tenantId)
    {
        $this->authorizeSuperAdmin();
        $tenant = Tenant::findOrFail($tenantId);

        $tenant->update([
            'status' => 'approved',
            'is_active' => true,
            'approved_at' => now(),
            'approved_by' => Auth::id(),
        ]);

        User::where('tenant_id', $tenant->id)->whereIn('user_type', ['tenant_admin', 'sub_agent'])->update(['is_active' => true]);

        return redirect()->route('admin.tenants.index')->with('success', 'Tenant approved successfully.');
    }

    public function reject(Request $request, $tenantId)
    {
        $this->authorizeSuperAdmin();
        $tenant = Tenant::findOrFail($tenantId);

        $tenant->update([
            'status' => 'rejected',
            'is_active' => false,
            'approved_at' => null,
            'approved_by' => Auth::id(),
            'approval_notes' => $request->input('approval_notes'),
        ]);

        User::where('tenant_id', $tenant->id)->whereIn('user_type', ['tenant_admin', 'sub_agent'])->update(['is_active' => false]);

        return redirect()->route('admin.tenants.index')->with('success', 'Tenant rejected.');
    }
}
