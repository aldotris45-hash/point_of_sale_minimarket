<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class IncomingGood extends Model
{
    use HasFactory;

    protected $fillable = [
        'date',
        'supplier_id',
        'product_id',
        'purchase_price',
        'quantity',
        'total',
        'user_id',
        'notes',
    ];

    protected $casts = [
        'date' => 'date',
        'purchase_price' => 'decimal:2',
        'total' => 'decimal:2',
    ];

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
}
