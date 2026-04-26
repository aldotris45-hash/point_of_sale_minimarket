<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Product extends Model
{
    use HasFactory;

    protected $fillable = [
        'category_id',
        'name',
        'sku',
        'has_retail',           // apakah bisa dijual eceran?
        // Sistem satuan
        'product_type',         // 'weight' | 'count'
        'unit',                 // satuan eceran: 'kg', 'botol', 'bungkus', 'pcs'
        'bulk_unit',            // satuan grosir: 'krat', 'karton', 'kardus', 'sak'
        'bulk_conversion',      // 1 bulk_unit = N unit eceran
        'krat_weight_kg',       // berat krat/wadah kosong (untuk weight-based)
        // Harga
        'price',                // alias/legacy → sama dengan price_per_unit
        'price_per_unit',       // harga jual eceran per unit/kg
        'price_per_bulk',       // harga jual grosir per krat/karton (nullable)
        'purchase_price_per_bulk', // HPP referensi per bulk
        // Promo
        'promo_price',
        'promo_label',
        // Stok
        'stock',
        'min_stock',
        'expiry_date',
    ];

    protected $casts = [
        'has_retail'              => 'boolean',
        'price'                   => 'decimal:2',
        'price_per_unit'          => 'decimal:2',
        'price_per_bulk'          => 'decimal:2',
        'purchase_price_per_bulk' => 'decimal:2',
        'promo_price'             => 'decimal:2',
        'bulk_conversion'         => 'decimal:4',
        'krat_weight_kg'          => 'decimal:3',
        'stock'                   => 'decimal:4',
        'expiry_date'             => 'date',
    ];

    // ─────────────────────────────────────────────
    //  Cek tipe produk
    // ─────────────────────────────────────────────

    /** Apakah produk dijual per berat (kg/gram)? */
    public function isWeightBased(): bool
    {
        return $this->product_type === 'weight';
    }

    /** Apakah produk bisa dijual eceran? */
    public function hasRetail(): bool
    {
        return $this->has_retail;
    }

    /** Apakah produk dijual per satuan (pcs/botol/bungkus)? */
    public function isCountBased(): bool
    {
        return $this->product_type === 'count';
    }

    /** Apakah produk punya satuan grosir (bisa dijual per krat/karton)? */
    public function hasBulkUnit(): bool
    {
        return ! empty($this->bulk_unit) && $this->bulk_conversion > 0;
    }

    // ─────────────────────────────────────────────
    //  Konversi satuan
    // ─────────────────────────────────────────────

    /**
     * Konversi qty dari satuan tertentu ke base unit (untuk deduct/tambah stok).
     *
     * Contoh:
     *   toBaseUnit(2, 'krat')   → 2 × 24 = 48 botol
     *   toBaseUnit(2.5, 'kg')   → 2.5 kg (sudah base)
     *   toBaseUnit(3, 'botol')  → 3 botol (sudah base)
     */
    public function toBaseUnit(float $qty, string $unit): float
    {
        if ($unit === $this->unit) {
            return $qty;
        }

        if ($this->hasBulkUnit() && $unit === $this->bulk_unit) {
            return $qty * (float) $this->bulk_conversion;
        }

        throw new \InvalidArgumentException(
            "Satuan '{$unit}' tidak dikenali untuk produk '{$this->name}'. " .
            "Satuan valid: {$this->unit}" . ($this->hasBulkUnit() ? ", {$this->bulk_unit}" : '')
        );
    }

    /**
     * Konversi qty dari base unit ke bulk unit.
     * Contoh: 48 botol → 2 krat (sisa 0)
     */
    public function toBulkUnit(float $baseQty): array
    {
        if (! $this->hasBulkUnit()) {
            return ['bulk' => 0, 'remainder' => $baseQty];
        }

        $bulk      = (int) floor($baseQty / (float) $this->bulk_conversion);
        $remainder = fmod($baseQty, (float) $this->bulk_conversion);

        return ['bulk' => $bulk, 'remainder' => $remainder];
    }

    // ─────────────────────────────────────────────
    //  Stok helpers
    // ─────────────────────────────────────────────

    /**
     * Tampilkan stok dalam format human-readable.
     *
     * Contoh output:
     *   Count-based: "5 krat 3 botol"
     *   Weight-based: "47.5 kg"
     */
    public function stockDisplay(): string
    {
        $stock = (float) $this->stock;

        if ($this->isWeightBased()) {
            return number_format($stock, 2) . ' ' . $this->unit;
        }

        if ($this->hasBulkUnit()) {
            ['bulk' => $bulk, 'remainder' => $rem] = $this->toBulkUnit($stock);
            $parts = [];
            if ($bulk > 0)   $parts[] = "{$bulk} {$this->bulk_unit}";
            if ($rem > 0)    $parts[] = number_format($rem, 0) . ' ' . $this->unit;
            return implode(' ', $parts) ?: "0 {$this->unit}";
        }

        return number_format($stock, 0) . ' ' . $this->unit;
    }

    /** Apakah stok di bawah minimum? */
    public function isLowStock(): bool
    {
        return $this->stock <= $this->min_stock;
    }

    // ─────────────────────────────────────────────
    //  Harga helpers
    // ─────────────────────────────────────────────

    /** Apakah sedang promo? */
    public function isOnPromo(): bool
    {
        return $this->promo_price !== null && (float) $this->promo_price > 0;
    }

    /** Harga jual eceran efektif (promo jika ada) */
    public function effectiveUnitPrice(): float
    {
        return $this->isOnPromo()
            ? (float) $this->promo_price
            : (float) ($this->price_per_unit ?: $this->price);
    }

    /** Harga jual grosir per bulk unit */
    public function effectiveBulkPrice(): float
    {
        return (float) ($this->price_per_bulk ?? (
            $this->effectiveUnitPrice() * (float) $this->bulk_conversion
        ));
    }

    /**
     * Hitung harga berdasarkan satuan jual.
     *
     * @param string $unit  Satuan jual ('kg','botol' atau 'krat','karton')
     * @param bool   $promo Gunakan harga promo jika ada?
     */
    public function priceFor(string $unit, bool $promo = true): float
    {
        if ($this->hasBulkUnit() && $unit === $this->bulk_unit) {
            return $this->effectiveBulkPrice();
        }

        return $promo ? $this->effectiveUnitPrice() : (float) ($this->price_per_unit ?: $this->price);
    }

    // ─────────────────────────────────────────────
    //  Relasi
    // ─────────────────────────────────────────────

    public function category(): BelongsTo
    {
        return $this->belongsTo(Category::class);
    }

    public function prices(): HasMany
    {
        return $this->hasMany(ProductPrice::class);
    }

    public function priceHistory(): HasMany
    {
        return $this->hasMany(ProductPriceHistory::class);
    }

    public function incomingGoods(): HasMany
    {
        return $this->hasMany(IncomingGood::class);
    }

    public function spoilages(): HasMany
    {
        return $this->hasMany(ProductSpoilage::class);
    }
}
