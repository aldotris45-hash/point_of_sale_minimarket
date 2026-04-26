<?php

namespace App\Services\IncomingGood;

use App\Models\IncomingGood;
use App\Models\Product;
use App\Models\ProductPrice;
use App\Models\ProductPriceHistory;
use Illuminate\Support\Facades\DB;

class IncomingGoodService implements IncomingGoodServiceInterface
{
    /**
     * Catat barang masuk dengan logik multi-satuan.
     *
     * ────────────────────────────────────────────────────────────
     *  TIPE A — Count-based (Coca Cola, Indomie, dll):
     *   1. Terima incoming_qty krat + conversion_factor (N pcs/krat)
     *   2. converted_qty = incoming_qty × conversion_factor
     *   3. stock_added   = converted_qty − spoilage_qty
     *
     *  TIPE B — Weight-based (sayur, buah, dll):
     *   1. Terima incoming_qty krat + gross_weight_kg
     *   2. krat_weight_used_kg = krat_weight_kg × incoming_qty (dari master produk)
     *   3. net_weight_kg = gross_weight_kg − krat_weight_used_kg
     *   4. stock_added   = net_weight_kg − spoilage_qty
     * ────────────────────────────────────────────────────────────
     */
    public function create(array $data): IncomingGood
    {
        return DB::transaction(function () use ($data) {
            $product = Product::findOrFail($data['product_id']);

            // ── Ambil nilai dasar ─────────────────────────────────
            $incomingQty       = (float) ($data['incoming_qty'] ?? $data['quantity'] ?? 0);
            $conversionFactor  = (float) ($data['conversion_factor'] ?? $product->bulk_conversion ?? 1);
            $incomingUnit      = $data['incoming_unit'] ?? $product->bulk_unit ?? $product->unit;
            $spoilageQty       = (float) ($data['spoilage_qty'] ?? 0);
            $purchasePricePerBulk = (float) ($data['purchase_price_per_bulk'] ?? $data['purchase_price'] ?? 0);
            $sellingPriceBulk   = isset($data['selling_price_bulk']) && $data['selling_price_bulk'] !== null
                ? (float) $data['selling_price_bulk'] : null;
            $sellingPriceRetail = isset($data['selling_price_retail']) && $data['selling_price_retail'] !== null
                ? (float) $data['selling_price_retail'] : null;

            // ── Hitung stok yang ditambahkan ──────────────────────
            if ($product->isWeightBased()) {
                // Tipe B: Weight-based
                // gross_weight_kg = berat 1 krat + isi (dari timbangan)
                // krat_weight     = berat krat kosong
                // Rumus: bersih = jumlah_krat × (kotor_per_krat - krat_kosong) - busuk
                $grossPerKrat       = (float) ($data['gross_weight_kg'] ?? 0);
                $kratWeightPerUnit  = (float) ($data['krat_weight_kg'] ?? $product->krat_weight_kg ?? 0);
                $netPerKrat         = max(0, $grossPerKrat - $kratWeightPerUnit);
                $grossWeightKg      = $grossPerKrat * $incomingQty;  // total berat kotor
                $kratWeightUsedKg   = $kratWeightPerUnit * $incomingQty;
                $netWeightKg        = $netPerKrat * $incomingQty;    // total berat bersih (sebelum busuk)
                $convertedQty       = $netWeightKg;
                $stockAdded         = max(0, $netWeightKg - $spoilageQty);
            } else {
                // Tipe A: Count-based
                $grossWeightKg    = null;
                $kratWeightUsedKg = null;
                $netWeightKg      = null;
                $convertedQty     = $incomingQty * $conversionFactor;
                $stockAdded       = max(0, $convertedQty - $spoilageQty);
            }

            // ── Total harga beli ──────────────────────────────────
            $total = $incomingQty * $purchasePricePerBulk;

            // ── Simpan record ─────────────────────────────────────
            $incomingGood = IncomingGood::create([
                'date'                    => $data['date'],
                'supplier_id'             => $data['supplier_id'] ?: null,
                'product_id'              => $product->id,

                // Satuan baru
                'incoming_unit'           => $incomingUnit,
                'incoming_qty'            => $incomingQty,
                'conversion_factor'       => $conversionFactor,
                'gross_weight_kg'         => $grossWeightKg,
                'krat_weight_used_kg'     => $kratWeightUsedKg ?? null,
                'net_weight_kg'           => $netWeightKg,
                'converted_qty'           => $convertedQty,
                'spoilage_qty'            => $spoilageQty,
                'spoilage_notes'          => $data['spoilage_notes'] ?? null,
                'stock_added'             => $stockAdded,
                'purchase_price_per_bulk' => $purchasePricePerBulk,

                // Legacy fields (agar backward-compatible)
                'purchase_price'          => $purchasePricePerBulk,
                'quantity'                => (int) round($incomingQty),
                'total'                   => $total,

                'user_id' => $data['user_id'],
                'notes'   => $data['notes'] ?? null,
            ]);

            // ── Tambah stok produk ────────────────────────────────
            $product->increment('stock', $stockAdded);

            // ── Update harga jual jika diisi (atau dihapus) ──────────────────────
            if (array_key_exists('selling_price_bulk', $data) || array_key_exists('selling_price_retail', $data)) {
                $this->updateSellingPrices($product, $data, $purchasePricePerBulk, $sellingPriceBulk, $sellingPriceRetail);
            } elseif ($purchasePricePerBulk > 0) {
                $this->recordCostPrice($product->id, $data['date'], $purchasePricePerBulk);
            }

            return $incomingGood;
        });
    }

    /**
     * Update tanggal barang masuk + cascade ke data terkait.
     */
    public function updateDate(IncomingGood $incomingGood, string $newDate): IncomingGood
    {
        return DB::transaction(function () use ($incomingGood, $newDate) {
            $oldDate = $incomingGood->date->toDateString();

            ProductPrice::where('product_id', $incomingGood->product_id)
                ->where('price_date', $oldDate)
                ->where('cost_price', $incomingGood->purchase_price)
                ->update(['price_date' => $newDate]);

            ProductPriceHistory::where('product_id', $incomingGood->product_id)
                ->where('effective_date', $oldDate)
                ->where('notes', 'LIKE', 'Via Barang Masuk%')
                ->update(['effective_date' => $newDate]);

            $incomingGood->update(['date' => $newDate]);

            return $incomingGood->fresh();
        });
    }

    /**
     * Hapus barang masuk dan kurangi stok kembali.
     * Menggunakan stock_added (bukan quantity lama) agar presisi.
     */
    public function delete(IncomingGood $incomingGood): void
    {
        DB::transaction(function () use ($incomingGood) {
            // Gunakan stock_added jika tersedia, fallback ke quantity lama
            $stockToRemove = (float) ($incomingGood->stock_added > 0
                ? $incomingGood->stock_added
                : $incomingGood->quantity);

            $product = $incomingGood->product;
            if ($product) {
                $newStock = max(0, (float) $product->stock - $stockToRemove);
                $product->update(['stock' => $newStock]);
            }

            $incomingGood->delete();
        });
    }

    // ─────────────────────────────────────────────────────────────
    //  Private helpers
    // ─────────────────────────────────────────────────────────────

    private function updateSellingPrices(Product $product, array $data, float $costPrice, ?float $sellingPriceBulk, ?float $sellingPriceRetail): void
    {
        // Tentukan status retail
        $hasRetail = $sellingPriceRetail !== null && $sellingPriceRetail > 0;

        // Update product table
        $updates = [
            'has_retail'     => $hasRetail,
            'price_per_bulk' => $sellingPriceBulk > 0 ? $sellingPriceBulk : null,
        ];

        if ($hasRetail) {
            $updates['price_per_unit'] = $sellingPriceRetail;
            $updates['price']          = $sellingPriceRetail;
        } else {
            $updates['price']          = $sellingPriceBulk > 0 ? $sellingPriceBulk : 0;
            // Jika kita biarkan product_type, unit, dll itu tidak masalah karena hanya update harga.
            // Bisa juga update product_type menjadi count otomatis jika tidak retail, tapi form produk sudah menangani ini.
        }

        $product->update($updates);

        // Rekam riwayat harga (gunakan harga utama sebagai acuan ProductPrice lama, atau catat keduanya di history)
        $mainPrice = $hasRetail ? $sellingPriceRetail : $sellingPriceBulk;

        if ($mainPrice !== null && $mainPrice > 0) {
            ProductPrice::updateOrCreate(
                [
                    'product_id' => $product->id,
                    'price_date' => date('Y-m-d', strtotime($data['date'])),
                ],
                [
                    'cost_price'    => $costPrice,
                    'selling_price' => $mainPrice,
                    'notes'         => $data['notes'] ?? null,
                ]
            );

            ProductPriceHistory::create([
                'product_id'     => $product->id,
                'selling_price'  => $mainPrice,
                'effective_date' => $data['date'],
                'changed_at'     => now(),
                'notes'          => 'Via Barang Masuk' . (!empty($data['notes']) ? ': ' . $data['notes'] : ''),
            ]);
        }
    }

    private function recordCostPrice(int $productId, string $date, float $costPrice): void
    {
        ProductPrice::updateOrCreate(
            [
                'product_id' => $productId,
                'price_date' => date('Y-m-d', strtotime($date)),
            ],
            ['cost_price' => $costPrice]
        );
    }
}
