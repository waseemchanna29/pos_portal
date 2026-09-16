<?php

use App\Http\Controllers\Auth\LoginController;
use App\Http\Controllers\Auth\LogoutController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\SuperAdmin\OutletController;
use App\Http\Controllers\SuperAdmin\UserController;
use App\Http\Controllers\Admin\CategoryController;
use App\Http\Controllers\Admin\ProductController;
use App\Http\Controllers\Admin\SalesmanController;
use App\Http\Controllers\Admin\PurchaseOrderController;
use App\Http\Controllers\Salesman\PosController;
use App\Http\Controllers\Salesman\BookingController;
use App\Http\Controllers\Salesman\PaymentController;
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

// ── Super Admin ────────────────────────────────────────────────────────────────
Route::middleware(['auth', 'superadmin'])->prefix('superadmin')->name('superadmin.')->group(function () {
    Route::get('/dashboard', [DashboardController::class, 'superadmin'])->name('dashboard');

    // Outlets CRUD + toggle
    Route::resource('outlets', OutletController::class);
    Route::post('/outlets/{outlet}/toggle',       [OutletController::class, 'toggleStatus'])->name('outlets.toggle');

    // Assign / unassign admin to outlet
    Route::post('/outlets/{outlet}/assign-admin', [OutletController::class, 'assignAdmin'])->name('outlets.assign-admin');

    // AJAX: unassigned admins list (for assign modal)
    Route::get('/available-admins', [OutletController::class, 'availableAdmins'])->name('available-admins');
});

// ── Admin ──────────────────────────────────────────────────────────────────────
Route::middleware(['auth', 'admin'])->prefix('admin')->name('admin.')->group(function () {
    Route::get('/dashboard', [DashboardController::class, 'admin'])->name('dashboard');

    // Categories (own outlet only)
    Route::resource('categories', CategoryController::class)->except(['show']);
    Route::post('/categories/{category}/toggle', [CategoryController::class, 'toggleStatus'])->name('categories.toggle');

    // Products (own outlet only)
    Route::resource('products', ProductController::class);
    Route::post('/products/{product}/toggle',    [ProductController::class, 'toggleStatus'])->name('products.toggle');

    // Salesmen (own outlet only)
    Route::resource('salesmen', SalesmanController::class)->except(['show']);
    Route::post('/salesmen/{salesman}/toggle',   [SalesmanController::class, 'toggleStatus'])->name('salesmen.toggle');

    // Purchase Orders (inventory management)
    Route::resource('purchase-orders', PurchaseOrderController::class)->except(['edit', 'update']);
    Route::post('/purchase-orders/{purchaseOrder}/receive', [PurchaseOrderController::class, 'receive'])->name('purchase-orders.receive');
});

// ── Salesman ───────────────────────────────────────────────────────────────────
// ── Salesman ───────────────────────────────────────────────────────────────────
Route::middleware(['auth', 'salesman'])->prefix('salesman')->name('salesman.')->group(function () {
    Route::get('/dashboard', [DashboardController::class, 'salesman'])->name('dashboard');

    // POS
    Route::get('/pos',          [PosController::class, 'index'])->name('pos.index');
    Route::post('/pos',         [PosController::class, 'store'])->name('pos.store');
    Route::get('/pos/{order}',  [PosController::class, 'receipt'])->name('pos.receipt');

    // Bookings
    Route::get('/bookings',                  [BookingController::class, 'index'])->name('bookings.index');
    Route::get('/bookings/create',           [BookingController::class, 'create'])->name('bookings.create');
    Route::post('/bookings',                 [BookingController::class, 'store'])->name('bookings.store');
    Route::get('/bookings/{order}',          [BookingController::class, 'show'])->name('bookings.show');
    Route::post('/bookings/{order}/cancel',  [BookingController::class, 'cancel'])->name('bookings.cancel');

    // Payments (on bookings)
    Route::post('/orders/{order}/payments',  [PaymentController::class, 'store'])->name('payments.store');
});
