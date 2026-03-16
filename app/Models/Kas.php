<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Kas extends Model
{
    protected $table = 'kas';

    protected $fillable = [
        'type',
        'amount',
        'balance_before',
        'balance_after',
        'description',
        'user_id',
    ];

    protected $casts = [
        'amount'         => 'decimal:2',
        'balance_before' => 'decimal:2',
        'balance_after'  => 'decimal:2',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    // Ambil saldo kas saat ini
    public static function currentBalance(): float
    {
        $latest = static::latest()->first();
        return $latest ? (float) $latest->balance_after : 0;
    }
}
