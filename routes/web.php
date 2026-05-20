<?php

use App\Http\Controllers\Auth\LoginController;
use App\Http\Controllers\Auth\LogoutController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\SuperAdmin\OutletController;
use App\Http\Controllers\SuperAdmin\UserController;
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

// Super Admin
Route::middleware(['auth', 'superadmin'])->prefix('superadmin')->name('superadmin.')->group(function () {
    Route::get('/dashboard', [DashboardController::class, 'superadmin'])->name('dashboard');

    Route::resource('outlets', OutletController::class);
    Route::post('/outlets/{outlet}/toggle', [OutletController::class, 'toggleStatus'])->name('outlets.toggle');

    Route::resource('users', UserController::class)->except(['show']);
    Route::post('/users/{user}/toggle', [UserController::class, 'toggleStatus'])->name('users.toggle');
});

// Admin
Route::middleware(['auth', 'admin'])->prefix('admin')->name('admin.')->group(function () {
    Route::get('/dashboard', [DashboardController::class, 'admin'])->name('dashboard');
});

// Salesman
Route::middleware(['auth'])->prefix('salesman')->name('salesman.')->group(function () {
    Route::get('/dashboard', [DashboardController::class, 'salesman'])->name('dashboard');
});