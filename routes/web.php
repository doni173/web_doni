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
use App\Http\Controllers\CartController;
use App\Http\Controllers\StockReportController;
use App\Http\Controllers\RoleController;
use App\Http\Controllers\PermissionController;
use App\Http\Controllers\PurchaseController;
use App\Http\Controllers\SupplierController;
use App\Http\Controllers\ReportController;


Route::prefix('items')->group(function () {

    // halaman utama item
    Route::get('/', [ItemController::class, 'index'])
        ->name('items.index');

    // simpan item
    Route::post('/', [ItemController::class, 'store'])
        ->name('items.store');

    // update item
    Route::put('/{id}', [ItemController::class, 'update'])
        ->name('items.update');

    // hapus item
    Route::delete('/{id}', [ItemController::class, 'destroy'])
        ->name('items.destroy');

    // 🔑 AKTIFKAN FSN (INI YANG ERROR TADI)
    Route::post('/{id}/activate-fsn', [ItemController::class, 'activateFSN'])
        ->name('items.activate.fsn');

    // hitung fsn
    Route::post('/calculate-fsn', [ItemController::class, 'calculateFSN'])
        ->name('items.calculate.fsn');

});

// ========================================
// AUTHENTICATION ROUTES
// ========================================
Route::get('/', [LoginController::class, 'showLoginForm'])->name('login')->middleware('guest');
Route::post('/', [LoginController::class, 'login']);
Route::post('/logout', [LoginController::class, 'logout'])->name('logout')->middleware('auth');

// ========================================
// DASHBOARD ROUTES
// ========================================
Route::middleware('auth')->group(function () {
    Route::get('/dashboard', [DashboardController::class, 'index'])
        ->middleware('role:admin')
        ->name('dashboard');
    
    Route::get('/dashboard_kasir', [DashboardController::class, 'kasirDashboard'])
        ->middleware('role:kasir')
        ->name('dashboard_kasir');
});

// ========================================
// RESOURCE ROUTES
// ========================================
Route::middleware('auth')->group(function () {
    Route::resource('categories', CategoryController::class);
    Route::resource('items', ItemController::class);
    Route::resource('brands', BrandController::class);
    Route::resource('services', ServiceController::class);
    Route::resource('suppliers', SupplierController::class);
});

// ========================================
// FSN ANALYSIS ROUTES
// ========================================
Route::middleware('auth')->group(function () {
    Route::post('/items/calculate-fsn', [ItemController::class, 'calculateFSN'])->name('items.calculate.fsn');
    Route::get('/items/{id}/calculate-fsn', [ItemController::class, 'calculateSingleFSN'])->name('items.calculate.single.fsn');
    Route::get('/fsn-report', [ItemController::class, 'fsnReport'])->name('fsn.report');
});

// ========================================
// USER MANAGEMENT ROUTES
// ========================================
Route::prefix('users')->middleware('auth')->group(function () {
    Route::get('/', [UserController::class, 'index'])->name('users.index');
    Route::get('/create', [UserController::class, 'create'])->name('users.create');
    Route::post('/', [UserController::class, 'store'])->name('users.store');
    Route::get('/{id}/edit', [UserController::class, 'edit'])->name('users.edit');
    Route::put('/{id}', [UserController::class, 'update'])->name('users.update');
    Route::delete('/{id}', [UserController::class, 'destroy'])->name('users.destroy');
});

// ========================================
// PROFILE ROUTES
// ========================================
Route::prefix('profile')->middleware('auth')->group(function () {
    Route::get('/', [UserController::class, 'profile'])->name('profile');
    Route::put('/', [UserController::class, 'updateProfile'])->name('profile.update');
    Route::put('/password', [UserController::class, 'updatePassword'])->name('profile.password.update');
});

// ========================================
// CART ROUTES
// ========================================
Route::middleware('auth')->group(function () {
    Route::post('/cart/add', [CartController::class, 'addToCart'])->name('cart.add');
    Route::delete('/cart/remove/{id}', [CartController::class, 'removeFromCart'])->name('cart.remove');
});

// ========================================
// SALES ROUTES (FIXED - Mengikuti Konvensi RESTful)
// ========================================
Route::prefix('sale')->middleware('auth')->group(function () {
    // Halaman form transaksi penjualan
    Route::get('/', [SaleController::class, 'index'])->name('sale.index');
    
    // Proses simpan transaksi (POST langsung ke /sale, bukan /sale/store)
    Route::post('/', [SaleController::class, 'store'])->name('sale.store');
    
    // History penjualan
    Route::get('/history', [SaleController::class, 'history'])->name('sale.history');
    
    // Detail penjualan (harus di bawah /history agar tidak conflict)
    Route::get('/{id}', [SaleController::class, 'show'])->name('sale.show');
});

// ========================================
// PURCHASE ROUTES (FIXED - Konsisten dengan Sales)
// ========================================
Route::prefix('purchase')->middleware('auth')->group(function () {
    // Halaman form pembelian
    Route::get('/', [PurchaseController::class, 'index'])->name('purchase.index');
    
    // Proses simpan pembelian
    Route::post('/', [PurchaseController::class, 'store'])->name('purchase.store');
    
    // History pembelian
    Route::get('/history', [PurchaseController::class, 'history'])->name('purchase.history');
    
    // Report pembelian
    Route::get('/report', [PurchaseController::class, 'report'])->name('purchase.report');
    
    // Detail pembelian
    Route::get('/{id}', [PurchaseController::class, 'show'])->name('purchase.show');
    
    // Hapus pembelian
    Route::delete('/{id}', [PurchaseController::class, 'destroy'])->name('purchase.destroy');
});

// ========================================
// REPORT ROUTES (Sales Report)
// ========================================
Route::middleware('auth')->group(function () {
    Route::get('/report', [ReportController::class, 'index'])->name('sale.report');
    Route::get('/report/pdf', [ReportController::class, 'exportPDF'])->name('sale.report.pdf');
});

// ========================================
// STOCK ROUTES
// ========================================
Route::prefix('stock')->middleware('auth')->group(function () {
    Route::get('/report', [StockReportController::class, 'index'])->name('stock.report');
    Route::post('/update', [StockReportController::class, 'updateStock'])->name('stock.update');
});

// ========================================
// ROLE & PERMISSION ROUTES (Admin Only)
// ========================================
Route::middleware(['auth', 'role:admin'])->group(function () {
    Route::resource('roles', RoleController::class);
    Route::resource('permissions', PermissionController::class);
});