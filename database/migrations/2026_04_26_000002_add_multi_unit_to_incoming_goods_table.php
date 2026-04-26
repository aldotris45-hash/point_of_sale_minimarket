<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Redesign tabel incoming_goods untuk sistem multi-satuan.
     *
     * Untuk produk COUNT-based (Coca Cola, Indomie, dll):
     *   - input: incoming_qty (krat) + conversion_factor (24 botol/krat)
     *   - converted_qty = incoming_qty × conversion_factor
     *   - spoilage_qty = jumlah pcs yang rusak/pecah
     *   - stock_added = converted_qty - spoilage_qty
     *
     * Untuk produk WEIGHT-based (sayur, buah, dll):
     *   - input: gross_weight_kg (timbang kotor semua termasuk krat)
     *   - krat_weight_used_kg = krat_weight × incoming_qty (auto dari master produk)
     *   - net_weight_kg = gross_weight_kg - krat_weight_used_kg
     *   - spoilage_qty = kg yang busuk/rusak
     *   - stock_added = net_weight_kg - spoilage_qty
     */
    public function up(): void
    {
        Schema::table('incoming_goods', function (Blueprint $table) {
            // Satuan masuk
            $table->string('incoming_unit', 30)->nullable()->after('product_id');   // 'krat', 'karton', 'sak'
            $table->decimal('incoming_qty', 10, 2)->default(0)->after('incoming_unit'); // qty bulk

            // Untuk produk weight-based (timbangan)
            $table->decimal('gross_weight_kg', 10, 3)->nullable()->after('incoming_qty');     // berat kotor total
            $table->decimal('krat_weight_used_kg', 10, 3)->nullable()->after('gross_weight_kg'); // berat semua krat
            $table->decimal('net_weight_kg', 10, 3)->nullable()->after('krat_weight_used_kg');   // berat bersih

            // Konversi (untuk count-based)
            $table->decimal('conversion_factor', 10, 4)->default(1)->after('net_weight_kg'); // 1 krat = N pcs

            // Jumlah yang dikonversi sebelum susut/busuk
            $table->decimal('converted_qty', 10, 4)->default(0)->after('conversion_factor');

            // Spoilage / Barang busuk atau rusak
            $table->decimal('spoilage_qty', 10, 4)->default(0)->after('converted_qty');
            $table->text('spoilage_notes')->nullable()->after('spoilage_qty');

            // Stock yang benar-benar ditambahkan
            $table->decimal('stock_added', 10, 4)->default(0)->after('spoilage_notes');

            // Harga beli per bulk (bisa beda tiap kedatangan)
            $table->decimal('purchase_price_per_bulk', 12, 2)->default(0)->after('stock_added');

            // Ubah quantity lama jadi nullable (tidak dipakai lagi, diganti incoming_qty)
            // Biarkan quantity kolom lama agar tidak break existing data
        });
    }

    public function down(): void
    {
        Schema::table('incoming_goods', function (Blueprint $table) {
            $table->dropColumn([
                'incoming_unit',
                'incoming_qty',
                'gross_weight_kg',
                'krat_weight_used_kg',
                'net_weight_kg',
                'conversion_factor',
                'converted_qty',
                'spoilage_qty',
                'spoilage_notes',
                'stock_added',
                'purchase_price_per_bulk',
            ]);
        });
    }
};
