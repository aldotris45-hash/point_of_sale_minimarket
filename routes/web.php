<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\CategoryController;
use App\Http\Controllers\ProductController;
use App\Http\Controllers\UserController;

Route::middleware('auth')->group(function () {
    Route::view('/', 'home')->name('dashboard');

    Route::get('/kasir', fn() => view('pages.placeholder', ['title' => 'Kasir']))->name('kasir');
    Route::get('/transaksi', fn() => view('pages.placeholder', ['title' => 'Transaksi']))->name('transaksi');
    Route::get('/pembayaran', fn() => view('pages.placeholder', ['title' => 'Pembayaran']))->name('pembayaran');
    Route::get('/laporan', fn() => view('pages.placeholder', ['title' => 'Laporan']))->name('laporan');

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

    Route::get('/pengaturan', fn() => view('pages.placeholder', ['title' => 'Pengaturan']))->name('pengaturan');
    Route::get('/log-aktivitas', fn() => view('pages.placeholder', ['title' => 'Log Aktivitas']))->name('log-aktivitas');
    Route::get('/bantuan', fn() => view('pages.placeholder', ['title' => 'Panduan']))->name('bantuan');
});

Route::middleware('guest')->group(function () {
    Route::get('/login', [AuthController::class, 'showLogin'])->name('login');
    Route::post('/login', [AuthController::class, 'login'])->middleware('throttle:6,1');
});

Route::post('/logout', [AuthController::class, 'logout'])
    ->middleware('auth')
    ->name('logout');
