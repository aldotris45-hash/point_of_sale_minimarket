<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\Customer;
use App\Models\Product;
use App\Models\Category;
use App\Models\Transaction;
use App\Models\TransactionDetail;
use App\Models\Payment;
use Illuminate\Support\Str;
use App\Enums\PaymentStatus;
use App\Enums\PaymentMethod;
use App\Enums\TransactionStatus;

class ImportLegacyExcel extends Command
{
    protected $signature = 'import:legacy';
    protected $description = 'Import legacy transaction history from parsed Excel JSON';

    public function handle()
    {
        $path = storage_path('app/legacy_import.json');
        if (!file_exists($path)) {
            $this->error("File $path tidak ditemukan!");
            return Command::FAILURE;
        }

        $this->info("Membaca file export Excel...");
        $data = json_decode(file_get_contents($path), true);

        if (!$data || !is_array($data)) {
            $this->error("Format JSON tidak valid.");
            return Command::FAILURE;
        }

        // Buat kategori default untuk produk import
        $defaultCategory = Category::firstOrCreate(
            ['name' => 'Barang (Import Excel)'],
            ['description' => 'Kategori otomatis untuk barang import dari Excel lama']
        );

        $totalInvoices = 0;
        $totalItems = 0;

        foreach ($data as $invoice) {
            $customerName = trim($invoice['customer'] ?? 'Pelanggan Umum');

            // Cari atau buat pelanggan
            $customer = Customer::firstOrCreate(
                ['name' => $customerName],
                [
                    'phone' => '-',
                    'address' => 'Diimport otomatis dari history excel',
                ]
            );

            $invoiceDate = $invoice['date'] . ' 12:00:00';

            $baseInvoiceNo = $invoice['invoiceNo'] ?: 'INV-LEGACY-' . uniqid();
            $finalInvoiceNo = $baseInvoiceNo;
            $counter = 1;
            while (Transaction::where('invoice_number', $finalInvoiceNo)->exists()) {
                $finalInvoiceNo = $baseInvoiceNo . '-' . $counter;
                $counter++;
            }

            // Buat transaksi
            $transaction = Transaction::create([
                'user_id'        => 1,
                'customer_id'    => $customer->id,
                'invoice_number' => $finalInvoiceNo,
                'subtotal'       => $invoice['totalSales'],
                'discount'       => 0,
                'tax'            => 0,
                'total'          => $invoice['totalSales'],
                'amount_paid'    => $invoice['totalSales'],
                'change'         => 0,
                'payment_method' => PaymentMethod::CASH->value,
                'status'         => TransactionStatus::PAID->value,
                'note'           => 'Diimport dari Excel (Tanggal Asli: ' . $invoice['date'] . ')',
                'created_at'     => $invoiceDate,
                'updated_at'     => $invoiceDate,
            ]);

            // Catat Payment
            Payment::create([
                'transaction_id' => $transaction->id,
                'amount'         => $invoice['totalSales'],
                'method'         => PaymentMethod::CASH->value,
                'status'         => PaymentStatus::SETTLEMENT->value,
                'paid_at'        => $invoiceDate,
                'created_at'     => $invoiceDate,
            ]);

            // Catat detail item transaksi
            foreach ($invoice['items'] as $itemLine) {
                $productName = trim($itemLine['name']);

                // Cari atau buat produk
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

                TransactionDetail::create([
                    'transaction_id' => $transaction->id,
                    'product_id'     => $product->id,
                    'quantity'       => $itemLine['qty'],
                    'price'          => $itemLine['sell_price'],
                    'total'          => $itemLine['subtotal'],
                    'created_at'     => $invoiceDate,
                ]);

                $totalItems++;
            }

            $totalInvoices++;
            $this->info("✓ Invoice {$transaction->invoice_number} ({$customerName}) - Rp " . number_format($invoice['totalSales'], 0, ',', '.'));
        }

        $this->newLine();
        $this->info("═══════════════════════════════════════════");
        $this->info("SELESAI! $totalInvoices Invoice dan $totalItems Item berhasil di-import!");
        $this->info("═══════════════════════════════════════════");
        return Command::SUCCESS;
    }
}
