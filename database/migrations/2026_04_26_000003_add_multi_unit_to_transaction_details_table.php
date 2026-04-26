<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Tambah kolom multi-satuan ke transaction_details.
     *
     * sale_unit        : satuan yang dijual ('kg','botol','bungkus' atau 'krat','karton')
     * sale_qty         : jumlah dalam satuan jual (bisa decimal untuk kg)
     * qty_in_base_unit : konversi ke base unit → digunakan untuk deduct stok
     * is_bulk_sale     : apakah penjualan grosir (krat/karton)
     *
     * Struk akan tampilkan:
     *   - Eceran  : "2.5 kg Kol @ Rp 8.000/kg = Rp 20.000"
     *   - Grosir  : "2 krat Coca Cola @ Rp 95.000/krat = Rp 190.000 [PROMO PARTAI]"
     */
    public function up(): void
    {
        Schema::table('transaction_details', function (Blueprint $table) {
            // Satuan jual
            $table->string('sale_unit', 30)->nullable()->after('product_id');        // 'kg','botol' atau 'krat'
            $table->decimal('sale_qty', 10, 4)->nullable()->after('sale_unit');      // qty dalam satuan jual
            $table->decimal('qty_in_base_unit', 10, 4)->nullable()->after('sale_qty'); // qty untuk deduct stok

            // Penanda grosir
            $table->boolean('is_bulk_sale')->default(false)->after('qty_in_base_unit');

            // Catatan promo (sudah ada is_promo dari migration sebelumnya, ini tambahan label)
            // is_promo sudah ada, hanya tambah promo_bulk_label jika belum ada
            $table->string('promo_bulk_label', 100)->nullable()->after('is_bulk_sale');

            // Ubah quantity ke decimal agar support pecahan kg (jika belum decimal)
            // $table->decimal('quantity', 10, 4)->default(0)->change(); // uncomment jika perlu
        });
    }

    public function down(): void
    {
        Schema::table('transaction_details', function (Blueprint $table) {
            $table->dropColumn([
                'sale_unit',
                'sale_qty',
                'qty_in_base_unit',
                'is_bulk_sale',
                'promo_bulk_label',
            ]);
        });
    }
};
