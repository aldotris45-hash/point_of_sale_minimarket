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
        'price',
        'promo_price',
        'promo_label',
        'stock',
        'min_stock',
        'expiry_date',
    ];

    protected $casts = [
        'price'       => 'decimal:2',
        'promo_price' => 'decimal:2',
        'expiry_date' => 'date',
    ];

    /** Apakah produk sedang promo */
    public function isOnPromo(): bool
    {
        return $this->promo_price !== null && $this->promo_price > 0;
    }

    /** Harga efektif (promo jika ada, fallback ke price) */
    public function effectivePrice(): float
    {
        return $this->isOnPromo() ? (float) $this->promo_price : (float) $this->price;
    }

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
}
