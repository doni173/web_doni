<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\LoginController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\CategoryController;
use App\Http\Controllers\ItemsController;
use App\Http\Controllers\BrandController;
use App\Http\Controllers\UserController;
use App\Http\Controllers\ServiceController;
use App\Http\Controllers\SaleController;
use App\Http\Controllers\CartController;
use App\Http\Controllers\StockReportController;
use App\Http\Controllers\RoleController;
use App\Http\Controllers\PermissionController;
use App\Http\Controllers\PurchaseController;
use Illuminate\Support\Facades\Auth;

Route::middleware(['auth'])->group(function () {
    
    // Route untuk halaman pembelian
    Route::get('/purchase', [PurchaseController::class, 'index'])->name('purchase.index');
    
    // Route untuk menyimpan transaksi pembelian
    Route::post('/purchase/store', [PurchaseController::class, 'store'])->name('purchase.store');
    
    // Route untuk melihat detail pembelian
    Route::get('/purchase/{id}', [PurchaseController::class, 'show'])->name('purchase.show');
    
    // Route untuk riwayat pembelian
    Route::get('/purchase/history/list', [PurchaseController::class, 'history'])->name('purchase.history');
    
    // Route untuk laporan pembelian
    Route::get('/purchase/report/generate', [PurchaseController::class, 'report'])->name('purchase.report');
    
    // Route untuk menghapus pembelian (opsional)
    Route::delete('/purchase/{id}', [PurchaseController::class, 'destroy'])->name('purchase.destroy');
    
});


Route::get('/report', [SaleController::class, 'report'])->name('sales.report');

// Route untuk menampilkan form login (GET) - hanya bisa diakses oleh pengguna yang belum login
Route::get('/', [LoginController::class, 'showLoginForm'])->name('login')->middleware('guest');

// Route untuk menangani proses login (POST)
Route::post('/', [LoginController::class, 'login']);

// Route untuk logout (POST) - menggunakan controller untuk logout
Route::post('/logout', [LoginController::class, 'logout'])->name('logout')->middleware('auth');

// Rute untuk dashboard admin (Hanya admin yang bisa mengakses halaman ini)
Route::get('/dashboard', [DashboardController::class, 'index'])
    ->middleware(['auth', 'role:admin'])
    ->name('dashboard');

// Rute untuk dashboard kasir (Hanya kasir yang bisa mengakses halaman ini)
Route::get('/dashboard_kasir', [DashboardController::class, 'kasirDashboard'])
    ->middleware(['auth', 'role:kasir'])
    ->name('dashboard_kasir');

// Route untuk kategori (CRUD) - hanya bisa diakses oleh pengguna yang sudah login
Route::resource('categories', CategoryController::class)
    ->middleware('auth')
    ->names([
        'index' => 'categories.index',
        'create' => 'categories.create',
        'store' => 'categories.store',
        'edit' => 'categories.edit',
        'update' => 'categories.update',
        'destroy' => 'categories.destroy',
    ]);

// Route untuk items (CRUD) - hanya bisa diakses oleh pengguna yang sudah login
Route::resource('items', ItemsController::class)
    ->middleware('auth')
    ->names([
        'index' => 'items.index',
        'create' => 'items.create',
        'store' => 'items.store',
        'edit' => 'items.edit',
        'update' => 'items.update',
        'destroy' => 'items.destroy',
    ]);

// Route untuk brands (CRUD) - hanya bisa diakses oleh pengguna yang sudah login
Route::resource('brands', BrandController::class)
    ->middleware('auth')
    ->names([
        'index' => 'brands.index',
        'create' => 'brands.create',
        'store' => 'brands.store',
        'edit' => 'brands.edit',
        'update' => 'brands.update',
        'destroy' => 'brands.destroy',
    ]);

// Route untuk services (CRUD) - hanya bisa diakses oleh pengguna yang sudah login
Route::resource('services', ServiceController::class)
    ->middleware('auth')
    ->names([
        'index' => 'services.index',
        'create' => 'services.create',
        'store' => 'services.store',
        'edit' => 'services.edit',
        'update' => 'services.update',
        'destroy' => 'services.destroy',
    ]);

// Route untuk users (CRUD) - hanya bisa diakses oleh pengguna yang sudah login
Route::prefix('users')->middleware('auth')->group(function () {
    Route::get('/', [UserController::class, 'index'])->name('users.index');
    Route::get('/create', [UserController::class, 'create'])->name('users.create');
    Route::post('/', [UserController::class, 'store'])->name('users.store');
    Route::get('/{id}/edit', [UserController::class, 'edit'])->name('users.edit');
    Route::put('/{id}', [UserController::class, 'update'])->name('users.update');
    Route::delete('/{id}', [UserController::class, 'destroy'])->name('users.destroy');
});

// ========================================
// ROUTE UNTUK CART
// ========================================
Route::post('/cart/add', [CartController::class, 'addToCart'])->name('cart.add')->middleware('auth');
Route::delete('/cart/remove/{id}', [CartController::class, 'removeFromCart'])->name('cart.remove')->middleware('auth');

// ========================================
// ROUTE UNTUK PENJUALAN (SALE)
// ========================================
Route::prefix('sale')->middleware('auth')->group(function () {
    // Halaman penjualan (menampilkan produk yang akan dibeli)
    Route::get('/', [SaleController::class, 'index'])->name('sale.index');
    
    // Menyimpan penjualan - INI YANG PENTING!
    Route::post('/store', [SaleController::class, 'store'])->name('sale.store');
    
    // History penjualan - HARUS DI ATAS route {id} agar tidak konflik
    Route::get('/history', [SaleController::class, 'history'])->name('sale.history');
    
    // Laporan penjualan
    Route::get('/report', [SaleController::class, 'report'])->name('sale.report');
    
    // Detail penjualan - HARUS DI BAWAH route yang lebih spesifik
    Route::get('/{id}', [SaleController::class, 'show'])->name('sale.show');
});

// ========================================
// ROUTE UNTUK STOK BARANG
// ========================================
Route::prefix('stock')->middleware('auth')->group(function () {
    // Laporan stok barang
    Route::get('/report', [StockReportController::class, 'index'])->name('stock.report');
    
    // Update stok barang secara manual
    Route::post('/update', [StockReportController::class, 'updateStock'])->name('stock.update');
});

// ========================================
// ROUTE UNTUK ROLE & PERMISSIONS (Admin Only)
// ========================================
Route::resource('roles', RoleController::class)
    ->middleware(['auth', 'role:admin'])
    ->names([
        'index' => 'roles.index',
        'create' => 'roles.create',
        'store' => 'roles.store',
        'edit' => 'roles.edit',
        'update' => 'roles.update',
        'destroy' => 'roles.destroy',
    ]);

Route::resource('permissions', PermissionController::class)
    ->middleware(['auth', 'role:admin'])
    ->names([
        'index' => 'permissions.index',
        'create' => 'permissions.create',
        'store' => 'permissions.store',
        'edit' => 'permissions.edit',
        'update' => 'permissions.update',
        'destroy' => 'permissions.destroy',
    ]);

// ========================================
// ROUTE UNTUK PROFIL PENGGUNA
// ========================================
Route::prefix('profile')->middleware('auth')->group(function () {
    // Halaman profil pengguna
    Route::get('/', [UserController::class, 'profile'])->name('profile');
    
    // Memperbarui profil pengguna
    Route::put('/', [UserController::class, 'updateProfile'])->name('profile.update');
    
    // Memperbarui password pengguna
    Route::put('/password', [UserController::class, 'updatePassword'])->name('profile.password.update');
});