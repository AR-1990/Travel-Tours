<?php

namespace App\Services;

use App\Models\System\Role;
use App\Models\System\Tenant;
use App\Models\Users\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

class TenantProvisioningService
{
    /**
     * @return array{0: string, 1: string}
     */
    public function generateUniqueAgencyAndAgentCodes(): array
    {
        do {
            $agency = 'AGY-'.strtoupper(Str::random(6));
        } while (Tenant::where('agency_code', $agency)->exists());

        do {
            $agent = 'AGT-'.strtoupper(Str::random(6));
        } while (Tenant::where('agent_code', $agent)->exists());

        return [$agency, $agent];
    }

    /**
     * @return list<string>|null
     */
    public function storeTenantDocuments(Request $request): ?array
    {
        if (! $request->hasFile('documents')) {
            return null;
        }

        $paths = [];
        foreach ($request->file('documents') as $file) {
            if ($file && $file->isValid()) {
                $paths[] = $file->store('tenants/documents', 'public');
            }
        }

        return $paths ?: null;
    }

    public function storeTenantLogo(Request $request): ?string
    {
        if (! $request->hasFile('logo')) {
            return null;
        }

        return $request->file('logo')->store('tenants/logos', 'public');
    }

    public function ensureTenantAdminRole(Tenant $tenant): Role
    {
        return Role::firstOrCreate(
            ['slug' => 'admin', 'tenant_id' => $tenant->id],
            [
                'name' => 'Admin',
                'category' => 'admin',
                'description' => 'Tenant admin role',
                'is_system' => true,
            ]
        );
    }

    public function resolveUsername(?string $username, string $email): string
    {
        $clean = $username !== null ? trim($username) : '';

        return $clean !== '' ? $clean : User::generateUniqueUsernameFromEmail($email);
    }

    public function storeUserPhoto(Request $request, string $field = 'admin_photo'): ?string
    {
        if (! $request->hasFile($field)) {
            return null;
        }

        return $request->file($field)->store('users/photos', 'public');
    }

    public function storeUserAgentDocument(Request $request, string $field = 'admin_agent_document'): ?string
    {
        if (! $request->hasFile($field)) {
            return null;
        }

        return $request->file($field)->store('users/agent-documents', 'public');
    }

    /**
     * Super-admin creates an approved agency + active tenant admin (from admin tenants form).
     */
    public function provisionApprovedAgencyWithAdmin(Request $request, User $assigner): Tenant
    {
        [$agencyCode, $agentCode] = $this->generateUniqueAgencyAndAgentCodes();
        $docPaths = $this->storeTenantDocuments($request);
        $logoPath = $this->storeTenantLogo($request);
        $tenantSlug = Str::slug($request->office_name).'-'.Str::lower(Str::random(5));

        $tenant = Tenant::create([
            'name' => $request->office_name,
            'slug' => $tenantSlug,
            'agency_code' => $agencyCode,
            'agent_code' => $agentCode,
            'email' => $request->tenant_email,
            'phone' => $request->tenant_phone,
            'assigned_by' => $assigner->id,
            'office_type' => $request->office_type,
            'debtor_type_id' => $request->debtor_type_id,
            'tax_number' => $request->tax_number,
            'reg_number' => $request->reg_number,
            'address_country' => $request->address_country,
            'address_state' => $request->address_state,
            'address_city' => $request->address_city,
            'address_line' => $request->address_line,
            'documents' => $docPaths ?: null,
            'logo' => $logoPath,
            'currency' => strtoupper($request->currency),
            'parent_tenant_id' => $request->parent_tenant_id,
            'is_active' => true,
            'status' => 'approved',
            'approved_at' => now(),
            'approved_by' => $assigner->id,
        ]);

        $adminRole = $this->ensureTenantAdminRole($tenant);

        $username = $this->resolveUsername($request->input('admin_username'), $request->admin_email);

        $adminPhoto = $this->storeUserPhoto($request, 'admin_photo');
        $adminDoc = $this->storeUserAgentDocument($request, 'admin_agent_document');

        User::create([
            'first_name' => $request->admin_first_name,
            'last_name' => $request->admin_last_name,
            'slug' => Str::slug($request->admin_first_name.' '.$request->admin_last_name),
            'email' => $request->admin_email,
            'username' => $username,
            'password' => Hash::make($request->admin_password),
            'phone' => $request->admin_phone,
            'country' => $request->admin_country,
            'photo' => $adminPhoto,
            'agent_document' => $adminDoc,
            'role_id' => $adminRole->id,
            'tenant_id' => $tenant->id,
            'designation' => 'Tenant Administrator',
            'user_type' => 'tenant_admin',
            'is_active' => true,
            'email_verified_at' => now(),
        ]);

        return $tenant;
    }

    /**
     * Public self-registration: pending agency + inactive tenant admin.
     */
    public function provisionPendingAgencySelfSignup(Request $request): Tenant
    {
        [$agencyCode, $agentCode] = $this->generateUniqueAgencyAndAgentCodes();
        $docPaths = $this->storeTenantDocuments($request);
        $logoPath = $this->storeTenantLogo($request);

        $tenant = Tenant::create([
            'name' => $request->tenant_name,
            'slug' => Str::slug($request->tenant_name).'-'.Str::lower(Str::random(5)),
            'agency_code' => $agencyCode,
            'agent_code' => $agentCode,
            'email' => $request->tenant_email,
            'phone' => $request->tenant_phone,
            'office_type' => $request->office_type,
            'debtor_type_id' => $request->debtor_type_id,
            'tax_number' => $request->tax_number,
            'reg_number' => $request->reg_number,
            'address_country' => $request->address_country,
            'address_state' => $request->address_state,
            'address_city' => $request->address_city,
            'address_line' => $request->address_line,
            'documents' => $docPaths ?: null,
            'logo' => $logoPath,
            'currency' => strtoupper($request->currency),
            'is_active' => false,
            'status' => 'pending',
        ]);

        $adminRole = $this->ensureTenantAdminRole($tenant);

        $username = $this->resolveUsername(
            $request->filled('username') ? $request->username : null,
            $request->email
        );

        $photoPath = $this->storeUserPhoto($request, 'photo');
        $agentDoc = $this->storeUserAgentDocument($request, 'agent_document');

        User::create([
            'first_name' => $request->first_name,
            'last_name' => $request->last_name,
            'slug' => Str::slug($request->first_name.' '.$request->last_name),
            'email' => $request->email,
            'username' => $username,
            'password' => Hash::make($request->password),
            'phone' => $request->mobile,
            'country' => $request->country,
            'photo' => $photoPath,
            'agent_document' => $agentDoc,
            'role_id' => $adminRole->id,
            'tenant_id' => $tenant->id,
            'designation' => 'Tenant Administrator',
            'user_type' => 'tenant_admin',
            'is_active' => false,
        ]);

        return $tenant;
    }
}
