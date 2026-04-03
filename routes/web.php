<?php

use App\Http\Controllers\ActivityLogController;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\CategoryController;
use App\Http\Controllers\ProductController;
use App\Http\Controllers\ProductPriceController;
use App\Http\Controllers\CashTransactionController;
use App\Http\Controllers\UserController;
use App\Http\Controllers\SettingsController;
use App\Http\Controllers\CashierController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\PaymentController;
use App\Http\Controllers\NotificationController;
use App\Http\Controllers\ReportController;
use App\Http\Controllers\TransactionController;
use App\Http\Controllers\SupplierController;
use App\Http\Controllers\CustomerController;
use App\Http\Controllers\IncomingGoodController;
use App\Http\Controllers\StockOpnameController;
use App\Http\Controllers\CashFlowController;
use App\Http\Controllers\TaxReportController;
use Illuminate\Foundation\Http\Middleware\VerifyCsrfToken;

// Login
Route::middleware('guest')->group(function () {
    Route::get('/login', [AuthController::class, 'showLogin'])->name('login');
    Route::post('/login', [AuthController::class, 'login'])->middleware('throttle:6,1');
});

// Logout
Route::post('/logout', [AuthController::class, 'logout'])
    ->middleware('auth')
    ->name('logout');


Route::middleware('auth')->group(function () {
    // Notifications
    Route::put('/notifications/{id}/read', [NotificationController::class, 'read'])
        ->name('notifications.read');
    Route::put('/notifications/read-all', [NotificationController::class, 'readAll'])
        ->name('notifications.read_all');

    // Dashboard
    Route::get('/', [DashboardController::class, 'index'])->name('dashboard');

    Route::middleware('role:admin,cashier')->group(function () {
        // Kasir
        Route::get('/kasir', [CashierController::class, 'index'])->name('kasir');
        Route::get('/kasir/products', [CashierController::class, 'products'])->name('kasir.products');
        Route::post('/kasir/checkout', [CashierController::class, 'checkout'])->name('kasir.checkout');
        Route::post('/kasir/hold', [CashierController::class, 'hold'])->name('kasir.hold');
        Route::get('/kasir/holds', [CashierController::class, 'holds'])->name('kasir.holds');
        Route::post('/kasir/holds/{transaction}/resume', [CashierController::class, 'resume'])->name('kasir.holds.resume');
        Route::delete('/kasir/holds/{transaction}', [CashierController::class, 'destroyHold'])->name('kasir.holds.destroy');

        // Transaksi
        Route::get('/transaksi/{transaction}/struk', [TransactionController::class, 'receipt'])->name('transaksi.struk');
        Route::get('/transaksi/{transaction}/invoice', [TransactionController::class, 'printInvoice'])->name('transaksi.invoice');
        Route::get('/transaksi/{transaction}/faktur', [TransactionController::class, 'printFaktur'])->name('transaksi.faktur');
        Route::get('/transaksi/{transaction}/struk/pdf', [TransactionController::class, 'receiptPdf'])->name('transaksi.struk.pdf');
        Route::get('/transaksi/{transaction}/invoice/pdf', [TransactionController::class, 'invoicePdf'])->name('transaksi.invoice.pdf');
        Route::get('/transaksi/{transaction}/faktur/pdf', [TransactionController::class, 'fakturPdf'])->name('transaksi.faktur.pdf');
        Route::get('/transaksi', [TransactionController::class, 'index'])->name('transaksi');
        Route::get('/transaksi-data', [TransactionController::class, 'data'])->name('transaksi.data');
        Route::get('/transaksi/{transaction}', [TransactionController::class, 'show'])->name('transaksi.show');
        // POST route for marking cash-tampo transactions as paid
        Route::post('/transaksi/{transaction}/lunas', [TransactionController::class, 'markAsPaid'])->name('transaksi.lunas');

        // Delete transaction (admin only, guarded in controller)
        Route::delete('/transaksi/{transaction}', [TransactionController::class, 'destroy'])->name('transaksi.destroy');
        Route::patch('/transaksi/{transaction}/update-date', [TransactionController::class, 'updateDate'])->name('transaksi.update-date');

    });

    Route::middleware('role:admin')->group(function () {
        // Pembayaran
        Route::get('/pembayaran', [PaymentController::class, 'index'])->name('pembayaran');
        Route::get('/pembayaran-data', [PaymentController::class, 'data'])->name('pembayaran.data');
        Route::delete('/pembayaran/{payment}', [PaymentController::class, 'destroy'])->name('pembayaran.destroy');

        // Laporan
        Route::get('/laporan', [ReportController::class, 'index'])->name('laporan');
        Route::get('/laporan-data', [ReportController::class, 'data'])->name('laporan.data');
        Route::get('/laporan/unduh', [ReportController::class, 'download'])->name('laporan.unduh');
        Route::get('/laporan/cetak-transaksi', [ReportController::class, 'printTransactions'])->name('laporan.cetak-transaksi');

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

        // Harga Produk (Fluktuatif)
        Route::get('/harga-produk', [ProductPriceController::class, 'index'])->name('harga-produk.index');
        Route::get('/harga-produk-data', [ProductPriceController::class, 'data'])->name('harga-produk.data');
        Route::get('/harga-produk/create', [ProductPriceController::class, 'create'])->name('harga-produk.create');
        Route::post('/harga-produk', [ProductPriceController::class, 'store'])->name('harga-produk.store');
        Route::get('/harga-produk/{productPrice}/edit', [ProductPriceController::class, 'edit'])->name('harga-produk.edit');
        Route::put('/harga-produk/{productPrice}', [ProductPriceController::class, 'update'])->name('harga-produk.update');
        Route::delete('/harga-produk/{productPrice}', [ProductPriceController::class, 'destroy'])->name('harga-produk.destroy');
        Route::get('/harga-produk/riwayat/{product}', [ProductPriceController::class, 'history'])->name('harga-produk.history');
        Route::get('/harga-produk/riwayat-data/{product}', [ProductPriceController::class, 'historyData'])->name('harga-produk.history-data');

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

        // Log Aktivitas
        Route::get('/log-aktivitas', [ActivityLogController::class, 'index'])->name('log-aktivitas');
        Route::get('/log-aktivitas-data', [ActivityLogController::class, 'data'])->name('log-aktivitas.data');

        // Buku Kas
        Route::get('/buku-kas', [CashTransactionController::class, 'index'])->name('buku-kas.index');
        Route::get('/buku-kas-data', [CashTransactionController::class, 'data'])->name('buku-kas.data');
        Route::get('/buku-kas/create', [CashTransactionController::class, 'create'])->name('buku-kas.create');
        Route::post('/buku-kas', [CashTransactionController::class, 'store'])->name('buku-kas.store');
        Route::delete('/buku-kas/{cashTransaction}', [CashTransactionController::class, 'destroy'])->name('buku-kas.destroy');

        // Arus Kas
        Route::get('/arus-kas', [CashFlowController::class, 'index'])->name('arus-kas');
        Route::get('/arus-kas/export-pdf', [CashFlowController::class, 'exportPdf'])->name('arus-kas.export-pdf');

        // Pajak
        Route::get('/pajak', [TaxReportController::class, 'index'])->name('pajak');
        Route::get('/pajak/export-pdf', [TaxReportController::class, 'exportPdf'])->name('pajak.export-pdf');
        Route::get('/pajak/export-csv', [TaxReportController::class, 'exportCsv'])->name('pajak.export-csv');

        // Supplier
        Route::resource('supplier', SupplierController::class)
            ->parameters(['supplier' => 'supplier'])
            ->names('supplier')
            ->except(['show']);
        Route::get('/supplier-data', [SupplierController::class, 'data'])->name('supplier.data');

        // Pelanggan
        Route::resource('pelanggan', CustomerController::class)
            ->parameters(['pelanggan' => 'customer'])
            ->names('pelanggan')
            ->except(['show']);
        Route::get('/pelanggan-data', [CustomerController::class, 'data'])->name('pelanggan.data');

        // Barang Masuk
        Route::get('/barang-masuk', [IncomingGoodController::class, 'index'])->name('barang-masuk.index');
        Route::get('/barang-masuk-data', [IncomingGoodController::class, 'data'])->name('barang-masuk.data');
        Route::get('/barang-masuk/create', [IncomingGoodController::class, 'create'])->name('barang-masuk.create');
        Route::post('/barang-masuk', [IncomingGoodController::class, 'store'])->name('barang-masuk.store');
        Route::delete('/barang-masuk/{incomingGood}', [IncomingGoodController::class, 'destroy'])->name('barang-masuk.destroy');
        Route::patch('/barang-masuk/{incomingGood}/update-date', [IncomingGoodController::class, 'updateDate'])->name('barang-masuk.update-date');

        // Stok Opname
        Route::get('/stok-opname', [StockOpnameController::class, 'index'])->name('stok-opname.index');
        Route::get('/stok-opname-data', [StockOpnameController::class, 'data'])->name('stok-opname.data');
        Route::get('/stok-opname/create', [StockOpnameController::class, 'create'])->name('stok-opname.create');
        Route::post('/stok-opname', [StockOpnameController::class, 'store'])->name('stok-opname.store');
        Route::delete('/stok-opname/{stockOpname}', [StockOpnameController::class, 'destroy'])->name('stok-opname.destroy');
        Route::get('/stok-opname/product-stock/{product}', [StockOpnameController::class, 'getProductStock'])->name('stok-opname.product-stock');
    });
});
