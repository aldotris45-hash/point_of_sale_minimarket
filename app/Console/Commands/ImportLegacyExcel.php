<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\Customer;
use App\Models\Product;
use App\Models\Category;
use App\Models\Transaction;
use App\Models\TransactionItem;
use App\Models\Payment;
use App\Models\CashFlow;
use Illuminate\Support\Str;
use App\Enums\PaymentStatus;
use App\Enums\PaymentMethod;

class ImportLegacyExcel extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'import:legacy';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Import legacy transaction history from parsed Excel JSON';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $path = storage_path('app/legacy_import.json');
        if (!file_exists($path)) {
            $this->error("File $path tidak ditemukan!");
            return Command::FAILURE;
        }

        $this->info("Membaca file export Excel dari $path...");
        $data = json_decode(file_get_contents($path), true);

        if (!$data || !is_array($data)) {
            $this->error("Format JSON tidak valid.");
            return Command::FAILURE;
        }

        $defaultCategory = Category::firstOrCreate(
            ['slug' => 'pindah-import'],
            ['name' => 'Barang (Import Excel)', 'description' => 'Kategori otomatis untuk barang import dari Excel lama']
        );

        $totalInvoices = 0;
        $totalItems = 0;

        foreach ($data as $invoice) {
            $customerName = \trim($invoice['customer'] ?? 'Pelanggan Umum');
            
            // Cari atau buat pelanggan
            $customer = Customer::firstOrCreate(
                ['name' => $customerName],
                [
                    'phone' => '-',
                    'address' => 'Diimport otomatis dari history excel'
                ]
            );

            // Buat transaksi
            $transaction = Transaction::create([
                'user_id' => 1, // Assume Admin
                'customer_id' => $customer->id,
                'invoice_number' => $invoice['invoiceNo'] ?: 'INV-LEGACY-' . uniqid(),
                'total_amount' => $invoice['totalSales'],
                'amount_paid' => $invoice['totalSales'],
                'return_amount' => 0,
                'payment_method' => PaymentMethod::CASH->value,
                'status' => PaymentStatus::PAID->value,
                'notes' => 'Diimport dari Excel (Tanggal Asli: ' . $invoice['date'] . ')',
                'created_at' => $invoice['date'] . ' 12:00:00',
                'updated_at' => $invoice['date'] . ' 12:00:00'
            ]);

            // Catat Payment
            Payment::create([
                'transaction_id' => $transaction->id,
                'amount'         => $invoice['totalSales'],
                'method'         => PaymentMethod::CASH->value,
                'status'         => PaymentStatus::PAID->value,
                'paid_at'        => $invoice['date'] . ' 12:00:00',
                'created_at'     => $invoice['date'] . ' 12:00:00',
            ]);

            // Catat Arus Kas Masuk (Income) & Modal (Expense if needed, but normally sales is income. We'll record the net or just full sales as income depending on CashFlow structure)
            // Typically in retail, we just record "Penjualan" as whole cash flow.
            CashFlow::create([
                'user_id' => 1,
                'type' => 'income',
                'amount' => $invoice['totalSales'],
                'description' => 'Penjualan LUNAS (History Invoice ' . $transaction->invoice_number . ')',
                'reference_type' => Transaction::class,
                'reference_id' => $transaction->id,
                'date' => $invoice['date'] . ' 12:00:00',
                'created_at' => $invoice['date'] . ' 12:00:00',
            ]);

            foreach ($invoice['items'] as $itemLine) {
                // Cari atau buat produk
                $productName = \trim($itemLine['name']);
                $product = Product::firstOrCreate(
                    ['name' => $productName],
                    [
                        'category_id' => $defaultCategory->id,
                        'sku' => strtoupper(Str::slug($productName) . '-' . rand(100, 999)),
                        'unit' => $itemLine['unit'] ?: 'Pcs',
                        'price' => $itemLine['sell_price'],
                        'cost_price' => $itemLine['cost_price'],
                        'stock' => 0, 
                        'stock_minimum' => 5
                    ]
                );

                // Tambah Item Transaksi
                TransactionItem::create([
                    'transaction_id' => $transaction->id,
                    'product_id' => $product->id,
                    'quantity' => $itemLine['qty'],
                    'price' => $itemLine['sell_price'],
                    'capital_price' => $itemLine['cost_price'],
                    'subtotal' => $itemLine['subtotal'],
                    'created_at' => $invoice['date'] . ' 12:00:00',
                ]);

                $totalItems++;
            }

            $totalInvoices++;
            $this->info("Berhasil menyimpan invoice {$transaction->invoice_number} ({$customerName})");
        }

        $this->info("SELESAI! $totalInvoices Invoice dan $totalItems Item Transaksi berhasil di-import!");
        return Command::SUCCESS;
    }
}
