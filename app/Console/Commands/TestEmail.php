<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Mail;
use App\Mail\PasswordResetMail;
use App\Mail\EmailVerificationMail;
use App\Mail\WelcomeMail;
use App\Models\Users\User;

class TestEmail extends Command
{
    protected $signature = 'email:test {type=all : Type of email to test (password-reset, verification, welcome, all)} {email? : Email address to send test email}';
    protected $description = 'Test email sending functionality';

    public function handle()
    {
        $type = $this->argument('type');
        $email = $this->argument('email') ?? $this->ask('Enter email address to send test email');

        if (!$email) {
            $this->error('Email address is required');
            return 1;
        }

        $this->info("Testing email: {$email}");
        $this->line('');

        try {
            if ($type === 'all' || $type === 'password-reset') {
                $this->info('Sending password reset email...');
                Mail::to($email)->send(new PasswordResetMail('test-token-123'));
                $this->info('✓ Password reset email sent successfully!');
                $this->line('');
            }

            if ($type === 'all' || $type === 'verification') {
                $this->info('Sending email verification email...');
                Mail::to($email)->send(new EmailVerificationMail('test-token-456', 'Test User'));
                $this->info('✓ Email verification email sent successfully!');
                $this->line('');
            }

            if ($type === 'all' || $type === 'welcome') {
                $user = User::where('email', $email)->first();
                if ($user) {
                    $this->info('Sending welcome email...');
                    Mail::to($email)->send(new WelcomeMail($user));
                    $this->info('✓ Welcome email sent successfully!');
                } else {
                    $this->warn('User not found. Creating test user...');
                    $user = User::create([
                        'first_name' => 'Test',
                        'last_name' => 'User',
                        'email' => $email,
                        'password' => bcrypt('password'),
                        'role_id' => 4,
                    ]);
                    Mail::to($email)->send(new WelcomeMail($user));
                    $this->info('✓ Welcome email sent successfully!');
                }
                $this->line('');
            }

            $this->info('All emails sent successfully!');
            $this->line('Check your email inbox and spam folder.');
            
            return 0;
        } catch (\Exception $e) {
            $this->error('Failed to send email: ' . $e->getMessage());
            $this->line('');
            $this->warn('Make sure your email configuration in .env is correct.');
            $this->warn('For development, you can use Mailtrap or log driver.');
            
            return 1;
        }
    }
}
