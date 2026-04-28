<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use App\Models\Users\User;

class EmailVerificationController extends Controller
{
    public function verify($token)
    {
        // Find the email verification token
        $verificationRecord = DB::table('email_verification_tokens')
            ->where('token', $token)
            ->first();

        if (!$verificationRecord) {
            return redirect()->route('home')->with('error', 'Invalid verification link.');
        }

        // Check if token is expired (24 hours)
        if (now()->diffInHours($verificationRecord->created_at) > 24) {
            DB::table('email_verification_tokens')->where('token', $token)->delete();
            return redirect()->route('home')->with('error', 'Verification link has expired. Please request a new one.');
        }

        // Find the user
        $user = User::where('email', $verificationRecord->email)->first();

        if (!$user) {
            return redirect()->route('home')->with('error', 'User not found.');
        }

        // Check if already verified
        if ($user->email_verified_at) {
            DB::table('email_verification_tokens')->where('token', $token)->delete();
            return redirect()->route('home')->with('success', 'Email is already verified.');
        }

        // Verify the email
        $user->email_verified_at = now();
        $user->save();

        // Delete the verification token
        DB::table('email_verification_tokens')->where('token', $token)->delete();

        return redirect()->route('home')->with('success', 'Email verified successfully! You can now login.');
    }
}

