<?php

namespace App\Console\Commands;

use App\Models\Users\User;
use App\Models\System\Role;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Hash;

class CreateAdminUser extends Command
{
    protected $signature = 'admin:create {--email=admin@example.com} {--password=password} {--name=Admin}';
    protected $description = 'Create an admin user';

    public function handle()
    {
        $email = $this->option('email');
        $password = $this->option('password');
        $name = $this->option('name');

        // Check if admin already exists
        if (User::where('email', $email)->exists()) {
            $this->error("User with email {$email} already exists!");
            return 1;
        }

        $superAdminRoleId = Role::where('slug', 'super-admin')->whereNull('tenant_id')->value('id');
        if (!$superAdminRoleId) {
            $this->error('Super Admin role not found. Please run: php artisan db:seed');
            return 1;
        }

        // Create super admin user
        $user = User::create([
            'first_name' => $name,
            'last_name' => 'User',
            'email' => $email,
            'password' => Hash::make($password),
            'role_id' => $superAdminRoleId,
            'user_type' => 'super_admin',
            'email_verified_at' => now(),
        ]);

        $this->info("Admin user created successfully!");
        $this->line("Email: {$email}");
        $this->line("Password: {$password}");
        $this->line("Role: Super Admin");
        
        return 0;
    }
}
