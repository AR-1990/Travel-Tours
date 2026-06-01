<?php

namespace App\Services;

use App\Models\System\Role;
use App\Models\Users\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;

class AdminUserService
{
    public function validateUserTenantRules(Request $request): void
    {
        if (in_array($request->user_type, ['tenant_admin', 'sub_agent'], true) && ! $request->filled('tenant_id')) {
            throw ValidationException::withMessages([
                'tenant_id' => ['Agency is required for this user type.'],
            ]);
        }

        if ($request->user_type === 'public' && $request->filled('tenant_id')) {
            throw ValidationException::withMessages([
                'tenant_id' => ['Public users must not be assigned to an agency.'],
            ]);
        }
    }

    public function validateRoleMatchesUserType(Request $request, Role $role): void
    {
        if ($request->user_type === 'public') {
            if ($role->tenant_id !== null) {
                throw ValidationException::withMessages([
                    'role_id' => ['Public users must use a global (platform) role.'],
                ]);
            }

            return;
        }

        if (! in_array($request->user_type, ['tenant_admin', 'sub_agent'], true)) {
            throw ValidationException::withMessages([
                'user_type' => ['Invalid user type.'],
            ]);
        }

        if (! $request->filled('tenant_id')) {
            throw ValidationException::withMessages([
                'tenant_id' => ['Agency is required for this user type.'],
            ]);
        }

        if ((int) $role->tenant_id !== (int) $request->tenant_id) {
            throw ValidationException::withMessages([
                'role_id' => ['Selected role does not belong to the chosen agency.'],
            ]);
        }
    }

    public function createFromAdminRequest(Request $request): User
    {
        $this->validateUserTenantRules($request);

        $role = Role::findOrFail($request->role_id);
        $this->validateRoleMatchesUserType($request, $role);

        $photoPath = $this->storePublicFile($request, 'photo', 'users/photos');
        $docPath = $this->storePublicFile($request, 'agent_document', 'users/agent-documents');

        return User::create([
            'first_name' => $request->first_name,
            'last_name' => $request->last_name,
            'slug' => Str::slug($request->first_name.' '.$request->last_name),
            'email' => $request->email,
            'username' => $request->username,
            'password' => Hash::make($request->password),
            'phone' => $request->phone,
            'country' => $request->country,
            'photo' => $photoPath,
            'agent_document' => $docPath,
            'role_id' => $request->role_id,
            'tenant_id' => $request->user_type === 'public' ? null : $request->tenant_id,
            'user_type' => $request->user_type,
            'is_active' => true,
            'email_verified_at' => now(),
        ]);
    }

    public function updateFromAdminRequest(User $user, Request $request): void
    {
        $this->validateUserTenantRules($request);

        $role = Role::findOrFail($request->role_id);
        $this->validateRoleMatchesUserType($request, $role);

        $photoPath = $user->photo;
        if ($request->hasFile('photo')) {
            if ($user->photo) {
                Storage::disk('public')->delete($user->photo);
            }
            $photoPath = $request->file('photo')->store('users/photos', 'public');
        }

        $docPath = $user->agent_document;
        if ($request->hasFile('agent_document')) {
            if ($user->agent_document) {
                Storage::disk('public')->delete($user->agent_document);
            }
            $docPath = $request->file('agent_document')->store('users/agent-documents', 'public');
        }

        $data = [
            'first_name' => $request->first_name,
            'last_name' => $request->last_name,
            'slug' => Str::slug($request->first_name.' '.$request->last_name),
            'email' => $request->email,
            'username' => $request->username,
            'phone' => $request->phone,
            'country' => $request->country,
            'photo' => $photoPath,
            'agent_document' => $docPath,
            'role_id' => $request->role_id,
            'tenant_id' => $request->user_type === 'public' ? null : $request->tenant_id,
            'user_type' => $request->user_type,
        ];

        if ($request->filled('password')) {
            $data['password'] = Hash::make($request->password);
        }

        $user->update($data);
    }

    protected function storePublicFile(Request $request, string $key, string $directory): ?string
    {
        if (! $request->hasFile($key)) {
            return null;
        }

        return $request->file($key)->store($directory, 'public');
    }
}
