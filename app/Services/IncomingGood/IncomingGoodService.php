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
                    'notes' => 'Via Barang Masuk' . ($data['notes'] ? ': ' . $data['notes'] : ''),
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
