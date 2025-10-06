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
    Route::get('/', [\App\Http\Controllers\DashboardController::class, 'index'])->name('dashboard');

    // Kasir
    Route::middleware('role:admin,cashier')->group(function () {
        Route::get('/kasir', [CashierController::class, 'index'])->name('kasir');
        Route::get('/kasir/products', [CashierController::class, 'products'])->name('kasir.products');
        Route::post('/kasir/checkout', [CashierController::class, 'checkout'])->name('kasir.checkout');
    });

    // Pembayaran
    Route::get('/pembayaran/{transaction}', [PaymentController::class, 'show'])->name('pembayaran.show');
    Route::get('/pembayaran/{transaction}/status', [PaymentController::class, 'status'])->name('pembayaran.status');
    Route::get('/pembayaran/{transaction}/complete', [PaymentController::class, 'complete'])->name('pembayaran.complete');

    // Transaksi
    Route::get('/transaksi/{transaction}/struk', [TransactionController::class, 'receipt'])->name('transaksi.struk');
    Route::get('/transaksi', [TransactionController::class, 'index'])->name('transaksi');
    Route::get('/transaksi-data', [TransactionController::class, 'data'])->name('transaksi.data');
    Route::get('/transaksi/{transaction}', [TransactionController::class, 'show'])->name('transaksi.show');


    // Kategori
    Route::middleware('role:admin')->group(function () {
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

        // Pembayaran
        Route::get('/pembayaran', [PaymentController::class, 'index'])->name('pembayaran');
        Route::get('/pembayaran-data', [PaymentController::class, 'data'])->name('pembayaran.data');
    });

    // Laporan
    Route::get('/laporan', [\App\Http\Controllers\ReportController::class, 'index'])->name('laporan');
    Route::get('/laporan-data', [\App\Http\Controllers\ReportController::class, 'data'])->name('laporan.data');
    Route::get('/laporan/unduh', [\App\Http\Controllers\ReportController::class, 'download'])->name('laporan.unduh');

    // Log Aktivitas
    Route::get('/log-aktivitas', [\App\Http\Controllers\ActivityLogController::class, 'index'])->name('log-aktivitas');
    Route::get('/log-aktivitas-data', [\App\Http\Controllers\ActivityLogController::class, 'data'])->name('log-aktivitas.data');
});

// Midtrans webhook
Route::post('/midtrans/notification', [MidtransController::class, 'notification'])
    ->middleware(['throttle:30,1'])
    ->withoutMiddleware([VerifyCsrfToken::class]);

Route::middleware('guest')->group(function () {
    Route::get('/login', [AuthController::class, 'showLogin'])->name('login');
    Route::post('/login', [AuthController::class, 'login'])->middleware('throttle:6,1');
});

Route::post('/logout', [AuthController::class, 'logout'])
    ->middleware('auth')
    ->name('logout');
