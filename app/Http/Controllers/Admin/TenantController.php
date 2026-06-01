<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\System\DebtorType;
use App\Models\System\Tenant;
use App\Models\Users\User;
use App\Services\TenantProvisioningService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class TenantController extends Controller
{
    public function __construct(
        protected TenantProvisioningService $tenantProvisioningService
    ) {}

    protected function authorizeSuperAdmin(): User
    {
        /** @var User $user */
        $user = Auth::user();
        if (! $user || $user->user_type !== 'super_admin') {
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

        $debtorTypes = DebtorType::where('is_active', true)->orderBy('name')->get();
        $parentAgencies = Tenant::where('status', 'approved')
            ->where('office_type', Tenant::OFFICE_GSA)
            ->orderBy('name')
            ->get();

        return view('admin.tenants.index', compact('tenants', 'debtorTypes', 'parentAgencies'));
    }

    public function show($tenantId)
    {
        $this->authorizeSuperAdmin();

        $tenant = Tenant::with(['users.role', 'debtorType', 'assigner', 'parent'])->findOrFail($tenantId);

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
        $assigner = $this->authorizeSuperAdmin();

        $request->validate([
            'office_name' => 'required|string|max:255',
            'tenant_email' => 'nullable|email|max:255',
            'tenant_phone' => 'nullable|string|max:50',
            'office_type' => 'required|in:b2b_agent,gsa_agent,api_agent',
            'debtor_type_id' => 'required|exists:debtor_types,id',
            'tax_number' => 'nullable|string|max:120',
            'reg_number' => 'nullable|string|max:120',
            'address_country' => 'nullable|string|max:120',
            'address_state' => 'nullable|string|max:120',
            'address_city' => 'nullable|string|max:120',
            'address_line' => 'nullable|string|max:500',
            'currency' => 'required|string|size:3',
            'parent_tenant_id' => 'nullable|exists:tenants,id',
            'documents' => 'nullable|array|max:10',
            'documents.*' => 'file|mimes:pdf,jpg,jpeg,png|max:512',
            'logo' => 'nullable|image|mimes:jpg,jpeg,png,webp|max:1024',
            'admin_first_name' => 'required|string|max:255',
            'admin_last_name' => 'required|string|max:255',
            'admin_email' => 'required|email|unique:users,email',
            'admin_username' => 'nullable|string|max:50|regex:/^[a-zA-Z0-9._-]+$/|unique:users,username',
            'admin_password' => 'required|string|min:8',
            'admin_phone' => 'nullable|string|max:50',
            'admin_country' => 'nullable|string|max:100',
            'admin_photo' => 'nullable|image|mimes:jpg,jpeg,png,webp|max:2048',
            'admin_agent_document' => 'nullable|file|mimes:pdf,jpg,jpeg,png|max:5120',
        ]);

        $this->tenantProvisioningService->provisionApprovedAgencyWithAdmin($request, $assigner);

        return redirect()->route('admin.tenants.index')->with('success', 'Agency and agent admin created successfully.');
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
