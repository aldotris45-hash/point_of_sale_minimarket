<?php

namespace App\Services\IncomingGood;

use App\Models\IncomingGood;
use App\Models\Product;
use App\Models\ProductPrice;
use App\Models\ProductPriceHistory;
use Illuminate\Support\Facades\DB;

class IncomingGoodService implements IncomingGoodServiceInterface
{
    public function create(array $data): IncomingGood
    {
        return DB::transaction(function () use ($data) {
            $purchasePrice = (float) ($data['purchase_price'] ?? 0);
            $sellingPrice = isset($data['selling_price']) && $data['selling_price'] !== null
                ? (float) $data['selling_price']
                : null;
            $quantity = (int) ($data['quantity'] ?? 0);

            $incomingGood = IncomingGood::create([
                'date' => $data['date'],
                'supplier_id' => $data['supplier_id'] ?: null,
                'product_id' => $data['product_id'],
                'purchase_price' => $purchasePrice,
                'quantity' => $quantity,
                'total' => $purchasePrice * $quantity,
                'user_id' => $data['user_id'],
                'notes' => $data['notes'] ?? null,
            ]);

            // Otomatis tambah stok produk
            Product::where('id', $data['product_id'])->increment('stock', $quantity);

            // Jika selling_price diisi, update harga jual & catat di product_prices
            if ($sellingPrice !== null && $sellingPrice > 0) {
                $product = Product::find($data['product_id']);

                // Buat/update record product_prices untuk tanggal ini
                ProductPrice::updateOrCreate(
                    [
                        'product_id' => $data['product_id'],
                        'price_date' => $data['date'],
                    ],
                    [
                        'cost_price' => $purchasePrice,
                        'selling_price' => $sellingPrice,
                        'notes' => $data['notes'] ?? null,
                    ]
                );

                // Catat riwayat perubahan harga
                ProductPriceHistory::create([
                    'product_id' => $data['product_id'],
                    'selling_price' => $sellingPrice,
                    'effective_date' => $data['date'],
                    'changed_at' => now(),
                    'notes' => 'Via Barang Masuk' . (!empty($data['notes']) ? ': ' . $data['notes'] : ''),
                ]);

                // Update harga jual di tabel produk utama
                $product->update(['price' => $sellingPrice]);
            } elseif ($purchasePrice > 0) {
                // Meskipun selling_price tidak diisi, tetap catat cost_price
                ProductPrice::updateOrCreate(
                    [
                        'product_id' => $data['product_id'],
                        'price_date' => $data['date'],
                    ],
                    [
                        'cost_price' => $purchasePrice,
                    ]
                );
            }

            return $incomingGood;
        });
    }

    /**
     * Update tanggal barang masuk + cascade ke data terkait.
     *
     * Yang di-update:
     * - incoming_goods.date
     * - product_prices.price_date (jika ada record terkait)
     * - product_price_histories.effective_date (jika ada record terkait)
     */
    public function updateDate(IncomingGood $incomingGood, string $newDate): IncomingGood
    {
        return DB::transaction(function () use ($incomingGood, $newDate) {
            $oldDate = $incomingGood->date->toDateString();

            // 1. Update product_prices yang dibuat via barang masuk ini
            ProductPrice::where('product_id', $incomingGood->product_id)
                ->where('price_date', $oldDate)
                ->where('cost_price', $incomingGood->purchase_price)
                ->update(['price_date' => $newDate]);

            // 2. Update product_price_histories yang dibuat via barang masuk ini
            ProductPriceHistory::where('product_id', $incomingGood->product_id)
                ->where('effective_date', $oldDate)
                ->where('notes', 'LIKE', 'Via Barang Masuk%')
                ->update(['effective_date' => $newDate]);

            // 3. Update tanggal di incoming_goods sendiri
            $incomingGood->update(['date' => $newDate]);

            return $incomingGood->fresh();
        });
    }

    public function delete(IncomingGood $incomingGood): void
    {
        DB::transaction(function () use ($incomingGood) {
            // Kurangi stok produk yang sudah ditambahkan
            Product::where('id', $incomingGood->product_id)
                ->decrement('stock', $incomingGood->quantity);

            $incomingGood->delete();
        });
    }
}
