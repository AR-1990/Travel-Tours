<?php

namespace App\Services;

use App\Models\System\Permission;
use App\Models\System\Role;
use App\Models\Users\User;
use Illuminate\Http\Request;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class SubAgentService
{
    public function isSuperAdmin(?User $user): bool
    {
        return (bool) ($user && $user->user_type === 'super_admin');
    }

    /**
     * Permissions grouped and sorted for the sub-agent form (excludes dashboard/managers groups).
     *
     * @return array<string, \Illuminate\Support\Collection<int, Permission>>
     */
    public function buildOrderedPermissionsForForm(): array
    {
        $groupOrder = ['users' => 1, 'subscriptions' => 2, 'products-services' => 3, 'blogs' => 4, 'events' => 5, 'ads' => 6, 'scheduler-logs' => 7];

        $allPermissions = Permission::where('group', '!=', 'dashboard')
            ->where('group', '!=', 'managers')
            ->where('slug', '!=', 'dashboard.view')
            ->get()
            ->groupBy('group');

        $getCrudPriority = $this->makeCrudPrioritySorter();

        $orderedPermissions = [];
        foreach ($groupOrder as $group => $order) {
            if (isset($allPermissions[$group])) {
                $groupPerms = $allPermissions[$group]->sortBy($getCrudPriority)->values();
                $orderedPermissions[$group] = $groupPerms;
            }
        }

        foreach ($allPermissions as $group => $perms) {
            if (! isset($orderedPermissions[$group])) {
                $orderedPermissions[$group] = $perms->sortBy($getCrudPriority)->values();
            }
        }

        return $orderedPermissions;
    }

    public function roleOptionsForTenant(User $tenantUser): Collection
    {
        return Role::where('tenant_id', $tenantUser->tenant_id)
            ->orderBy('name')
            ->get();
    }

    public function createSubAgent(User $actor, Request $request, Role $role): User
    {
        $username = $request->filled('username')
            ? $request->username
            : User::generateUniqueUsernameFromEmail($request->email);

        $photoPath = null;
        if ($request->hasFile('photo')) {
            $photoPath = $request->file('photo')->store('users/photos', 'public');
        }

        $docPath = null;
        if ($request->hasFile('agent_document')) {
            $docPath = $request->file('agent_document')->store('users/agent-documents', 'public');
        }

        $user = User::create([
            'first_name' => $request->first_name,
            'last_name' => $request->last_name,
            'slug' => Str::slug($request->first_name.' '.$request->last_name),
            'email' => $request->email,
            'username' => $username,
            'phone' => $request->phone,
            'country' => $request->country,
            'photo' => $photoPath,
            'agent_document' => $docPath,
            'password' => Hash::make($request->password),
            'role_id' => $request->role_id,
            'tenant_id' => $this->isSuperAdmin($actor) ? $role->tenant_id : $actor->tenant_id,
            'designation' => $request->designation,
            'user_type' => 'sub_agent',
            'email_verified_at' => now(),
        ]);

        $this->syncDirectPermissions($user, $request);

        return $user;
    }

    public function updateSubAgent(User $actor, User $manager, Request $request, Role $role): void
    {
        $username = $request->filled('username')
            ? $request->username
            : ($manager->username ?: User::generateUniqueUsernameFromEmail($request->email));

        $photoPath = $manager->photo;
        if ($request->hasFile('photo')) {
            if ($manager->photo) {
                Storage::disk('public')->delete($manager->photo);
            }
            $photoPath = $request->file('photo')->store('users/photos', 'public');
        }

        $docPath = $manager->agent_document;
        if ($request->hasFile('agent_document')) {
            if ($manager->agent_document) {
                Storage::disk('public')->delete($manager->agent_document);
            }
            $docPath = $request->file('agent_document')->store('users/agent-documents', 'public');
        }

        $payload = [
            'first_name' => $request->first_name,
            'last_name' => $request->last_name,
            'slug' => Str::slug($request->first_name.' '.$request->last_name),
            'email' => $request->email,
            'username' => $username,
            'phone' => $request->phone,
            'country' => $request->country,
            'photo' => $photoPath,
            'agent_document' => $docPath,
            'designation' => $request->designation,
            'role_id' => $request->role_id,
        ];

        if ($request->filled('password')) {
            $payload['password'] = Hash::make($request->password);
        }

        $manager->update($payload);

        $this->syncDirectPermissions($manager, $request);
    }

    public function syncDirectPermissions(User $user, Request $request): void
    {
        if ($request->has('permissions')) {
            $user->userPermissions()->sync($request->permissions ?? []);
        }
    }

    protected function makeCrudPrioritySorter(): \Closure
    {
        return function ($permission) {
            $slug = strtolower($permission->slug);
            $name = strtolower($permission->name);

            if (strpos($slug, '.create') !== false || preg_match('/\badd\s+/', $name)) {
                return 1;
            }

            if (strpos($slug, 'filter') !== false || strpos($name, 'filter') !== false) {
                return 2.5;
            }
            if (strpos($slug, '.view') !== false || preg_match('/\bview\s+/', $name) || strpos($name, 'view/') !== false) {
                return 2;
            }

            if (strpos($slug, '.edit') !== false || strpos($slug, '.profile') !== false ||
                strpos($slug, '.company') !== false || strpos($slug, '.password') !== false ||
                preg_match('/\bedit\s+/', $name) || strpos($name, 'profile') !== false ||
                strpos($name, 'company') !== false || strpos($name, 'password') !== false) {
                return 3;
            }

            if (strpos($slug, '.delete') !== false || preg_match('/\bdelete\s+/', $name)) {
                return 4;
            }

            if (strpos($slug, '.restore') !== false || preg_match('/\brestore\s+/', $name)) {
                return 5;
            }

            return 999;
        };
    }
}
