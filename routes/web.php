<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\CategoryController;
use App\Http\Controllers\ProductController;
use App\Http\Controllers\UserController;
use App\Http\Controllers\SettingsController;
use App\Http\Controllers\CashierController;
use App\Http\Controllers\PaymentController;
use App\Http\Controllers\MidtransController;
use App\Http\Controllers\TransactionController;
use Illuminate\Foundation\Http\Middleware\VerifyCsrfToken;

Route::middleware('auth')->group(function () {
    Route::view('/', 'home')->name('dashboard');

    // Kasir
    Route::get('/kasir', [CashierController::class, 'index'])->name('kasir');
    Route::get('/kasir/products', [CashierController::class, 'products'])->name('kasir.products');
    Route::post('/kasir/checkout', [CashierController::class, 'checkout'])->name('kasir.checkout');

    // Pembayaran
    Route::get('/pembayaran/{transaction}', [PaymentController::class, 'show'])->name('pembayaran.show');
    Route::get('/pembayaran/{transaction}/status', [PaymentController::class, 'status'])->name('pembayaran.status');
    Route::get('/pembayaran/{transaction}/complete', [PaymentController::class, 'complete'])->name('pembayaran.complete');

    // Struk/Receipt
    Route::get('/transaksi/{transaction}/struk', [TransactionController::class, 'receipt'])->name('transaksi.struk');

    Route::get('/transaksi', fn() => view('pages.placeholder', ['title' => 'Transaksi']))->name('transaksi');
    Route::get('/pembayaran', fn() => view('pages.placeholder', ['title' => 'Pembayaran']))->name('pembayaran');

    // Kategori
    Route::resource('kategori', CategoryController::class)
        ->parameters(['kategori' => 'category'])
        ->names('kategori')
        ->except(['show']);
    Route::get('/kategori-data', [CategoryController::class, 'data'])->name('kategori.data');

    // Produk
    Route::resource('produk', ProductController::class)
        ->parameters(['produk' => 'product'])
        ->names('produk')
        ->except(['show']);
    Route::get('/produk-data', [ProductController::class, 'data'])->name('produk.data');

    // Pengguna
    Route::resource('pengguna', UserController::class)
        ->parameters(['pengguna' => 'user'])
        ->names('pengguna')
        ->except(['show']);
    Route::get('/pengguna-data', [UserController::class, 'data'])->name('pengguna.data');

    // Pengaturan
    Route::get('/pengaturan', [SettingsController::class, 'index'])->name('pengaturan.index');
    Route::put('/pengaturan', [SettingsController::class, 'update'])->name('pengaturan.update');
    Route::get('/pengaturan/preview-receipt', [SettingsController::class, 'previewReceipt'])->name('pengaturan.preview');

    Route::get('/log-aktivitas', fn() => view('pages.placeholder', ['title' => 'Log Aktivitas']))->name('log-aktivitas');
    Route::get('/bantuan', fn() => view('pages.placeholder', ['title' => 'Panduan']))->name('bantuan');
});

// Midtrans webhook
Route::post('/midtrans/notification', [MidtransController::class, 'notification'])
    ->name('midtrans.notification')
    ->withoutMiddleware([VerifyCsrfToken::class]);

Route::middleware('guest')->group(function () {
    Route::get('/login', [AuthController::class, 'showLogin'])->name('login');
    Route::post('/login', [AuthController::class, 'login'])->middleware('throttle:6,1');
});

Route::post('/logout', [AuthController::class, 'logout'])
    ->middleware('auth')
    ->name('logout');
