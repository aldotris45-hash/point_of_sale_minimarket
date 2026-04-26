<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class IncomingGood extends Model
{
    use HasFactory;

    protected $fillable = [
        'date',
        'supplier_id',
        'product_id',

        // Satuan masuk (baru)
        'incoming_unit',           // 'krat', 'karton', 'sak', dll
        'incoming_qty',            // jumlah bulk yang datang

        // Weight-based (produk per kg)
        'gross_weight_kg',         // berat kotor total (krat + isi)
        'krat_weight_used_kg',     // berat semua krat kosong
        'net_weight_kg',           // berat bersih = gross - krat

        // Count-based (produk per pcs)
        'conversion_factor',       // 1 krat = N pcs (diambil dari master produk)
        'converted_qty',           // hasil konversi sebelum susut

        // Susut / busuk saat datang
        'spoilage_qty',            // qty yang busuk/rusak (kg atau pcs)
        'spoilage_notes',          // keterangan busuk

        // Stock yang benar-benar ditambahkan
        'stock_added',             // = converted_qty - spoilage_qty

        // Harga beli (per batch, bisa beda tiap kedatangan)
        'purchase_price_per_bulk', // harga per krat/karton saat ini
        'purchase_price',          // legacy: harga per unit (opsional)
        'total',                   // total bayar = incoming_qty × purchase_price_per_bulk

        'user_id',
        'notes',
        // 'quantity' lama dibiarkan agar tidak break
    ];

    protected $casts = [
        'date'                    => 'date',
        'incoming_qty'            => 'decimal:2',
        'gross_weight_kg'         => 'decimal:3',
        'krat_weight_used_kg'     => 'decimal:3',
        'net_weight_kg'           => 'decimal:3',
        'conversion_factor'       => 'decimal:4',
        'converted_qty'           => 'decimal:4',
        'spoilage_qty'            => 'decimal:4',
        'stock_added'             => 'decimal:4',
        'purchase_price_per_bulk' => 'decimal:2',
        'purchase_price'          => 'decimal:2',
        'total'                   => 'decimal:2',
    ];

    // ─────────────────────────────────────────────
    //  Business Logic Helpers
    // ─────────────────────────────────────────────

    /**
     * Hitung net weight untuk produk weight-based.
     * gross_weight - krat_weight = net_weight
     */
    public function calculateNetWeight(): float
    {
        return max(0, (float) $this->gross_weight_kg - (float) $this->krat_weight_used_kg);
    }

    /**
     * Hitung qty yang masuk ke stok (setelah dikurangi busuk).
     *
     * Weight: net_weight_kg - spoilage_qty
     * Count:  (incoming_qty × conversion_factor) - spoilage_qty
     */
    public function calculateStockAdded(): float
    {
        $baseQty = $this->product?->isWeightBased()
            ? $this->calculateNetWeight()
            : ((float) $this->incoming_qty * (float) $this->conversion_factor);

        return max(0, $baseQty - (float) $this->spoilage_qty);
    }

    /**
     * Hitung total harga beli.
     */
    public function calculateTotal(): float
    {
        return (float) $this->incoming_qty * (float) $this->purchase_price_per_bulk;
    }

    // ─────────────────────────────────────────────
    //  Relasi
    // ─────────────────────────────────────────────

    public function supplier(): BelongsTo
    {
        return $this->belongsTo(Supplier::class);
    }

    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class);
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function spoilages(): HasMany
    {
        return $this->hasMany(ProductSpoilage::class);
    }
}
