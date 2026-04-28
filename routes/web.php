<?php

use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Auth;
use App\Http\Controllers\Auth\PasswordResetController;
use App\Http\Controllers\Auth\EmailVerificationController;
use App\Http\Controllers\User\UserController;
use App\Http\Controllers\Admin\AdminController;
use App\Http\Controllers\Admin\AdminUserController;
use App\Http\Controllers\Admin\RoleController;
use App\Http\Controllers\Admin\PermissionController;
use App\Http\Controllers\Admin\ManagersController;
use App\Http\Middleware\RoleMiddleware;

/*
|--------------------------------------------------------------------------
| Public Routes
|--------------------------------------------------------------------------
*/

Route::get('/', function () {
    return view('pages.home');
})->name('home');

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
| Admin Routes (Admin, Manager, Editor)
|--------------------------------------------------------------------------
*/

Route::middleware(['auth', RoleMiddleware::class . ':1|2|3'])->group(function () {
    // Admin Dashboard
    Route::get('/admin/dashboard', [AdminController::class, 'adminDashboard'])->name('admin.dashboard');

    // Users Management
    Route::get('/admin/users', [AdminUserController::class, 'index'])->name('admin.users');
    Route::get('/admin/users/create', [AdminUserController::class, 'create'])->name('admin.users.create');
    Route::post('/admin/users', [AdminUserController::class, 'store'])->name('admin.users.store');
    Route::get('/admin/users/{id}', [AdminUserController::class, 'show'])->name('admin.users.show');
    Route::get('/admin/users/{id}/edit', [AdminUserController::class, 'edit'])->name('admin.users.edit');
    Route::put('/admin/users/{id}', [AdminUserController::class, 'update'])->name('admin.users.update');
    Route::delete('/admin/users/{id}', [AdminUserController::class, 'destroy'])->name('admin.users.destroy');
    Route::post('/admin/users/{id}/restore', [AdminUserController::class, 'restore'])->name('admin.users.restore');

    // Roles Management
    Route::get('/admin/roles', [RoleController::class, 'index'])->name('admin.roles');
    Route::get('/admin/roles/create', [RoleController::class, 'create'])->name('admin.roles.create');
    Route::post('/admin/roles', [RoleController::class, 'store'])->name('admin.roles.store');
    Route::get('/admin/roles/{id}', [RoleController::class, 'show'])->name('admin.roles.show');
    Route::get('/admin/roles/{id}/edit', [RoleController::class, 'edit'])->name('admin.roles.edit');
    Route::put('/admin/roles/{id}', [RoleController::class, 'update'])->name('admin.roles.update');
    Route::delete('/admin/roles/{id}', [RoleController::class, 'destroy'])->name('admin.roles.destroy');
    Route::post('/admin/roles/{id}/permissions', [RoleController::class, 'updatePermissions'])->name('admin.roles.permissions');

    // Permissions Management
    Route::get('/admin/permissions', [PermissionController::class, 'index'])->name('admin.permissions');
    Route::get('/admin/permissions/create', [PermissionController::class, 'create'])->name('admin.permissions.create');
    Route::post('/admin/permissions', [PermissionController::class, 'store'])->name('admin.permissions.store');
    Route::get('/admin/permissions/{id}/edit', [PermissionController::class, 'edit'])->name('admin.permissions.edit');
    Route::put('/admin/permissions/{id}', [PermissionController::class, 'update'])->name('admin.permissions.update');
    Route::delete('/admin/permissions/{id}', [PermissionController::class, 'destroy'])->name('admin.permissions.destroy');
});

/*
|--------------------------------------------------------------------------
| Admin Only Routes (Role ID = 1)
|--------------------------------------------------------------------------
*/

Route::middleware(['auth', RoleMiddleware::class . ':1'])->group(function () {
    // Managers Management
    Route::get('/admin/managers', [ManagersController::class, 'index'])->name('admin.managers');
    Route::get('/admin/managers/create', [ManagersController::class, 'create'])->name('admin.managers.create');
    Route::post('/admin/managers', [ManagersController::class, 'store'])->name('admin.managers.store');
    Route::get('/admin/managers/{id}/edit', [ManagersController::class, 'edit'])->name('admin.managers.edit');
    Route::put('/admin/managers/{id}', [ManagersController::class, 'update'])->name('admin.managers.update');
    Route::post('/admin/managers/{id}/permissions', [ManagersController::class, 'updatePermissions'])->name('admin.managers.permissions');
    Route::delete('/admin/managers/{id}', [ManagersController::class, 'destroy'])->name('admin.managers.destroy');
    Route::post('/admin/managers/{id}/restore', [ManagersController::class, 'restore'])->name('admin.managers.restore');
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
