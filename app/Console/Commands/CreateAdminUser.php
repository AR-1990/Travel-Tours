<?php

namespace App\Console\Commands;

use App\Models\Users\User;
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

        // Create admin user
        $user = User::create([
            'first_name' => $name,
            'last_name' => 'User',
            'email' => $email,
            'password' => Hash::make($password),
            'role_id' => 1, // Admin role
            'email_verified_at' => now(),
        ]);

        $this->info("Admin user created successfully!");
        $this->line("Email: {$email}");
        $this->line("Password: {$password}");
        $this->line("Role: Admin (ID: 1)");
        
        return 0;
    }
}
