<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\Category;
use App\Models\Product;
use App\Models\Customer;
use App\Models\Supplier;
use App\Models\Transaction;
use App\Models\TransactionDetail;
use App\Models\IncomingGood;
use App\Models\Payment;
use App\Models\CashTransaction;
use App\Enums\CashTransactionCategory;
use App\Services\IncomingGood\IncomingGoodService;
use Illuminate\Support\Str;

class ImportManualData extends Command
{
    protected $signature = 'import:manual';
    protected $description = 'Import 3 manual transactions requested via Godmode';

    public function handle(IncomingGoodService $incomingGoodService)
    {
        $this->info("Membersihkan test run sebelumnya...");
        
        $trxIds = Transaction::withTrashed()->where('note', 'Transaksi Manual Godmode')->pluck('id');
        Payment::whereIn('transaction_id', $trxIds)->delete();
        TransactionDetail::whereIn('transaction_id', $trxIds)->delete();
        Transaction::withTrashed()->whereIn('id', $trxIds)->forceDelete();

        IncomingGood::where('notes', 'Import Manual Godmode')->delete();
        CashTransaction::where('description', 'LIKE', 'Modal Kulakan Manual%')->orWhere('description', 'LIKE', 'Pendapatan Transaksi INV-MANUAL%')->delete();

        $this->info("Menyisipkan data invoice manual...");

        $invoices = [
            [
                'date' => '2026-03-08',
                'customer' => 'SPPG Balung Kidul',
                'items' => [
                    ['name' => 'Sawi', 'qty' => 100, 'costPrice' => 5000, 'unitPrice' => 7750]
                ]
            ],
            [
                'date' => '2026-03-09',
                'customer' => 'SPPG Patemon Tanggul',
                'items' => [
                    ['name' => 'Klengkeng Biru', 'qty' => 18, 'costPrice' => 420000, 'unitPrice' => 500000]
                ]
            ],
            [
                'date' => '2026-03-10',
                'customer' => 'SPPG Mayang',
                'items' => [
                    ['name' => 'Ubi Cilembu Lokal', 'qty' => 300, 'costPrice' => 7500, 'unitPrice' => 11000]
                ]
            ]
        ];

        $defaultCategory = Category::firstOrCreate(['name' => 'Sayur']);
        $defaultSupplier = Supplier::firstOrCreate(
            ['name' => 'Supplier Legacy'],
            ['contact_name' => 'Sistem Import', 'phone' => '-', 'address' => '-']
        );

        foreach ($invoices as $index => $invoice) {
            $dateObj = \Carbon\Carbon::parse($invoice['date']);
            $dateStr = $dateObj->toDateString(); // Y-m-d

            // 1. CARI/BUAT CUSTOMER
            $customer = Customer::firstOrCreate(
                ['name' => $invoice['customer']],
                ['phone' => '-', 'address' => '-']
            );

            // 2. JAM PAGI: BARANG MASUK 
            $totalModal = 0;
            foreach ($invoice['items'] as $item) {
                // Gunakan nama produk yang ada, atau buat baru jika tidak ditemukan
                $product = Product::firstOrCreate(
                    ['name' => $item['name']],
                    [
                        'category_id' => $defaultCategory->id,
                        'sku' => strtoupper(Str::slug($item['name'])) . '-' . rand(1000, 9999),
                        'price' => $item['unitPrice'],
                        'stock' => 0,
                    ]
                );

                $incomingTime = $dateStr . ' 08:00:00';
                $incomingGoodService->create([
                    'date' => $incomingTime,
                    'supplier_id' => $defaultSupplier->id,
                    'product_id' => $product->id,
                    'purchase_price' => $item['costPrice'],
                    'selling_price' => $item['unitPrice'],
                    'quantity' => $item['qty'],
                    'user_id' => 1,
                    'notes' => 'Import Manual Godmode',
                ]);
                
                $totalModal += ($item['costPrice'] * $item['qty']);
            }

            // 3. JAM SIANG: TRANSAKSI JUAL
            $subtotal = 0;
            foreach ($invoice['items'] as $item) {
                $subtotal += ($item['unitPrice'] * $item['qty']);
            }
            
            // Generate Invoice
            $transactionTime = $dateStr . ' 12:00:00';
            $baseInvoiceNo = 'INV-MANUAL-' . $dateObj->format('dmY') . '-' . ($index+1);
            
            // Tangani duplikat jika pernah dieksekusi sebelumnya
            $finalInvoiceNo = $baseInvoiceNo;
            $counter = 1;
            while (Transaction::withTrashed()->where('invoice_number', $finalInvoiceNo)->exists()) {
                $finalInvoiceNo = $baseInvoiceNo . '-' . $counter;
                $counter++;
            }

            $transaction = Transaction::create([
                'user_id' => 1,
                'customer_id' => $customer->id,
                'invoice_number' => $finalInvoiceNo,
                'subtotal' => $subtotal,
                'discount' => 0,
                'tax' => 0,
                'total' => $subtotal,
                'amount_paid' => $subtotal,
                'change' => 0,
                'payment_method' => \App\Enums\PaymentMethod::CASH,
                'status' => \App\Enums\TransactionStatus::PAID,
                'note' => 'Transaksi Manual Godmode',
                'created_at' => $transactionTime,
                'updated_at' => $transactionTime,
            ]);

            foreach ($invoice['items'] as $item) {
                $product = Product::where('name', $item['name'])->first();
                TransactionDetail::create([
                    'transaction_id' => $transaction->id,
                    'product_id' => $product->id,
                    'quantity' => $item['qty'],
                    'unit_price' => $item['unitPrice'],
                    'subtotal' => $item['qty'] * $item['unitPrice'],
                    'created_at' => $transactionTime,
                    'updated_at' => $transactionTime,
                ]);

                // Kurangi stok karena sudah dibeli
                Product::whereKey($product->id)->decrement('stock', $item['qty']);
            }

            Payment::create([
                'transaction_id' => $transaction->id,
                'payment_method' => \App\Enums\PaymentMethod::CASH,
                'amount' => $subtotal,
                'change' => 0,
                'created_at' => $transactionTime,
                'updated_at' => $transactionTime,
            ]);

            CashTransaction::create([
                'user_id'     => 1,
                'type'        => 'in',
                'category'    => CashTransactionCategory::PENJUALAN->value,
                'date'        => $dateStr,
                'amount'      => $subtotal,
                'description' => 'Pendapatan Transaksi ' . $finalInvoiceNo . ' (' . $invoice['customer'] . ')',
            ]);

            $this->line("- Berhasil insert transaksi: " . $invoice['customer']);
        }

        $this->info("Semua transaksi manual selesai dimasukkan! Silakan cek web.");
    }
}
