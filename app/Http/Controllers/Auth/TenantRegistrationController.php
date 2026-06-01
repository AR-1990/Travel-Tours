<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\System\DebtorType;
use App\Services\TenantProvisioningService;
use Illuminate\Http\Request;

class TenantRegistrationController extends Controller
{
    public function __construct(
        protected TenantProvisioningService $tenantProvisioningService
    ) {}

    public function showForm()
    {
        $debtorTypes = DebtorType::where('is_active', true)->orderBy('name')->get();

        return view('auth.tenant-register', compact('debtorTypes'));
    }

    public function register(Request $request)
    {
        $request->validate([
            'tenant_name' => 'required|string|max:255',
            'tenant_email' => 'required|email|max:255',
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
            'documents' => 'nullable|array|max:10',
            'documents.*' => 'file|mimes:pdf,jpg,jpeg,png|max:512',
            'logo' => 'nullable|image|mimes:jpg,jpeg,png,webp|max:1024',
            'first_name' => 'required|string|max:255',
            'last_name' => 'required|string|max:255',
            'email' => 'required|email|unique:users,email',
            'username' => 'nullable|string|max:50|regex:/^[a-zA-Z0-9._-]+$/|unique:users,username',
            'mobile' => 'nullable|string|max:50',
            'country' => 'nullable|string|max:100',
            'password' => 'required|string|min:8|confirmed',
            'photo' => 'nullable|image|mimes:jpg,jpeg,png,webp|max:2048',
            'agent_document' => 'nullable|file|mimes:pdf,jpg,jpeg,png|max:5120',
        ]);

        $this->tenantProvisioningService->provisionPendingAgencySelfSignup($request);

        return redirect()->route('agent.login')->with('success', 'Agency signup submitted. Super admin approval is required before login.');
    }
}
