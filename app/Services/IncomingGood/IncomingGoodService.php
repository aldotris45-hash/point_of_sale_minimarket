<?php

namespace App\Services\IncomingGood;

use App\Models\IncomingGood;
use App\Models\Product;
use Illuminate\Support\Facades\DB;

class IncomingGoodService implements IncomingGoodServiceInterface
{
    public function create(array $data): IncomingGood
    {
        return DB::transaction(function () use ($data) {
            $purchasePrice = (float) ($data['purchase_price'] ?? 0);
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
