<?php

use App\Http\Controllers\AirportLookupController;
use App\Http\Controllers\Admin\AdminController;
use App\Http\Controllers\Admin\AdminUserController;
use App\Http\Controllers\Admin\BlogController as AdminBlogController;
use App\Http\Controllers\Admin\DebtorTypeController;
use App\Http\Controllers\Admin\FlightController as AdminFlightController;
use App\Http\Controllers\Admin\IntegrationsController;
use App\Http\Controllers\Admin\ManagersController;
use App\Http\Controllers\Admin\PermissionController;
use App\Http\Controllers\Admin\RoleController;
use App\Http\Controllers\Admin\TenantController;
use App\Http\Controllers\Agent\DashboardController as AgentDashboardController;
use App\Http\Controllers\Agent\FlightController as AgentFlightController;
use App\Http\Controllers\Agent\PermissionController as AgentPermissionController;
use App\Http\Controllers\Agent\RoleController as AgentRoleController;
use App\Http\Controllers\Agent\SubAgentController as AgentSubAgentController;
use App\Http\Controllers\Auth\EmailVerificationController;
use App\Http\Controllers\Auth\PasswordResetController;
use App\Http\Controllers\Auth\TenantRegistrationController;
use App\Http\Controllers\BlogController;
use App\Http\Controllers\PublicFlightController;
use App\Http\Controllers\SubAgent\DashboardController as SubAgentDashboardController;
use App\Http\Controllers\SubAgent\FlightController as SubAgentFlightController;
use App\Http\Controllers\SubAgent\PermissionController as SubAgentPermissionController;
use App\Http\Controllers\SubAgent\RoleController as SubAgentRoleController;
use App\Http\Controllers\SubAgent\SubAgentController as SubAgentManagementController;
use App\Http\Controllers\User\UserController;
use App\Http\Middleware\RoleMiddleware;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Public Routes
|--------------------------------------------------------------------------
*/

Route::get('/', function () {
    $recentBlogs = \App\Models\Content\Blog::latest()->take(3)->get();
    $airportOptions = collect(\App\Support\AirportDirectory::popular())
        ->mapWithKeys(fn (array $row): array => [(string) $row['code'] => (string) ($row['label'] ?? $row['code'])])
        ->all();

    $stored = session('public.flight_search');
    $flightSearchInput = is_array($stored) ? ($stored['input'] ?? []) : [];

    return view('frontend.index', compact('recentBlogs', 'airportOptions', 'flightSearchInput'));
})->name('home');
Route::get('/flights', [PublicFlightController::class, 'flightHub'])->name('frontend.flights.hub');
Route::post('/search/flights', [PublicFlightController::class, 'flightSearch'])->name('frontend.flights.search');
Route::get('/flights/results', [PublicFlightController::class, 'flightResults'])->name('frontend.flights.results');
Route::post('/flights/price', [PublicFlightController::class, 'flightPrice'])->name('frontend.flights.price');
Route::get('/flights/price', [PublicFlightController::class, 'flightPriceShow'])->name('frontend.flights.price.show');
Route::get('/flights/book', [PublicFlightController::class, 'flightBookShow'])->name('frontend.flights.book');
Route::post('/flights/book', [PublicFlightController::class, 'flightBookStore'])->name('frontend.flights.book.store');
Route::get('/flights/confirmation', [PublicFlightController::class, 'flightConfirmation'])->name('frontend.flights.confirmation');
Route::post('/flights/ticket', [PublicFlightController::class, 'flightTicketIssue'])->name('frontend.flights.ticket');
Route::match(['get', 'post'], '/flights/operations/{operation}', [PublicFlightController::class, 'flightOperation'])
    ->name('frontend.flights.operation');

/** Marketing / platform hub (Tailwind) — use e.g. for links that need the simple landing */
Route::get('/platform', function () {
    return view('pages.home');
})->name('platform.home');
Route::get('/blogs', [BlogController::class, 'index'])->name('blogs.index');
Route::get('/blogs/{slug}', [BlogController::class, 'show'])->name('blogs.show');

/*
|--------------------------------------------------------------------------
| Guest Routes (Authentication)
|--------------------------------------------------------------------------
*/

Route::middleware('guest')->group(function () {
    // User Registration
    Route::get('/register', [UserController::class, 'showRegisterForm'])->name('register.form');
    Route::post('/register', [UserController::class, 'register'])->name('register');

    // User Login
    Route::get('/login', [UserController::class, 'showLoginForm'])->name('login.form');
    Route::post('/login', [UserController::class, 'login'])->name('login');

    // Password Reset
    Route::get('/forgot-password', [PasswordResetController::class, 'showForgotPasswordForm'])->name('password.request');
    Route::post('/forgot-password', [PasswordResetController::class, 'sendResetLink'])->name('password.email');
    Route::get('/reset-password/{token}', [PasswordResetController::class, 'showResetPasswordForm'])->name('password.reset');
    Route::post('/reset-password', [PasswordResetController::class, 'resetPassword'])->name('password.update');

    // Email Verification
    Route::get('/verify-email/{token}', [EmailVerificationController::class, 'verify'])->name('email.verify');

    // Admin Login
    Route::get('/admin/login', [AdminController::class, 'showLoginForm'])->name('admin.login');
    Route::post('/admin/login', [AdminController::class, 'login'])->name('post.admin.login');
    Route::get('/tenant/login', [AdminController::class, 'showTenantLoginForm'])->name('tenant.login');
    Route::post('/tenant/login', [AdminController::class, 'tenantLogin'])->name('post.tenant.login');
    Route::get('/agent/login', [AdminController::class, 'showTenantLoginForm'])->name('agent.login');
    Route::post('/agent/login', [AdminController::class, 'tenantLogin'])->name('post.agent.login');
    Route::get('/sub-agent/login', [AdminController::class, 'showSubAgentLoginForm'])->name('subagent.login');
    Route::post('/sub-agent/login', [AdminController::class, 'subAgentLogin'])->name('post.subagent.login');

    // Agent Signup
    Route::get('/tenant/register', [TenantRegistrationController::class, 'showForm'])->name('tenant.register.form');
    Route::post('/tenant/register', [TenantRegistrationController::class, 'register'])->name('tenant.register');
    Route::get('/agent/register', [TenantRegistrationController::class, 'showForm'])->name('agent.register.form');
    Route::post('/agent/register', [TenantRegistrationController::class, 'register'])->name('agent.register');
});

/*
|--------------------------------------------------------------------------
| Authenticated User Routes
|--------------------------------------------------------------------------
*/

Route::middleware('auth')->group(function () {
    Route::prefix('api')->name('api.')->group(function () {
        Route::get('/airports/search', [AirportLookupController::class, 'search'])->name('airports.search');
        Route::get('/airports/{code}', [AirportLookupController::class, 'show'])
            ->name('airports.show')
            ->where('code', '[A-Za-z]{3}');
    });

    // User Dashboard
    Route::get('/dashboard', [UserController::class, 'dashboard'])->name('dashboard');

    // User Profile
    Route::get('/profile', [UserController::class, 'profile'])->name('profile');
    Route::post('/profile/update', [UserController::class, 'updateProfile'])->name('profile.update');

    // User Settings
    Route::get('/settings', [UserController::class, 'settings'])->name('settings');
    Route::post('/settings/update', [UserController::class, 'updateSettings'])->name('settings.update');
});

/*
|--------------------------------------------------------------------------
| Prefixed Admin Spaces
|--------------------------------------------------------------------------
*/

Route::middleware(['auth', RoleMiddleware::class.':1'])->prefix('admin')->name('admin.')->group(function () {
    Route::get('/dashboard', [AdminController::class, 'adminDashboard'])->name('dashboard');

    // Users (currently blocked by controller policy)
    Route::get('/users', [AdminUserController::class, 'index'])->name('users');
    Route::get('/users/create', [AdminUserController::class, 'create'])->name('users.create');
    Route::post('/users', [AdminUserController::class, 'store'])->name('users.store');
    Route::get('/users/{id}', [AdminUserController::class, 'show'])->name('users.show');
    Route::get('/users/{id}/edit', [AdminUserController::class, 'edit'])->name('users.edit');
    Route::put('/users/{id}', [AdminUserController::class, 'update'])->name('users.update');
    Route::delete('/users/{id}', [AdminUserController::class, 'destroy'])->name('users.destroy');
    Route::post('/users/{id}/restore', [AdminUserController::class, 'restore'])->name('users.restore');

    // Roles/Permissions/Sub-Agents
    Route::get('/roles', [RoleController::class, 'index'])->name('roles');
    Route::get('/roles/create', [RoleController::class, 'create'])->name('roles.create');
    Route::post('/roles', [RoleController::class, 'store'])->name('roles.store');
    Route::get('/roles/{id}', [RoleController::class, 'show'])->name('roles.show');
    Route::get('/roles/{id}/edit', [RoleController::class, 'edit'])->name('roles.edit');
    Route::put('/roles/{id}', [RoleController::class, 'update'])->name('roles.update');
    Route::delete('/roles/{id}', [RoleController::class, 'destroy'])->name('roles.destroy');
    Route::post('/roles/{id}/permissions', [RoleController::class, 'updatePermissions'])->name('roles.permissions');

    Route::get('/permissions', [PermissionController::class, 'index'])->name('permissions');
    Route::get('/permissions/create', [PermissionController::class, 'create'])->name('permissions.create');
    Route::post('/permissions', [PermissionController::class, 'store'])->name('permissions.store');
    Route::get('/permissions/{id}/edit', [PermissionController::class, 'edit'])->name('permissions.edit');
    Route::put('/permissions/{id}', [PermissionController::class, 'update'])->name('permissions.update');
    Route::delete('/permissions/{id}', [PermissionController::class, 'destroy'])->name('permissions.destroy');

    Route::get('/sub-agents', [ManagersController::class, 'index'])->name('managers');
    Route::get('/sub-agents/create', [ManagersController::class, 'create'])->name('managers.create');
    Route::post('/sub-agents', [ManagersController::class, 'store'])->name('managers.store');
    Route::get('/sub-agents/{id}/edit', [ManagersController::class, 'edit'])->name('managers.edit');
    Route::put('/sub-agents/{id}', [ManagersController::class, 'update'])->name('managers.update');
    Route::post('/sub-agents/{id}/permissions', [ManagersController::class, 'updatePermissions'])->name('managers.permissions');
    Route::delete('/sub-agents/{id}', [ManagersController::class, 'destroy'])->name('managers.destroy');
    Route::post('/sub-agents/{id}/restore', [ManagersController::class, 'restore'])->name('managers.restore');

    // Agent/Tenant oversight
    Route::get('/tenants', [TenantController::class, 'index'])->name('tenants.index');
    Route::get('/tenants/{tenant}', [TenantController::class, 'show'])->name('tenants.show');
    Route::post('/tenants', [TenantController::class, 'store'])->name('tenants.store');
    Route::post('/tenants/{tenant}/approve', [TenantController::class, 'approve'])->name('tenants.approve');
    Route::post('/tenants/{tenant}/reject', [TenantController::class, 'reject'])->name('tenants.reject');

    Route::get('/blogs', [AdminBlogController::class, 'index'])->name('blogs.index');
    Route::get('/blogs/create', [AdminBlogController::class, 'create'])->name('blogs.create');
    Route::post('/blogs', [AdminBlogController::class, 'store'])->name('blogs.store');
    Route::get('/blogs/{id}/edit', [AdminBlogController::class, 'edit'])->name('blogs.edit');
    Route::put('/blogs/{id}', [AdminBlogController::class, 'update'])->name('blogs.update');
    Route::delete('/blogs/{id}', [AdminBlogController::class, 'destroy'])->name('blogs.destroy');

    Route::get('/debtor-types', [DebtorTypeController::class, 'index'])->name('debtor-types.index');
    Route::get('/debtor-types/create', [DebtorTypeController::class, 'create'])->name('debtor-types.create');
    Route::post('/debtor-types', [DebtorTypeController::class, 'store'])->name('debtor-types.store');
    Route::get('/debtor-types/{id}/edit', [DebtorTypeController::class, 'edit'])->name('debtor-types.edit');
    Route::put('/debtor-types/{id}', [DebtorTypeController::class, 'update'])->name('debtor-types.update');
    Route::delete('/debtor-types/{id}', [DebtorTypeController::class, 'destroy'])->name('debtor-types.destroy');

    Route::get('/integrations', [IntegrationsController::class, 'index'])->name('integrations.index');
    Route::get('/integrations/{slug}', [IntegrationsController::class, 'edit'])->name('integrations.edit')->where('slug', '[a-z0-9_-]+');
    Route::put('/integrations/{slug}', [IntegrationsController::class, 'update'])->name('integrations.update')->where('slug', '[a-z0-9_-]+');
    Route::post('/integrations/{slug}/ping', [IntegrationsController::class, 'ping'])->name('integrations.ping')->where('slug', '[a-z0-9_-]+');
    Route::post('/integrations/{slug}/test-search', [IntegrationsController::class, 'testSearch'])->name('integrations.test-search')->where('slug', '[a-z0-9_-]+');

    Route::get('/flights', [AdminFlightController::class, 'hub'])->name('flights.index');
    Route::match(['get', 'post'], '/flights/search', [AdminFlightController::class, 'search'])->name('flights.search');
    Route::post('/flights/price', [AdminFlightController::class, 'price'])->name('flights.price');
    Route::get('/flights/price', [AdminFlightController::class, 'priceShow'])->name('flights.price.show');
    Route::get('/flights/book', [AdminFlightController::class, 'bookShow'])->name('flights.book');
    Route::post('/flights/book', [AdminFlightController::class, 'bookStore'])->name('flights.book.store');
    Route::get('/flights/confirmation', [AdminFlightController::class, 'confirmation'])->name('flights.confirmation');
    Route::post('/flights/ticket', [AdminFlightController::class, 'ticketIssue'])->name('flights.ticket');
    Route::match(['get', 'post'], '/flights/ops/{operation}', [AdminFlightController::class, 'operation'])->name('flights.operation')->where('operation', '[a-z0-9_]+');
});

Route::middleware(['auth', RoleMiddleware::class.':1|2|3'])->prefix('agent')->name('agent.')->group(function () {
    Route::get('/dashboard', [AgentDashboardController::class, 'adminDashboard'])->name('dashboard');

    Route::get('/roles', [AgentRoleController::class, 'index'])->name('roles');
    Route::get('/roles/create', [AgentRoleController::class, 'create'])->name('roles.create');
    Route::post('/roles', [AgentRoleController::class, 'store'])->name('roles.store');
    Route::get('/roles/{id}', [AgentRoleController::class, 'show'])->name('roles.show');
    Route::get('/roles/{id}/edit', [AgentRoleController::class, 'edit'])->name('roles.edit');
    Route::put('/roles/{id}', [AgentRoleController::class, 'update'])->name('roles.update');
    Route::delete('/roles/{id}', [AgentRoleController::class, 'destroy'])->name('roles.destroy');
    Route::post('/roles/{id}/permissions', [AgentRoleController::class, 'updatePermissions'])->name('roles.permissions');

    Route::get('/permissions', [AgentPermissionController::class, 'index'])->name('permissions');
    Route::get('/permissions/create', [AgentPermissionController::class, 'create'])->name('permissions.create');
    Route::post('/permissions', [AgentPermissionController::class, 'store'])->name('permissions.store');
    Route::get('/permissions/{id}/edit', [AgentPermissionController::class, 'edit'])->name('permissions.edit');
    Route::put('/permissions/{id}', [AgentPermissionController::class, 'update'])->name('permissions.update');
    Route::delete('/permissions/{id}', [AgentPermissionController::class, 'destroy'])->name('permissions.destroy');

    Route::get('/sub-agents', [AgentSubAgentController::class, 'index'])->name('managers');
    Route::get('/sub-agents/create', [AgentSubAgentController::class, 'create'])->name('managers.create');
    Route::post('/sub-agents', [AgentSubAgentController::class, 'store'])->name('managers.store');
    Route::get('/sub-agents/{id}/edit', [AgentSubAgentController::class, 'edit'])->name('managers.edit');
    Route::put('/sub-agents/{id}', [AgentSubAgentController::class, 'update'])->name('managers.update');
    Route::post('/sub-agents/{id}/permissions', [AgentSubAgentController::class, 'updatePermissions'])->name('managers.permissions');
    Route::delete('/sub-agents/{id}', [AgentSubAgentController::class, 'destroy'])->name('managers.destroy');
    Route::post('/sub-agents/{id}/restore', [AgentSubAgentController::class, 'restore'])->name('managers.restore');

    Route::get('/flights', [AgentFlightController::class, 'hub'])->name('flights.index');
    Route::match(['get', 'post'], '/flights/search', [AgentFlightController::class, 'search'])->name('flights.search');
    Route::post('/flights/price', [AgentFlightController::class, 'price'])->name('flights.price');
    Route::get('/flights/price', [AgentFlightController::class, 'priceShow'])->name('flights.price.show');
    Route::get('/flights/book', [AgentFlightController::class, 'bookShow'])->name('flights.book');
    Route::post('/flights/book', [AgentFlightController::class, 'bookStore'])->name('flights.book.store');
    Route::get('/flights/confirmation', [AgentFlightController::class, 'confirmation'])->name('flights.confirmation');
    Route::post('/flights/ticket', [AgentFlightController::class, 'ticketIssue'])->name('flights.ticket');
    Route::match(['get', 'post'], '/flights/ops/{operation}', [AgentFlightController::class, 'operation'])->name('flights.operation')->where('operation', '[a-z0-9_]+');
});

Route::middleware(['auth', RoleMiddleware::class.':1|2|3'])->prefix('sub-agent')->name('subagent.')->group(function () {
    Route::get('/dashboard', [SubAgentDashboardController::class, 'adminDashboard'])->name('dashboard');

    Route::get('/roles', [SubAgentRoleController::class, 'index'])->name('roles');
    Route::get('/roles/{id}', [SubAgentRoleController::class, 'show'])->name('roles.show');

    Route::get('/permissions', [SubAgentPermissionController::class, 'index'])->name('permissions');

    Route::get('/sub-agents', [SubAgentManagementController::class, 'index'])->name('managers');

    Route::get('/flights', [SubAgentFlightController::class, 'hub'])->name('flights.index');
    Route::match(['get', 'post'], '/flights/search', [SubAgentFlightController::class, 'search'])->name('flights.search');
    Route::post('/flights/price', [SubAgentFlightController::class, 'price'])->name('flights.price');
    Route::get('/flights/price', [SubAgentFlightController::class, 'priceShow'])->name('flights.price.show');
    Route::get('/flights/book', [SubAgentFlightController::class, 'bookShow'])->name('flights.book');
    Route::post('/flights/book', [SubAgentFlightController::class, 'bookStore'])->name('flights.book.store');
    Route::get('/flights/confirmation', [SubAgentFlightController::class, 'confirmation'])->name('flights.confirmation');
    Route::post('/flights/ticket', [SubAgentFlightController::class, 'ticketIssue'])->name('flights.ticket');
    Route::match(['get', 'post'], '/flights/ops/{operation}', [SubAgentFlightController::class, 'operation'])->name('flights.operation')->where('operation', '[a-z0-9_]+');
});

/*
|--------------------------------------------------------------------------
| Logout Routes
|--------------------------------------------------------------------------
*/

Route::get('/logout', function () {
    Auth::logout();

    return redirect()->route('login.form');
})->name('logout');

Route::middleware('auth')->group(function () {
    Route::get('/admin/logout', function () {
        Auth::logout();

        return redirect()->route('admin.login');
    })->name('admin.logout');
});
