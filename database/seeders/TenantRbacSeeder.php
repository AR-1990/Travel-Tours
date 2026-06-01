<?php

namespace Database\Seeders;

use App\Models\System\DebtorType;
use App\Models\System\Permission;
use App\Models\System\Role;
use App\Models\System\Tenant;
use App\Models\Users\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

class TenantRbacSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $permissions = [
            // Dashboard and roles/users management
            ['name' => 'View Dashboard', 'slug' => 'dashboard.view', 'group' => 'dashboard'],
            ['name' => 'View Tenants', 'slug' => 'tenants.view', 'group' => 'tenants'],
            ['name' => 'Create Tenants', 'slug' => 'tenants.create', 'group' => 'tenants'],
            ['name' => 'Approve Tenants', 'slug' => 'tenants.approve', 'group' => 'tenants'],
            ['name' => 'Reject Tenants', 'slug' => 'tenants.reject', 'group' => 'tenants'],
            ['name' => 'View Roles', 'slug' => 'roles.view', 'group' => 'roles'],
            ['name' => 'Create Roles', 'slug' => 'roles.create', 'group' => 'roles'],
            ['name' => 'Edit Roles', 'slug' => 'roles.edit', 'group' => 'roles'],
            ['name' => 'Delete Roles', 'slug' => 'roles.delete', 'group' => 'roles'],
            ['name' => 'View Users', 'slug' => 'users.view', 'group' => 'users'],
            ['name' => 'Create Users', 'slug' => 'users.create', 'group' => 'users'],
            ['name' => 'Edit Users', 'slug' => 'users.edit', 'group' => 'users'],
            ['name' => 'Delete Users', 'slug' => 'users.delete', 'group' => 'users'],
            ['name' => 'Restore Users', 'slug' => 'users.restore', 'group' => 'users'],
            ['name' => 'Reset Passwords', 'slug' => 'users.reset-password', 'group' => 'users'],
            ['name' => 'View Sub Agents', 'slug' => 'managers.view', 'group' => 'managers'],
            ['name' => 'Create Sub Agents', 'slug' => 'managers.create', 'group' => 'managers'],
            ['name' => 'Edit Sub Agents', 'slug' => 'managers.edit', 'group' => 'managers'],
            ['name' => 'Delete Sub Agents', 'slug' => 'managers.delete', 'group' => 'managers'],
            ['name' => 'Restore Sub Agents', 'slug' => 'managers.restore', 'group' => 'managers'],
            ['name' => 'View Blogs', 'slug' => 'blogs.view', 'group' => 'blogs'],
            ['name' => 'Create Blogs', 'slug' => 'blogs.create', 'group' => 'blogs'],
            ['name' => 'Edit Blogs', 'slug' => 'blogs.edit', 'group' => 'blogs'],
            ['name' => 'Delete Blogs', 'slug' => 'blogs.delete', 'group' => 'blogs'],

            // Flights (Travelport)
            ['name' => 'Search Flights', 'slug' => 'flights.search', 'group' => 'flights'],
            ['name' => 'Book Flights', 'slug' => 'flights.book', 'group' => 'flights'],

            // Sales
            ['name' => 'Confirm Bookings', 'slug' => 'booking.confirm', 'group' => 'sales'],
            ['name' => 'Booking Messages', 'slug' => 'booking.message', 'group' => 'sales'],
            ['name' => 'Booking Alerts', 'slug' => 'booking.alerts', 'group' => 'sales'],
            ['name' => 'Cancel Bookings', 'slug' => 'booking.cancel', 'group' => 'sales'],
            ['name' => 'View Booking Statements', 'slug' => 'booking.statement', 'group' => 'sales'],

            // Accounts/Finance
            ['name' => 'Manage Accounts', 'slug' => 'finance.accounts', 'group' => 'finance'],
            ['name' => 'Manage Finance', 'slug' => 'finance.manage', 'group' => 'finance'],
            ['name' => 'View Finance Reports', 'slug' => 'finance.reports', 'group' => 'finance'],

            // Operations
            ['name' => 'Manage Ferry Charter', 'slug' => 'operations.ferry-charter', 'group' => 'operations'],
            ['name' => 'Manage Manual Hotels', 'slug' => 'operations.manual-hotels', 'group' => 'operations'],
            ['name' => 'Customer Support', 'slug' => 'operations.customer-support', 'group' => 'operations'],
        ];

        foreach ($permissions as $permission) {
            Permission::updateOrCreate(
                ['slug' => $permission['slug']],
                [
                    'name' => $permission['name'],
                    'group' => $permission['group'],
                    'description' => $permission['name'],
                ]
            );
        }

        $superAdminRole = Role::updateOrCreate(
            ['slug' => 'super-admin', 'tenant_id' => null],
            [
                'name' => 'Super Admin',
                'category' => 'admin',
                'description' => 'Global super admin with full system access.',
                'is_system' => true,
            ]
        );

        $publicRole = Role::updateOrCreate(
            ['slug' => 'public-user', 'tenant_id' => null],
            [
                'name' => 'Public User',
                'category' => 'public',
                'description' => 'Default role for website users.',
                'is_system' => true,
            ]
        );

        $cashDebtorId = DebtorType::where('slug', 'cash')->value('id');

        $tenant = Tenant::updateOrCreate(
            ['slug' => 'demo-travel-tenant'],
            [
                'name' => 'Demo Travel Tenant',
                'email' => 'tenant@example.com',
                'phone' => '+920000000000',
                'is_active' => true,
                'status' => 'approved',
                'approved_at' => now(),
                'debtor_type_id' => $cashDebtorId,
                'office_type' => Tenant::OFFICE_B2B,
                'currency' => 'USD',
            ]
        );

        $tenantAdminRole = Role::updateOrCreate(
            ['slug' => 'admin', 'tenant_id' => $tenant->id],
            [
                'name' => 'Admin',
                'category' => 'admin',
                'description' => 'Tenant admin role',
                'is_system' => true,
            ]
        );

        $financeRole = Role::updateOrCreate(
            ['slug' => 'finance', 'tenant_id' => $tenant->id],
            [
                'name' => 'Accounts / Finance',
                'category' => 'finance',
                'description' => 'Finance operations role',
                'is_system' => true,
            ]
        );

        $salesRole = Role::updateOrCreate(
            ['slug' => 'sales', 'tenant_id' => $tenant->id],
            [
                'name' => 'Sales',
                'category' => 'sales',
                'description' => 'Sales operations role',
                'is_system' => true,
            ]
        );

        $operationsRole = Role::updateOrCreate(
            ['slug' => 'operations', 'tenant_id' => $tenant->id],
            [
                'name' => 'Operations',
                'category' => 'operations',
                'description' => 'Operations and support role',
                'is_system' => true,
            ]
        );

        $permissionIds = Permission::pluck('id', 'slug');

        $tenantAdminRole->permissions()->sync($permissionIds->values());
        $financeRole->permissions()->sync($permissionIds->only([
            'dashboard.view',
            'finance.accounts',
            'finance.manage',
            'finance.reports',
            'users.reset-password',
            'users.view',
        ])->values());
        $salesRole->permissions()->sync($permissionIds->only([
            'dashboard.view',
            'flights.search',
            'flights.book',
            'booking.confirm',
            'booking.message',
            'booking.alerts',
            'booking.cancel',
            'booking.statement',
            'users.reset-password',
        ])->values());
        $operationsRole->permissions()->sync($permissionIds->only([
            'dashboard.view',
            'operations.ferry-charter',
            'operations.manual-hotels',
            'operations.customer-support',
        ])->values());

        $superAdmin = User::updateOrCreate(
            ['email' => 'superadmin@traveltours.com'],
            [
                'first_name' => 'Super',
                'last_name' => 'Admin',
                'slug' => Str::slug('Super Admin'),
                'username' => 'superadmin',
                'password' => Hash::make('password123'),
                'role_id' => $superAdminRole->id,
                'tenant_id' => null,
                'designation' => 'Super Admin',
                'user_type' => 'super_admin',
                'is_active' => true,
                'email_verified_at' => now(),
            ]
        );

        $tenant->update([
            'approved_by' => $superAdmin->id,
            'approval_notes' => 'Seeded as approved demo tenant.',
            'assigned_by' => $superAdmin->id,
        ]);

        $tenantAdmin = User::updateOrCreate(
            ['email' => 'tenantadmin@traveltours.com'],
            [
                'first_name' => 'Tenant',
                'last_name' => 'Admin',
                'slug' => Str::slug('Tenant Admin'),
                'username' => 'tenantadmin',
                'password' => Hash::make('password123'),
                'role_id' => $tenantAdminRole->id,
                'tenant_id' => $tenant->id,
                'designation' => 'Tenant Administrator',
                'user_type' => 'tenant_admin',
                'is_active' => true,
                'email_verified_at' => now(),
            ]
        );

        User::updateOrCreate(
            ['email' => 'finance.agent@traveltours.com'],
            [
                'first_name' => 'Finance',
                'last_name' => 'Agent',
                'slug' => Str::slug('Finance Agent'),
                'username' => 'financeagent',
                'password' => Hash::make('password123'),
                'role_id' => $financeRole->id,
                'tenant_id' => $tenant->id,
                'designation' => 'Accounts Officer',
                'user_type' => 'sub_agent',
                'is_active' => true,
                'email_verified_at' => now(),
            ]
        );

        User::updateOrCreate(
            ['email' => 'sales.agent@traveltours.com'],
            [
                'first_name' => 'Sales',
                'last_name' => 'Agent',
                'slug' => Str::slug('Sales Agent'),
                'username' => 'salesagent',
                'password' => Hash::make('password123'),
                'role_id' => $salesRole->id,
                'tenant_id' => $tenant->id,
                'designation' => 'Sales Executive',
                'user_type' => 'sub_agent',
                'is_active' => true,
                'email_verified_at' => now(),
            ]
        );

        User::updateOrCreate(
            ['email' => 'operations.agent@traveltours.com'],
            [
                'first_name' => 'Operations',
                'last_name' => 'Agent',
                'slug' => Str::slug('Operations Agent'),
                'username' => 'operationsagent',
                'password' => Hash::make('password123'),
                'role_id' => $operationsRole->id,
                'tenant_id' => $tenant->id,
                'designation' => 'Operations Officer',
                'user_type' => 'sub_agent',
                'is_active' => true,
                'email_verified_at' => now(),
            ]
        );

        // Example of tenant signup awaiting super-admin approval.
        $pendingTenant = Tenant::updateOrCreate(
            ['slug' => 'pending-travel-tenant'],
            [
                'name' => 'Pending Travel Tenant',
                'email' => 'pending-tenant@example.com',
                'phone' => '+920000000001',
                'is_active' => false,
                'status' => 'pending',
                'approved_at' => null,
                'approved_by' => null,
                'approval_notes' => 'Created from tenant-signup flow and awaiting review.',
                'debtor_type_id' => $cashDebtorId,
                'office_type' => Tenant::OFFICE_B2B,
                'currency' => 'USD',
            ]
        );

        $pendingTenantAdminRole = Role::updateOrCreate(
            ['slug' => 'admin', 'tenant_id' => $pendingTenant->id],
            [
                'name' => 'Admin',
                'category' => 'admin',
                'description' => 'Tenant admin role for pending tenant',
                'is_system' => true,
            ]
        );

        User::updateOrCreate(
            ['email' => 'pending.tenantadmin@traveltours.com'],
            [
                'first_name' => 'Pending',
                'last_name' => 'Admin',
                'slug' => Str::slug('Pending Tenant Admin'),
                'username' => 'pendingtenantadmin',
                'password' => Hash::make('password123'),
                'role_id' => $pendingTenantAdminRole->id,
                'tenant_id' => $pendingTenant->id,
                'designation' => 'Tenant Administrator',
                'user_type' => 'tenant_admin',
                'is_active' => false,
                'email_verified_at' => now(),
            ]
        );

        // Ensure tenant admin can manage categories/users/permissions from UI.
        $tenantAdmin->userPermissions()->syncWithoutDetaching($permissionIds->only([
            'roles.view',
            'roles.create',
            'roles.edit',
            'users.view',
            'users.create',
            'users.edit',
            'users.delete',
            'users.reset-password',
            'managers.view',
            'managers.create',
            'managers.edit',
            'managers.delete',
            'managers.restore',
        ])->values());

        $superAdmin->userPermissions()->syncWithoutDetaching($permissionIds->only([
            'tenants.view',
            'tenants.create',
            'tenants.approve',
            'tenants.reject',
        ])->values());
    }
}
