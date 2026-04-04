<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\Customer;
use App\Models\Product;
use App\Models\Category;
use App\Models\Transaction;
use App\Models\TransactionDetail;
use App\Models\IncomingGood;
use App\Models\Payment;
use App\Models\CashTransaction;
use App\Models\Supplier;
use Illuminate\Support\Str;
use App\Enums\PaymentStatus;
use App\Enums\PaymentMethod;
use App\Enums\TransactionStatus;
use App\Enums\CashTransactionCategory;
use App\Services\IncomingGood\IncomingGoodServiceInterface;

class ImportLegacyExcel extends Command
{
    protected $signature = 'import:legacy';
    protected $description = 'Import legacy transaction history from parsed Excel JSON (Barang Masuk -> Transaksi -> Balance)';

    public function handle()
    {
        $this->info("Membersihkan data import sebelumnya (biar tidak dobel)...");
        
        // Hapus transaksi lama
        $trxIds = Transaction::withTrashed()->where('note', 'LIKE', '%Import%Excel%')->pluck('id');
        Payment::whereIn('transaction_id', $trxIds)->delete();
        TransactionDetail::whereIn('transaction_id', $trxIds)->delete();
        Transaction::withTrashed()->whereIn('id', $trxIds)->forceDelete();

        // Hapus Barang Masuk lama & riwayat harganya
        IncomingGood::where('notes', 'Import Modal Excel Legacy')->delete();
        \App\Models\ProductPriceHistory::where('notes', 'LIKE', 'Via Barang Masuk: Import Modal%')->delete();

        // Hapus Buku Kas lama
        CashTransaction::where('description', 'LIKE', '%(Import)%')->delete();

        // Reset Stok Produk Import menjadi 0 (jika ada sisa dari crash sebelumnya)
        $defaultCategory = Category::where('name', 'Sayur')->first();
        if ($defaultCategory) {
            Product::where('category_id', $defaultCategory->id)->update(['stock' => 0]);
        }

        $path = storage_path('app/legacy_import.json');
        if (!file_exists($path)) {
            $this->error("File $path tidak ditemukan! Jalankan script node parse_excel.mjs terlebih dahulu.");
            return Command::FAILURE;
        }

        $this->info("Membaca file export Excel...");
        $data = json_decode(file_get_contents($path), true);

        if (!$data || !is_array($data)) {
            $this->error("Format JSON tidak valid.");
            return Command::FAILURE;
        }

        $incomingGoodService = app(IncomingGoodServiceInterface::class);

        // Buat kategori default
        $defaultCategory = Category::firstOrCreate(
            ['name' => 'Sayur'],
            ['description' => 'Kategori produk sayuran']
        );

        // Buat supplier default
        $defaultSupplier = Supplier::firstOrCreate(
            ['name' => 'Supplier Excel Legacy'],
            ['phone' => '-', 'address' => 'Otomatis diimport dari history Excel']
        );

        $totalInvoices = 0;
        $totalItems = 0;

        $ignoredKeywords = ['gaji', 'karyawan', 'note', 'fee', 'bonus', 'operasional', 'ongkos', 'bensin', 'pengeluaran', 'tutup warung'];

        foreach ($data as $invoice) {
            $customerName = trim($invoice['customer'] ?? 'Pelanggan Umum');

            $customer = Customer::firstOrCreate(
                ['name' => $customerName],
                ['phone' => '-', 'address' => 'Diimport otomatis dari history excel']
            );

            // Filter out item operasional/gaji
            $validItems = [];
            foreach ($invoice['items'] as $itemLine) {
                $nameLower = strtolower(trim($itemLine['name']));
                $skip = false;
                foreach ($ignoredKeywords as $ignored) {
                    if (str_contains($nameLower, $ignored)) {
                        $skip = true;
                        break;
                    }
                }
                
                if (!$skip) {
                    $validItems[] = $itemLine;
                }
            }

            if (empty($validItems)) {
                $this->warn("Skipped invoice (empty valid items) -> Date: {$invoice['date']}, Cust: {$customerName}");
                continue;
            }

            $totalSales = array_sum(array_column($validItems, 'subtotal'));
            $invoiceDate = $invoice['date'];
            
            // FASE 1: BARANG MASUK (Pagi jam 08:00)
            $incomingTime = $invoiceDate . ' 08:00:00';
            
            foreach ($validItems as $idx => $itemLine) {
                $productName = trim($itemLine['name']);

                // Find or Create Product
                $product = Product::firstOrCreate(
                    ['name' => $productName],
                    [
                        'category_id' => $defaultCategory->id,
                        'sku'         => strtoupper(Str::slug($productName)) . '-' . rand(100, 999),
                        'price'       => $itemLine['sell_price'],
                        'stock'       => 0,
                        'min_stock'   => 5,
                    ]
                );
                
                // Tambahkan referensi object product untuk FASE 2
                $validItems[$idx]['_product'] = $product;

                // Rekam Incoming Good (Supplier -> Restock -> Harga Modal -> Update Stok)
                $incomingGoodService->create([
                    'date'           => $incomingTime,
                    'supplier_id'    => $defaultSupplier->id,
                    'product_id'     => $product->id,
                    'purchase_price' => $itemLine['cost_price'],
                    'selling_price'  => $itemLine['sell_price'],
                    'quantity'       => $itemLine['qty'],
                    'user_id'        => 1, // Assume user_id 1 is the importer
                    'notes'          => 'Import Modal Excel Legacy',
                ]);
            }

            // FASE 2: TRANSAKSI PENJUALAN (Siang jam 12:00)
            $salesTime = $invoiceDate . ' 12:00:00';

            $baseInvoiceNo = $invoice['invoiceNo'] ?: 'INV-LEGACY-' . uniqid();
            $finalInvoiceNo = $baseInvoiceNo;
            $counter = 1;
            while (Transaction::withTrashed()->where('invoice_number', $finalInvoiceNo)->exists()) {
                $finalInvoiceNo = $baseInvoiceNo . '-' . $counter;
                $counter++;
            }

            // Create Transaction
            $transaction = Transaction::create([
                'user_id'        => 1,
                'customer_id'    => $customer->id,
                'invoice_number' => $finalInvoiceNo,
                'subtotal'       => $totalSales,
                'discount'       => 0,
                'tax'            => 0,
                'total'          => $totalSales,
                'amount_paid'    => $totalSales,
                'change'         => 0,
                'payment_method' => PaymentMethod::CASH->value,
                'status'         => TransactionStatus::PAID->value,
                'note'           => 'Transaksi Import dari Excel',
                'created_at'     => $salesTime,
                'updated_at'     => $salesTime,
            ]);

            // Create Payment Log
            Payment::create([
                'transaction_id' => $transaction->id,
                'amount'         => $totalSales,
                'method'         => PaymentMethod::CASH->value,
                'status'         => PaymentStatus::SETTLEMENT->value,
                'paid_at'        => $salesTime,
                'created_at'     => $salesTime,
            ]);

            // Create CashTransaction for Penjualan (Pemasukan)
            CashTransaction::create([
                'user_id'     => 1,
                'type'        => 'in',
                'category'    => CashTransactionCategory::PENJUALAN->value,
                'date'        => $invoiceDate,
                'amount'      => $totalSales,
                'description' => 'Penjualan #' . $transaction->invoice_number . ' (Import)',
            ]);

            // Catat detail item transaksi (mengurangi stok menjadi 0 balance)
            foreach ($validItems as $itemLine) {
                $product = $itemLine['_product'];
                
                TransactionDetail::create([
                    'transaction_id' => $transaction->id,
                    'product_id'     => $product->id,
                    'quantity'       => $itemLine['qty'],
                    'price'          => $itemLine['sell_price'],
                    'total'          => $itemLine['subtotal'],
                    'created_at'     => $salesTime,
                ]);

                // Kurangi stok kembali sebesar yang terjual
                Product::whereKey($product->id)->decrement('stock', $itemLine['qty']);
                
                $totalItems++;
            }

            $totalInvoices++;
            $this->info("✓ Invoice {$transaction->invoice_number} ({$customerName}) - Rp " . number_format($totalSales, 0, ',', '.'));
        }

        $this->newLine();
        $this->info("═══════════════════════════════════════════");
        $this->info("SELESAI! $totalInvoices Invoice dan $totalItems Item berhasil di-import!");
        $this->info("Barang Masuk (Modal & Stok) -> Penjualan -> Balance ter-update secara otomatis.");
        $this->info("═══════════════════════════════════════════");

        return Command::SUCCESS;
    }
}
