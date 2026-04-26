<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Tambah kolom sistem multi-satuan ke tabel products.
     *
     * product_type     : 'weight' (per kg/gram) atau 'count' (per pcs/botol/bungkus)
     * unit             : satuan eceran, misal 'kg', 'botol', 'bungkus', 'pcs'
     * bulk_unit        : satuan grosir/masuk, misal 'krat', 'karton', 'kardus', 'sak'
     * bulk_conversion  : 1 bulk_unit = N unit eceran (misal 24 botol per krat, 15 kg per krat)
     * krat_weight_kg   : berat krat/wadah kosong dalam kg (untuk produk weight-based)
     * price_per_unit   : harga jual eceran (per kg / per pcs)
     * price_per_bulk   : harga jual grosir (per krat/karton), nullable jika tidak dijual grosir
     */
    public function up(): void
    {
        Schema::table('products', function (Blueprint $table) {
            // Tipe produk: weight (timbangan) atau count (biji/pcs)
            $table->enum('product_type', ['weight', 'count'])->default('count')->after('category_id');

            // Satuan
            $table->string('unit', 30)->default('pcs')->after('product_type');         // kg, botol, bungkus, pcs
            $table->string('bulk_unit', 30)->nullable()->after('unit');                 // krat, karton, kardus, sak
            $table->decimal('bulk_conversion', 10, 4)->default(1)->after('bulk_unit'); // 1 bulk = N unit
            $table->decimal('krat_weight_kg', 8, 3)->default(0)->after('bulk_conversion'); // berat krat kosong

            // Harga jual (price sudah ada, kita tambah per_unit dan per_bulk yang lebih eksplisit)
            $table->decimal('price_per_unit', 12, 2)->default(0)->after('price');       // harga eceran
            $table->decimal('price_per_bulk', 12, 2)->nullable()->after('price_per_unit'); // harga grosir (nullable)
            $table->decimal('purchase_price_per_bulk', 12, 2)->default(0)->after('price_per_bulk'); // HPP referensi

            // Stok: ubah ke decimal agar support pecahan (kg)
            // (sudah ada migration sebelumnya untuk ini, skip jika sudah decimal)
        });
    }

    public function down(): void
    {
        Schema::table('products', function (Blueprint $table) {
            $table->dropColumn([
                'product_type',
                'unit',
                'bulk_unit',
                'bulk_conversion',
                'krat_weight_kg',
                'price_per_unit',
                'price_per_bulk',
                'purchase_price_per_bulk',
            ]);
        });
    }
};
