<?php

use App\Http\Controllers\Admin;
use App\Http\Controllers\WebAuthController;
use Illuminate\Support\Facades\Route;

// Redirect root to login
Route::get('/', function () {
    return redirect('/login');
});

// Authentication routes
Route::get('/login', [WebAuthController::class, 'showLogin'])->name('login');
Route::post('/login', [WebAuthController::class, 'login'])->name('login.post');
Route::post('/logout', [WebAuthController::class, 'logout'])->name('logout');

// System Admin routes
Route::middleware(['auth', 'super_admin'])->prefix('admin')->name('admin.')->group(function () {
    // Dashboard
    Route::get('/dashboard', [Admin\DashboardController::class, 'index'])->name('dashboard');

    // Organizations
    Route::resource('organizations', Admin\OrganizationController::class);

    // Projects
    Route::resource('projects', Admin\ProjectController::class);

    // Users
    Route::resource('users', Admin\UserController::class);

    // Orders (view only)
    Route::get('orders', [Admin\OrderController::class, 'index'])->name('orders.index');
    Route::get('orders/{order}', [Admin\OrderController::class, 'show'])->name('orders.show');
});

// Tenant Admin routes
Route::middleware(['auth', 'tenant_admin'])->prefix('dashboard')->name('dashboard.')->group(function () {
    // Dashboard
    Route::get('/', [\App\Http\Controllers\DashboardController::class, 'index'])->name('index');

    // Users management
    Route::resource('users', \App\Http\Controllers\UserController::class);

    // Orders management - Apply tenant service resolution for customization
    Route::middleware(\App\Http\Middleware\ResolveTenantServices::class)->group(function () {
        Route::resource('orders', \App\Http\Controllers\Tenant\OrderController::class);
    });
});
