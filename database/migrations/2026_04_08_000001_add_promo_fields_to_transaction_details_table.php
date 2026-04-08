<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Tambah kolom is_promo dan original_price ke transaction_details.
     *
     * is_promo      — flag boolean: apakah item ini dibeli dengan harga promo
     * original_price — harga normal produk saat transaksi terjadi (untuk ditampilkan di struk)
     */
    public function up(): void
    {
        Schema::table('transaction_details', function (Blueprint $table) {
            // Apakah item ini dijual dengan harga promo
            $table->boolean('is_promo')->default(false)->after('price');
            // Harga normal (sebelum promo) saat transaksi, null jika bukan promo
            $table->decimal('original_price', 12, 2)->nullable()->after('is_promo');
        });
    }

    public function down(): void
    {
        Schema::table('transaction_details', function (Blueprint $table) {
            $table->dropColumn(['is_promo', 'original_price']);
        });
    }
};
