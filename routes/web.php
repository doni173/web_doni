<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\LoginController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\CategoryController;
use App\Http\Controllers\ItemController;
use App\Http\Controllers\BrandController;
use App\Http\Controllers\UserController;
use App\Http\Controllers\ServiceController;
use App\Http\Controllers\SaleController;

use App\Http\Controllers\StockReportController;
use App\Http\Controllers\RoleController;
use App\Http\Controllers\PermissionController;
use App\Http\Controllers\PurchaseController;
use App\Http\Controllers\SupplierController;
use App\Http\Controllers\ReportController;

// ============================================================
// AUTHENTICATION ROUTES (Guest Only)
// ============================================================
Route::middleware('guest')->group(function () {
    Route::get('/', [LoginController::class, 'showLoginForm'])->name('login');
    Route::post('/', [LoginController::class, 'login']);
});

Route::post('/logout', [LoginController::class, 'logout'])
    ->name('logout')
    ->middleware('auth');

// ============================================================
// ADMIN ONLY ROUTES
// ============================================================
Route::middleware(['auth', 'role:admin'])->group(function () {

    // Dashboard Admin
    Route::get('/dashboard', [DashboardController::class, 'index'])
        ->name('dashboard');

    // Master Data
    Route::resource('categories', CategoryController::class);
    Route::resource('brands', BrandController::class);
    Route::resource('services', ServiceController::class);
    Route::resource('suppliers', SupplierController::class);

    // Barang / Inventory
    Route::prefix('items')->name('items.')->group(function () {
        Route::get('/',                   [ItemController::class, 'index'])->name('index');
        Route::post('/',                  [ItemController::class, 'store'])->name('store');
        Route::put('/{id}',               [ItemController::class, 'update'])->name('update');
        Route::delete('/{id}',            [ItemController::class, 'destroy'])->name('destroy');
        Route::post('/calculate-fsn',     [ItemController::class, 'calculateFSN'])->name('calculate.fsn');
        Route::get('/{id}/calculate-fsn', [ItemController::class, 'calculateSingleFSN'])->name('calculate.single.fsn');
    });

    // FSN Analysis
    Route::get('/fsn-report', [ItemController::class, 'fsnReport'])->name('fsn.report');

    // User Management
    Route::prefix('users')->name('users.')->group(function () {
        Route::get('/',          [UserController::class, 'index'])->name('index');
        Route::get('/create',    [UserController::class, 'create'])->name('create');
        Route::post('/',         [UserController::class, 'store'])->name('store');
        Route::get('/{id}/edit', [UserController::class, 'edit'])->name('edit');
        Route::put('/{id}',      [UserController::class, 'update'])->name('update');
        Route::delete('/{id}',   [UserController::class, 'destroy'])->name('destroy');
    });

    // Pembelian (Purchase)
    Route::prefix('purchase')->name('purchase.')->group(function () {
        Route::get('/',        [PurchaseController::class, 'index'])->name('index');
        Route::post('/',       [PurchaseController::class, 'store'])->name('store');
        Route::get('/history', [PurchaseController::class, 'history'])->name('history');
        Route::get('/report',  [PurchaseController::class, 'report'])->name('report');
        Route::get('/{id}',    [PurchaseController::class, 'show'])->name('show');
        Route::delete('/{id}', [PurchaseController::class, 'destroy'])->name('destroy');
    });

    // Laporan Penjualan
    Route::get('/report',     [ReportController::class, 'index'])->name('sale.report');
    Route::get('/report/pdf', [ReportController::class, 'exportPDF'])->name('sale.report.pdf');

    // Laporan Stok
    Route::prefix('stock')->name('stock.')->group(function () {
        Route::get('/report',  [StockReportController::class, 'index'])->name('report');
        Route::post('/update', [StockReportController::class, 'updateStock'])->name('update');
    });

    // Role & Permission Management
    Route::resource('roles', RoleController::class);
    Route::resource('permissions', PermissionController::class);
});

// ============================================================
// KASIR ONLY ROUTES
// ============================================================
Route::middleware(['auth', 'role:kasir'])->group(function () {

    // Dashboard Kasir
    Route::get('/dashboard-kasir', [DashboardController::class, 'kasirDashboard'])
        ->name('dashboard_kasir');
});

// ============================================================
// SHARED ROUTES (Admin & Kasir)
// ============================================================
Route::middleware(['auth', 'role:admin,kasir'])->group(function () {

    // Profile
    Route::prefix('profile')->name('profile.')->group(function () {
        Route::get('/',         [UserController::class, 'profile'])->name('index');
        Route::put('/',         [UserController::class, 'updateProfile'])->name('update');
        Route::put('/password', [UserController::class, 'updatePassword'])->name('password.update');
    });

    // ✅ Penjualan — dipindah ke shared agar admin & kasir bisa akses
    Route::prefix('sale')->name('sale.')->group(function () {
        Route::get('/',           [SaleController::class, 'index'])->name('index');
        Route::post('/',          [SaleController::class, 'store'])->name('store');
        Route::get('/history',    [SaleController::class, 'history'])->name('history');
        Route::get('/print/{id}', [SaleController::class, 'print'])->name('print');
        Route::get('/{id}',       [SaleController::class, 'show'])->name('show');
        Route::delete('/{id}',    [SaleController::class, 'destroy'])->name('destroy');
    });

});