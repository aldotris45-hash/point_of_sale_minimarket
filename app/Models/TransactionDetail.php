<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class TransactionDetail extends Model
{
    use HasFactory;

    protected $fillable = [
        'transaction_id',
        'product_id',
        'quantity',         // qty dalam satuan jual (bisa decimal untuk kg)
        'price',            // harga per satuan jual
        'item_discount',    // diskon per unit (Rp) — nego harga di kasir
        'is_promo',
        'original_price',
        'total',
        // Multi-unit fields
        'sale_unit',        // satuan jual: 'kg','botol' atau 'krat','karton'
        'sale_qty',         // sama dengan quantity, untuk kejelasan
        'qty_in_base_unit', // qty dalam base unit → yang dikurangi dari stok
        'is_bulk_sale',     // true = penjualan grosir
        'promo_bulk_label', // label promo grosir, misal "Promo Partai 5 Krat"
    ];

    protected $casts = [
        'price'            => 'decimal:2',
        'item_discount'    => 'decimal:2',
        'original_price'   => 'decimal:2',
        'total'            => 'decimal:2',
        'quantity'         => 'decimal:4',
        'sale_qty'         => 'decimal:4',
        'qty_in_base_unit' => 'decimal:4',
        'is_promo'         => 'boolean',
        'is_bulk_sale'     => 'boolean',
    ];

    /**
     * Format tampilan untuk struk nota.
     *
     * Eceran : "2.5 kg @ Rp 8.000/kg"
     * Grosir : "2 krat @ Rp 95.000/krat"
     */
    public function saleDisplay(): string
    {
        $unit = $this->sale_unit ?? ($this->product?->unit ?? 'pcs');
        $qty  = (float) ($this->sale_qty ?? $this->quantity);

        // Format qty: desimal untuk kg, bulat untuk pcs/botol
        $qtyFormatted = in_array($unit, ['kg', 'gram', 'liter'])
            ? number_format($qty, 2)
            : number_format($qty, 0);

        $priceFormatted = 'Rp ' . number_format((float) $this->price, 0, ',', '.');

        return "{$qtyFormatted} {$unit} @ {$priceFormatted}/{$unit}";
    }

    /**
     * Label untuk struk: tampilkan promo grosir jika ada.
     */
    public function promoLabel(): ?string
    {
        if ($this->promo_bulk_label) {
            return $this->promo_bulk_label;
        }
        if ($this->is_promo && $this->is_bulk_sale) {
            return 'Promo Partai';
        }
        if ($this->is_promo) {
            return 'Harga Promo';
        }
        return null;
    }

    public function transaction(): BelongsTo
    {
        return $this->belongsTo(Transaction::class);
    }

    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class);
    }
}
