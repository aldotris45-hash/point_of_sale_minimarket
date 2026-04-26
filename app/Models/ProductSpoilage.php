<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ProductSpoilage extends Model
{
    use HasFactory;

    protected $fillable = [
        'date',
        'product_id',
        'incoming_good_id', // optional: link ke batch kedatangan mana
        'spoilage_qty',     // jumlah yang busuk/susut
        'unit',             // 'kg' atau 'botol'/'pcs' sesuai product.unit
        'reason',           // 'busuk','rusak','kadaluarsa','penyusutan','lainnya'
        'notes',
        'recorded_by',
    ];

    protected $casts = [
        'date'         => 'date',
        'spoilage_qty' => 'decimal:4',
    ];

    /**
     * Label yang mudah dibaca untuk alasan busuk.
     */
    public function reasonLabel(): string
    {
        return match ($this->reason) {
            'busuk'       => 'Busuk / Membusuk',
            'rusak'       => 'Rusak / Pecah',
            'kadaluarsa'  => 'Kadaluarsa',
            'penyusutan'  => 'Penyusutan Alami',
            'lainnya'     => 'Lainnya',
            default       => ucfirst($this->reason),
        };
    }

    // ─────────────────────────────────────────────
    //  Relasi
    // ─────────────────────────────────────────────

    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class);
    }

    public function incomingGood(): BelongsTo
    {
        return $this->belongsTo(IncomingGood::class);
    }

    public function recordedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'recorded_by');
    }
}
