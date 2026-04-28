<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Users\User;
use Illuminate\Support\Facades\Auth;

class AdminController extends Controller
{
    /**
     * Show admin login form
     */
    public function showLoginForm()
    {
        if (Auth::check()) {
            $user = Auth::user();
            $user->refresh();
            if (in_array((int) $user->role_id, [1, 2, 3])) {
                return redirect()->route('admin.dashboard');
            } else {
                Auth::logout();
                return view('auth.admin-login');
            }
        }
        return view('auth.admin-login');
    }

    /**
     * Login admin user
     */
    public function login(\Illuminate\Http\Request $request)
    {
        $request->validate([
            'email' => 'required|email',
            'password' => 'required',
        ]);

        if (Auth::attempt($request->only('email', 'password'))) {
            $request->session()->regenerate();

            $user = Auth::user();
            $user->refresh();
            
            if (in_array((int) $user->role_id, [1, 2, 3])) {
                return redirect()->route('admin.dashboard');
            } else {
                Auth::logout();
                return redirect()->route('admin.login')->withErrors([
                    'email' => 'Access denied. Only Admin, Manager, and Editor roles can access the admin panel.',
                ]);
            }
        }

        return back()->withErrors([
            'email' => 'The provided credentials do not match our records.',
        ]);
    }

    /**
     * Show admin dashboard
     */
    public function adminDashboard()
    {
        $user = Auth::user();
        
        // Get basic stats
        $totalUsers = User::where('role_id', 4)->count();
        $totalAdmins = User::whereIn('role_id', [1, 2, 3])->count();
        $recentUsers = User::where('role_id', 4)->latest()->take(5)->get();
        
        return view('admin.dashboard', compact('totalUsers', 'totalAdmins', 'recentUsers'));
    }
}
