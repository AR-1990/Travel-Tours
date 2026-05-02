<?php

use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Auth;
use App\Http\Controllers\Auth\PasswordResetController;
use App\Http\Controllers\Auth\EmailVerificationController;
use App\Http\Controllers\Auth\TenantRegistrationController;
use App\Http\Controllers\User\UserController;
use App\Http\Controllers\BlogController;
use App\Http\Controllers\FrontController;
use App\Http\Controllers\Admin\AdminController;
use App\Http\Controllers\Admin\AdminUserController;
use App\Http\Controllers\Admin\RoleController;
use App\Http\Controllers\Admin\PermissionController;
use App\Http\Controllers\Admin\ManagersController;
use App\Http\Controllers\Admin\TenantController;
use App\Http\Controllers\Admin\BlogController as AdminBlogController;
use App\Http\Controllers\Agent\DashboardController as AgentDashboardController;
use App\Http\Controllers\Agent\RoleController as AgentRoleController;
use App\Http\Controllers\Agent\PermissionController as AgentPermissionController;
use App\Http\Controllers\Agent\SubAgentController as AgentSubAgentController;
use App\Http\Controllers\SubAgent\DashboardController as SubAgentDashboardController;
use App\Http\Controllers\SubAgent\RoleController as SubAgentRoleController;
use App\Http\Controllers\SubAgent\PermissionController as SubAgentPermissionController;
use App\Http\Controllers\SubAgent\SubAgentController as SubAgentManagementController;
use App\Http\Middleware\RoleMiddleware;

/*
|--------------------------------------------------------------------------
| Public Routes
|--------------------------------------------------------------------------
*/

Route::get('/', function () {
    return view('frontend.index');
})->name('home');
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

Route::middleware(['auth', RoleMiddleware::class . ':1'])->prefix('admin')->name('admin.')->group(function () {
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
});

Route::middleware(['auth', RoleMiddleware::class . ':1|2|3'])->prefix('agent')->name('agent.')->group(function () {
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
});

Route::middleware(['auth', RoleMiddleware::class . ':1|2|3'])->prefix('sub-agent')->name('subagent.')->group(function () {
    Route::get('/dashboard', [SubAgentDashboardController::class, 'adminDashboard'])->name('dashboard');

    Route::get('/roles', [SubAgentRoleController::class, 'index'])->name('roles');
    Route::get('/roles/{id}', [SubAgentRoleController::class, 'show'])->name('roles.show');

    Route::get('/permissions', [SubAgentPermissionController::class, 'index'])->name('permissions');

    Route::get('/sub-agents', [SubAgentManagementController::class, 'index'])->name('managers');
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

/*
|--------------------------------------------------------------------------
| Front Routes
|--------------------------------------------------------------------------
*/
Route::get('/home', [FrontController::class, 'index']);

Route::get('/404', [FrontController::class, 'notFound']);
Route::get('/about', [FrontController::class, 'about']);
Route::get('/activity-add', [FrontController::class, 'activityAdd']);
Route::get('/activity-booking', [FrontController::class, 'activityBooking']);
Route::get('/activity-full-width', [FrontController::class, 'activityFullWidth']);
Route::get('/activity-grid', [FrontController::class, 'activityGrid']);
Route::get('/activity-list', [FrontController::class, 'activityList']);
Route::get('/activity-search-result', [FrontController::class, 'activitySearchResult']);
Route::get('/activity-single', [FrontController::class, 'activitySingle']);

Route::get('/become-expert', [FrontController::class, 'becomeExpert']);
// Route::get('/blog', [FrontController::class, 'blog']);
// Route::get('/blog-single', [FrontController::class, 'blogSingle']);

Route::get('/booking-confirm', [FrontController::class, 'bookingConfirm']);

Route::get('/car-add', [FrontController::class, 'carAdd']);
Route::get('/car-booking', [FrontController::class, 'carBooking']);
Route::get('/car-full-width', [FrontController::class, 'carFullWidth']);
Route::get('/car-grid', [FrontController::class, 'carGrid']);
Route::get('/car-list', [FrontController::class, 'carList']);
Route::get('/car-search-result', [FrontController::class, 'carSearchResult']);
Route::get('/car-single', [FrontController::class, 'carSingle']);

Route::get('/career', [FrontController::class, 'career']);
Route::get('/career-single', [FrontController::class, 'careerSingle']);

Route::get('/cart', [FrontController::class, 'cart']);
Route::get('/checkout', [FrontController::class, 'checkout']);
Route::get('/coming-soon', [FrontController::class, 'comingSoon']);
Route::get('/contact', [FrontController::class, 'contact']);

Route::get('/cruise-add', [FrontController::class, 'cruiseAdd']);
Route::get('/cruise-booking', [FrontController::class, 'cruiseBooking']);
Route::get('/cruise-full-width', [FrontController::class, 'cruiseFullWidth']);
Route::get('/cruise-grid', [FrontController::class, 'cruiseGrid']);
Route::get('/cruise-list', [FrontController::class, 'cruiseList']);
Route::get('/cruise-search-result', [FrontController::class, 'cruiseSearchResult']);
Route::get('/cruise-single', [FrontController::class, 'cruiseSingle']);

// Route::get('/dashboard', [FrontController::class, 'dashboard']);
Route::get('/destination', [FrontController::class, 'destination']);
Route::get('/faq', [FrontController::class, 'faq']);

Route::get('/flight-add', [FrontController::class, 'flightAdd']);
Route::get('/flight-booking', [FrontController::class, 'flightBooking']);
Route::get('/flight-full-width', [FrontController::class, 'flightFullWidth']);
Route::get('/flight-grid', [FrontController::class, 'flightGrid']);
Route::get('/flight-list', [FrontController::class, 'flightList']);
Route::get('/flight-search-result', [FrontController::class, 'flightSearchResult']);
Route::get('/flight-single', [FrontController::class, 'flightSingle']);

// Route::get('/forgot-password', [FrontController::class, 'forgotPassword']);
Route::get('/gallery', [FrontController::class, 'gallery']);

Route::get('/hotel-add', [FrontController::class, 'hotelAdd']);
Route::get('/hotel-booking', [FrontController::class, 'hotelBooking']);
Route::get('/hotel-full-width', [FrontController::class, 'hotelFullWidth']);
Route::get('/hotel-grid', [FrontController::class, 'hotelGrid']);
Route::get('/hotel-list', [FrontController::class, 'hotelList']);
Route::get('/hotel-room-add', [FrontController::class, 'hotelRoomAdd']);
Route::get('/hotel-room-full-width', [FrontController::class, 'hotelRoomFullWidth']);
Route::get('/hotel-room-grid', [FrontController::class, 'hotelRoomGrid']);
Route::get('/hotel-room-list', [FrontController::class, 'hotelRoomList']);
Route::get('/hotel-room-search-result', [FrontController::class, 'hotelRoomSearchResult']);
Route::get('/hotel-room-single', [FrontController::class, 'hotelRoomSingle']);
Route::get('/hotel-search-result', [FrontController::class, 'hotelSearchResult']);
Route::get('/hotel-single', [FrontController::class, 'hotelSingle']);

// Route::get('/login', [FrontController::class, 'login']);
Route::get('/pricing', [FrontController::class, 'pricing']);
Route::get('/privacy', [FrontController::class, 'privacy']);

// Route::get('/profile', [FrontController::class, 'profile']);
Route::get('/profile-booking-history', [FrontController::class, 'profileBookingHistory']);
Route::get('/profile-booking', [FrontController::class, 'profileBooking']);
Route::get('/profile-listing', [FrontController::class, 'profileListing']);
Route::get('/profile-message', [FrontController::class, 'profileMessage']);
Route::get('/profile-notification', [FrontController::class, 'profileNotification']);
Route::get('/profile-setting', [FrontController::class, 'profileSetting']);
Route::get('/profile-wallet', [FrontController::class, 'profileWallet']);
Route::get('/profile-wishlist', [FrontController::class, 'profileWishlist']);

// Route::get('/register', [FrontController::class, 'register']);
Route::get('/service', [FrontController::class, 'service']);
Route::get('/service-single', [FrontController::class, 'serviceSingle']);

Route::get('/team', [FrontController::class, 'team']);
Route::get('/terms', [FrontController::class, 'terms']);
Route::get('/testimonial', [FrontController::class, 'testimonial']);

Route::get('/tour-add', [FrontController::class, 'tourAdd']);
Route::get('/tour-booking', [FrontController::class, 'tourBooking']);
Route::get('/tour-full-width', [FrontController::class, 'tourFullWidth']);
Route::get('/tour-grid', [FrontController::class, 'tourGrid']);
Route::get('/tour-list', [FrontController::class, 'tourList']);
Route::get('/tour-search-result', [FrontController::class, 'tourSearchResult']);
Route::get('/tour-single', [FrontController::class, 'tourSingle']);
