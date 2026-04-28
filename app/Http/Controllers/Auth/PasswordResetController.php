<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log;
use App\Mail\PasswordResetMail;
use App\Models\Users\User;

class PasswordResetController extends Controller
{
    public function showForgotPasswordForm()
    {
        return view('auth.forgot-password');
    }

    public function sendResetLink(Request $request)
    {
        $request->validate([
            'email' => 'required|email|exists:users,email',
        ]);

        $token = Str::random(64);

        DB::table('password_reset_tokens')->updateOrInsert(
            ['email' => $request->email],
            ['token' => $token, 'created_at' => now()]
        );

        try {
            Mail::to($request->email)->send(new PasswordResetMail($token));
            
            Log::info('Password reset email queued successfully', ['email' => $request->email]);
        } catch (\Exception $e) {
            Log::error('Password reset email failed to queue', [
                'email' => $request->email,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
            
            return back()->withErrors(['email' => 'Failed to send email. Please try again later or contact support.']);
        }

        return back()->with('success', 'Password reset link sent to your email.');
    }

    public function showSetupPasswordForm($token)
    {
        return view('auth.setup-password', ['token' => $token]);
    }

    public function showResetPasswordForm($token)
    {
        return view('auth.reset-password', ['token' => $token]);
    }

    public function resetPassword(Request $request)
    {
        $request->validate([
            'token' => 'required',
            'email' => 'required|email|exists:users,email',
            'password' => 'required|min:8|confirmed',
        ]);

        $resetRecord = DB::table('password_reset_tokens')
            ->where('email', $request->email)
            ->where('token', $request->token)
            ->first();

        if (!$resetRecord || now()->diffInMinutes($resetRecord->created_at) > 60) {
            return back()->withErrors(['token' => 'Invalid or expired token.']);
        }

        // Find the user
        $user = User::where('email', $request->email)->first();
        
        if (!$user) {
            return back()->withErrors(['email' => 'User not found.']);
        }

        // Check if email was already verified
        $wasEmailVerified = (bool) $user->email_verified_at;

        // Update password
        $user->password = Hash::make($request->password);
        
        // Verify email if not already verified
        if (!$user->email_verified_at) {
            $user->email_verified_at = now();
        }
        
        $user->save();

        // Delete the password reset token
        DB::table('password_reset_tokens')->where('email', $request->email)->delete();

        // Determine success message based on whether email was verified
        $message = $wasEmailVerified
            ? 'Password reset successfully. You can now login.'
            : 'Password set and email verified successfully! You can now login.';

        return redirect()->route('login')->with('success', $message);
    }


}
