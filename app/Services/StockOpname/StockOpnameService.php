<?php

namespace App\Services\StockOpname;

use App\Models\Product;
use App\Models\StockOpname;
use Illuminate\Support\Facades\DB;

class StockOpnameService implements StockOpnameServiceInterface
{
    public function create(array $data): StockOpname
    {
        return DB::transaction(function () use ($data) {
            $product = Product::findOrFail($data['product_id']);
            $systemStock = (int) $product->stock;
            $physicalStock = (int) ($data['physical_stock'] ?? 0);
            $difference = $physicalStock - $systemStock;

            $stockOpname = StockOpname::create([
                'date' => $data['date'],
                'product_id' => $data['product_id'],
                'system_stock' => $systemStock,
                'physical_stock' => $physicalStock,
                'difference' => $difference,
                'notes' => $data['notes'] ?? null,
                'user_id' => $data['user_id'],
            ]);

            // Otomatis sesuaikan stok produk ke stok fisik
            $product->update(['stock' => $physicalStock]);

            return $stockOpname;
        });
    }

    public function delete(StockOpname $stockOpname): void
    {
        $stockOpname->delete();
    }
}
