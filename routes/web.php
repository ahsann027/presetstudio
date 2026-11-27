<?php

use App\Http\Controllers\Admin\AdminAuthController;
use App\Http\Controllers\Admin\AdminOrderController;
use App\Http\Controllers\Admin\AdminProductController;
use App\Http\Controllers\CatalogController;
use App\Http\Controllers\CheckoutController;
use App\Http\Controllers\DownloadController;
use App\Http\Controllers\PaymentNotificationController;
use App\Http\Middleware\EnsureUserIsAdmin;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| RUTE PUBLIK (pembeli, tanpa login)
|--------------------------------------------------------------------------
*/

// Katalog
Route::get('/', [CatalogController::class, 'index'])->name('catalog.index');
Route::get('/preset/{id}', [CatalogController::class, 'show'])->whereNumber('id')->name('catalog.show');
Route::get('/cara-beli', [CatalogController::class, 'info'])->name('catalog.info');

// Checkout & pembayaran
Route::get('/beli/{id}', [CheckoutController::class, 'create'])->whereNumber('id')->name('checkout.create');
Route::post('/beli/{id}', [CheckoutController::class, 'store'])->whereNumber('id')->middleware('throttle:10,1')->name('checkout.store');
Route::get('/pembayaran/{token}', [CheckoutController::class, 'pay'])->name('checkout.pay');
Route::post('/pembayaran/{token}/simulasi', [CheckoutController::class, 'simulate'])->name('checkout.simulate');
Route::get('/pesanan/{token}', [CheckoutController::class, 'success'])->name('checkout.success');

// Unduhan: URL bertanda tangan (signed) + dibatasi laju
Route::get('/unduh/{token}', DownloadController::class)
    ->where('token', '[A-Za-z0-9]{40}')
    ->middleware(['signed', 'throttle:20,1'])
    ->name('download');

// Webhook Midtrans (kecualikan dari CSRF di bootstrap/app.php - lihat README)
Route::post('/midtrans/notification', PaymentNotificationController::class)->name('midtrans.notification');

/*
|--------------------------------------------------------------------------
| RUTE ADMIN
|--------------------------------------------------------------------------
*/

// Login admin. Nama rute "login" dipakai middleware auth untuk mengarahkan tamu ke sini.
Route::get('/admin/login', [AdminAuthController::class, 'showLogin'])->middleware('guest')->name('login');
Route::post('/admin/login', [AdminAuthController::class, 'login'])->middleware(['guest', 'throttle:5,1'])->name('admin.login.store');
Route::post('/admin/logout', [AdminAuthController::class, 'logout'])->middleware('auth')->name('admin.logout');

// Area admin: wajib login DAN is_admin = true
Route::prefix('admin')
    ->name('admin.')
    ->middleware(['auth', EnsureUserIsAdmin::class])
    ->group(function () {
        Route::redirect('/', '/admin/produk');

        Route::resource('produk', AdminProductController::class)
            ->parameters(['produk' => 'product'])
            ->except('show')
            ->names('products');

        Route::get('pesanan', [AdminOrderController::class, 'index'])->name('orders.index');
    });
