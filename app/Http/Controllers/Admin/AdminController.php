<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\System\Tenant;
use App\Models\Users\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class AdminController extends Controller
{
    protected function dashboardRouteForUser(User $user): string
    {
        return match ($user->user_type) {
            'super_admin' => 'admin.dashboard',
            'tenant_admin' => 'agent.dashboard',
            'sub_agent' => 'subagent.dashboard',
            default => 'dashboard',
        };
    }

    /**
     * Show admin login form
     */
    public function showLoginForm()
    {
        return $this->showPortalLoginForm(
            'super_admin',
            'Super Admin Login',
            'Sign in as super admin',
            'post.admin.login'
        );
    }

    public function showTenantLoginForm()
    {
        return $this->showPortalLoginForm(
            'tenant_admin',
            'Agent Admin Login',
            'Sign in as agent admin',
            'post.tenant.login'
        );
    }

    public function showSubAgentLoginForm()
    {
        return $this->showPortalLoginForm(
            'sub_agent',
            'Sub Agent Login',
            'Sign in as sub agent',
            'post.subagent.login'
        );
    }

    public function login(Request $request)
    {
        return $this->handlePortalLogin($request, 'super_admin', 'admin.login', 'super admin');
    }

    public function tenantLogin(Request $request)
    {
        return $this->handlePortalLogin($request, 'tenant_admin', 'agent.login', 'agent admin');
    }

    public function subAgentLogin(Request $request)
    {
        return $this->handlePortalLogin($request, 'sub_agent', 'subagent.login', 'sub agent');
    }

    protected function showPortalLoginForm(string $expectedType, string $title, string $description, string $actionRoute)
    {
        if (Auth::check()) {
            $user = Auth::user();
            /** @var \App\Models\Users\User $user */
            $user->refresh();

            if ($user->user_type === $expectedType && $user->canAccessAdminPanel()) {
                return redirect()->route($this->dashboardRouteForUser($user));
            }

            Auth::logout();
        }

        return view('auth.admin-login', compact('title', 'description', 'actionRoute'));
    }

    protected function handlePortalLogin(Request $request, string $expectedType, string $errorRoute, string $label)
    {
        $request->validate([
            'login' => 'required|string',
            'password' => 'required',
        ]);

        $credentials = User::credentialsFromLogin($request->input('login'), $request->input('password'));

        if (Auth::attempt($credentials)) {
            $request->session()->regenerate();

            $user = Auth::user();
            /** @var \App\Models\Users\User $user */
            $user->refresh();

            if ($user->user_type !== $expectedType) {
                Auth::logout();

                return redirect()->route($errorRoute)->withErrors([
                    'login' => 'Invalid portal. Please use the correct login URL for your account type.',
                ]);
            }

            if ($user->canAccessAdminPanel()) {
                return redirect()->route($this->dashboardRouteForUser($user));
            } else {
                Auth::logout();

                return redirect()->route($errorRoute)->withErrors([
                    'login' => 'Access denied for '.$label.'. Your account is inactive or awaiting approval.',
                ]);
            }
        }

        return back()->withErrors([
            'login' => 'The provided credentials do not match our records.',
        ]);
    }

    /**
     * Show admin dashboard
     */
    public function adminDashboard()
    {
        $user = Auth::user();
        /** @var \App\Models\Users\User $user */
        if ($user->user_type === 'super_admin') {
            if (request()->routeIs('agent.*') || request()->routeIs('subagent.*')) {
                return redirect()->route('admin.dashboard');
            }
            $totalUsers = User::where('user_type', 'public')->count();
            $totalAdmins = User::whereIn('user_type', ['super_admin', 'tenant_admin', 'sub_agent'])->count();
            $recentUsers = User::where('user_type', 'public')->latest()->take(5)->get();
            $totalTenants = Tenant::count();
            $pendingTenants = Tenant::where('status', 'pending')->count();
            $subAgentCount = User::where('user_type', 'sub_agent')->count();

            return view('admin.dashboard', compact('totalUsers', 'totalAdmins', 'recentUsers', 'totalTenants', 'pendingTenants', 'subAgentCount'));
        }

        if ($user->user_type === 'tenant_admin' && request()->routeIs('admin.*')) {
            return redirect()->route('agent.dashboard');
        }

        if ($user->user_type === 'sub_agent' && (request()->routeIs('admin.*') || request()->routeIs('agent.*'))) {
            return redirect()->route('subagent.dashboard');
        }

        $tenantId = $user->tenant_id;
        $totalUsers = User::where('tenant_id', $tenantId)->where('user_type', 'public')->count();
        $totalAdmins = User::where('tenant_id', $tenantId)->whereIn('user_type', ['tenant_admin', 'sub_agent'])->count();
        $recentUsers = User::where('tenant_id', $tenantId)->latest()->take(5)->get();
        $subAgentCount = User::where('tenant_id', $tenantId)->where('user_type', 'sub_agent')->count();
        $totalTenants = null;
        $pendingTenants = null;

        return view('admin.dashboard', compact('totalUsers', 'totalAdmins', 'recentUsers', 'subAgentCount', 'totalTenants', 'pendingTenants'));
    }
}
