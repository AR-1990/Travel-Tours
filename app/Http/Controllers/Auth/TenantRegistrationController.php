<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\System\Role;
use App\Models\System\Tenant;
use App\Models\Users\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

class TenantRegistrationController extends Controller
{
    public function showForm()
    {
        return view('auth.tenant-register');
    }

    public function register(Request $request)
    {
        $request->validate([
            'tenant_name' => 'required|string|max:255',
            'tenant_email' => 'required|email|max:255',
            'tenant_phone' => 'nullable|string|max:50',
            'first_name' => 'required|string|max:255',
            'last_name' => 'required|string|max:255',
            'email' => 'required|email|unique:users,email',
            'password' => 'required|string|min:8|confirmed',
        ]);

        $tenant = Tenant::create([
            'name' => $request->tenant_name,
            'slug' => Str::slug($request->tenant_name) . '-' . Str::lower(Str::random(5)),
            'email' => $request->tenant_email,
            'phone' => $request->tenant_phone,
            'is_active' => false,
            'status' => 'pending',
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
            'first_name' => $request->first_name,
            'last_name' => $request->last_name,
            'slug' => Str::slug($request->first_name . ' ' . $request->last_name),
            'email' => $request->email,
            'password' => Hash::make($request->password),
            'role_id' => $adminRole->id,
            'tenant_id' => $tenant->id,
            'designation' => 'Tenant Administrator',
            'user_type' => 'tenant_admin',
            'is_active' => false,
        ]);

        return redirect()->route('admin.login')->with('success', 'Tenant signup submitted. Super admin approval is required before login.');
    }
}
