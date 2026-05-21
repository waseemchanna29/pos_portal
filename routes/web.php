<?php

use App\Http\Controllers\Auth\LoginController;
use App\Http\Controllers\Auth\LogoutController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\SuperAdmin\OutletController;
use App\Http\Controllers\SuperAdmin\UserController;
use App\Http\Controllers\SuperAdmin\CategoryController as SuperAdminCategoryController;
use App\Http\Controllers\SuperAdmin\ProductController as SuperAdminProductController;
use App\Http\Controllers\Admin\CategoryController;
use App\Http\Controllers\Admin\ProductController;
use App\Http\Controllers\Admin\SalesmanController;
use Illuminate\Support\Facades\Route;

// Root
Route::get('/', fn() => redirect()->route('login'));

// Guest
Route::middleware('guest')->group(function () {
    Route::get('/login',  [LoginController::class, 'showLoginForm'])->name('login');
    Route::post('/login', [LoginController::class, 'login'])->name('login.post');
});

// Logout
Route::post('/logout', [LogoutController::class, 'logout'])->name('logout')->middleware('auth');

// ── Super Admin ────────────────────────────────────────────
Route::middleware(['auth', 'superadmin'])->prefix('superadmin')->name('superadmin.')->group(function () {

    Route::get('/dashboard', [DashboardController::class, 'superadmin'])->name('dashboard');

    // Outlets
    Route::resource('outlets', OutletController::class);
    Route::post('/outlets/{outlet}/toggle', [OutletController::class, 'toggleStatus'])->name('outlets.toggle');

    // Users (admin + salesman)
    Route::resource('users', UserController::class)->except(['show']);
    Route::post('/users/{user}/toggle', [UserController::class, 'toggleStatus'])->name('users.toggle');

    // Categories (across all outlets)
    Route::resource('categories', SuperAdminCategoryController::class)->except(['show']);
    Route::post('/categories/{category}/toggle', [SuperAdminCategoryController::class, 'toggleStatus'])->name('categories.toggle');

    // Products (across all outlets)
    Route::resource('products', SuperAdminProductController::class);
    Route::post('/products/{product}/toggle', [SuperAdminProductController::class, 'toggleStatus'])->name('products.toggle');

    // AJAX: load categories by outlet (used in product create form)
    Route::get('/outlets/{outlet}/categories', function (\App\Models\Outlet $outlet) {
        $categories = \App\Models\Category::where('outlet_id', $outlet->id)
            ->where('is_active', true)
            ->orderBy('name')
            ->get(['id', 'name']);
        return response()->json($categories);
    })->name('outlets.categories');
});

// ── Admin ──────────────────────────────────────────────────
Route::middleware(['auth', 'admin'])->prefix('admin')->name('admin.')->group(function () {

    Route::get('/dashboard', [DashboardController::class, 'admin'])->name('dashboard');

    // Categories
    Route::resource('categories', CategoryController::class)->except(['show']);
    Route::post('/categories/{category}/toggle', [CategoryController::class, 'toggleStatus'])->name('categories.toggle');

    // Products
    Route::resource('products', ProductController::class);
    Route::post('/products/{product}/toggle', [ProductController::class, 'toggleStatus'])->name('products.toggle');

    // Salesmen
    Route::resource('salesmen', SalesmanController::class)->except(['show']);
    Route::post('/salesmen/{salesman}/toggle', [SalesmanController::class, 'toggleStatus'])->name('salesmen.toggle');
});

// ── Salesman ───────────────────────────────────────────────
Route::middleware(['auth'])->prefix('salesman')->name('salesman.')->group(function () {
    Route::get('/dashboard', [DashboardController::class, 'salesman'])->name('dashboard');
});
