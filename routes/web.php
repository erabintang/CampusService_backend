<?php

use App\Http\Controllers\Admin\CategoryController;
use App\Http\Controllers\Admin\DashboardController;
use App\Http\Controllers\Admin\OrderController as AdminOrderController;
use App\Http\Controllers\Admin\ProductController as AdminProductController;
use App\Http\Controllers\Admin\UploadController as AdminUploadController;
use App\Http\Controllers\Admin\UserController;
use App\Http\Controllers\Api\AdminUploadController as ApiAdminUploadController;
use App\Http\Controllers\Api\AuthController as ApiAuthController;
use App\Http\Controllers\Api\CategoryController as ApiCategoryController;
use App\Http\Controllers\Api\OrderController as ApiOrderController;
use App\Http\Controllers\Api\ProductController as ApiProductController;
use App\Http\Controllers\Api\UploadController;
use App\Http\Controllers\Api\Admin\CategoryController as ApiAdminCategoryController;
use App\Http\Controllers\Api\Admin\DashboardController as ApiAdminDashboardController;
use App\Http\Controllers\Api\Admin\OrderController as ApiAdminOrderController;
use App\Http\Controllers\Api\Admin\ProductController as ApiAdminProductController;
use App\Http\Controllers\Api\Admin\UserController as ApiAdminUserController;
use App\Http\Controllers\CheckoutController;
use App\Http\Controllers\HomeController;
use App\Http\Controllers\OrderController;
use App\Http\Controllers\ProductController;
use App\Http\Controllers\ProfileController;
use Illuminate\Support\Facades\Route;

Route::get('/', [HomeController::class, 'index'])->name('home');

Route::get('/products', [ProductController::class, 'index'])->name('products.index');
Route::get('/products/{product:slug}', [ProductController::class, 'show'])->name('products.show');

Route::get('/dashboard', function () {
    return view('dashboard');
})->middleware(['auth', 'verified'])->name('dashboard');

// ===== API publik (tanpa login) =====
Route::prefix('api')->name('api.')->group(function () {
    Route::get('/products', [ApiProductController::class, 'index'])->name('products.index');
    Route::get('/products/{product:slug}', [ApiProductController::class, 'show'])->name('products.show');
    Route::get('/categories', [ApiCategoryController::class, 'index'])->name('categories.index');
});

Route::middleware('auth')->group(function () {
    Route::get('/checkout/{product:slug}', [CheckoutController::class, 'create'])->name('checkout');
    Route::post('/checkout/{product:slug}', [CheckoutController::class, 'store'])->name('checkout.store');

    Route::get('/orders', [OrderController::class, 'index'])->name('orders.index');
    Route::get('/orders/{order}', [OrderController::class, 'show'])->name('orders.show');

    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');

    // ===== Endpoint API untuk klien Next.js =====
    Route::get('/api/auth/me', [ApiAuthController::class, 'me'])->name('api.auth.me');
    Route::get('/api/user', [ApiAuthController::class, 'user'])->name('api.user');
    Route::post('/api/logout', [ApiAuthController::class, 'logout'])->name('api.logout');

    Route::get('/api/profile', [ApiAuthController::class, 'profile'])->name('api.profile');
    Route::put('/api/profile', [ApiAuthController::class, 'updateProfile'])->name('api.profile.update');

    // ===== API user (login) =====
    Route::middleware('auth')->prefix('api')->name('api.')->group(function () {
        Route::get('/orders', [ApiOrderController::class, 'index'])->name('orders.index');
        Route::post('/orders', [ApiOrderController::class, 'store'])->name('orders.store');
        Route::get('/orders/{order}', [ApiOrderController::class, 'show'])->name('orders.show');
    });

    // ===== Chunked upload API (session auth + CSRF) =====
    // Endpoint ini dipakai oleh klien chunked upload (admin Blade / Next.js).
    Route::prefix('api/uploads')->name('api.uploads.')->group(function () {
        // Config harus didaftarkan SEBELUM route {upload} agar tidak bentrok binding.
        Route::get('/config', [UploadController::class, 'config'])->name('config');
        Route::post('/', [UploadController::class, 'store'])->name('store');
        Route::get('/{upload}', [UploadController::class, 'show'])->name('show');
        Route::post('/{upload}/chunks', [UploadController::class, 'chunks'])->name('chunks');
        Route::post('/{upload}/complete', [UploadController::class, 'complete'])->name('complete');
        Route::delete('/{upload}', [UploadController::class, 'destroy'])->name('destroy');
        Route::get('/{upload}/download', [UploadController::class, 'download'])->name('download');
    });
});

Route::middleware(['auth', 'admin'])->prefix('admin')->name('admin.')->group(function () {
    Route::get('/', [DashboardController::class, 'index'])->name('dashboard');
    Route::resource('categories', CategoryController::class)->except('show');
    Route::resource('products', AdminProductController::class);
    Route::resource('users', UserController::class)->only(['index', 'show', 'edit', 'update', 'destroy']);
    Route::resource('orders', AdminOrderController::class)->only(['index', 'show']);
    Route::patch('orders/{order}/status', [AdminOrderController::class, 'updateStatus'])->name('orders.status');

    Route::get('uploads', [AdminUploadController::class, 'index'])->name('uploads.index');
    Route::get('uploads/{upload}', [AdminUploadController::class, 'show'])->name('uploads.show');
    Route::delete('uploads/{upload}', [AdminUploadController::class, 'destroy'])->name('uploads.destroy');
    Route::get('uploads/{upload}/download', [AdminUploadController::class, 'download'])->name('uploads.download');
});

// ===== API admin untuk Next.js (JSON) =====
Route::middleware(['auth', 'admin'])->prefix('api/admin')->name('api.admin.')->group(function () {
    Route::get('dashboard', [ApiAdminDashboardController::class, 'index'])->name('dashboard');

    Route::get('products', [ApiAdminProductController::class, 'index'])->name('products.index');
    Route::post('products', [ApiAdminProductController::class, 'store'])->name('products.store');
    Route::get('products/{product}', [ApiAdminProductController::class, 'show'])->name('products.show');
    Route::put('products/{product}', [ApiAdminProductController::class, 'update'])->name('products.update');
    Route::delete('products/{product}', [ApiAdminProductController::class, 'destroy'])->name('products.destroy');

    Route::get('categories', [ApiAdminCategoryController::class, 'index'])->name('categories.index');
    Route::post('categories', [ApiAdminCategoryController::class, 'store'])->name('categories.store');
    Route::put('categories/{category}', [ApiAdminCategoryController::class, 'update'])->name('categories.update');
    Route::delete('categories/{category}', [ApiAdminCategoryController::class, 'destroy'])->name('categories.destroy');

    Route::get('users', [ApiAdminUserController::class, 'index'])->name('users.index');
    Route::get('users/{user}', [ApiAdminUserController::class, 'show'])->name('users.show');
    Route::put('users/{user}', [ApiAdminUserController::class, 'update'])->name('users.update');
    Route::delete('users/{user}', [ApiAdminUserController::class, 'destroy'])->name('users.destroy');

    Route::get('orders', [ApiAdminOrderController::class, 'index'])->name('orders.index');
    Route::get('orders/{order}', [ApiAdminOrderController::class, 'show'])->name('orders.show');
    Route::patch('orders/{order}/status', [ApiAdminOrderController::class, 'updateStatus'])->name('orders.status');

    Route::get('uploads', [ApiAdminUploadController::class, 'index'])->name('uploads.index');
});

// ===== API auth publik (guest) =====
Route::prefix('api')->middleware('guest')->name('api.')->group(function () {
    Route::post('/login', [ApiAuthController::class, 'login'])->name('login');
    Route::post('/register', [ApiAuthController::class, 'register'])->name('register');
});

require __DIR__.'/auth.php';
