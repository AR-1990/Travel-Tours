<?php

namespace App\Console\Commands;

use App\Models\Users\User;
use Illuminate\Console\Command;

class ShowAdminUsers extends Command
{
    protected $signature = 'admin:list';
    protected $description = 'List all admin users';

    public function handle()
    {
        $admins = User::where('role_id', 1)->get(['id', 'first_name', 'last_name', 'email', 'created_at']);

        if ($admins->count() === 0) {
            $this->info('No admin users found.');
            return 0;
        }

        $this->info('Admin Users:');
        $this->line('');
        
        foreach ($admins as $admin) {
            $this->line("ID: {$admin->id}");
            $this->line("Name: {$admin->first_name} {$admin->last_name}");
            $this->line("Email: {$admin->email}");
            $this->line("Created: {$admin->created_at}");
            $this->line('---');
        }

        return 0;
    }
}
