<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Tabel untuk mencatat susut / barang busuk yang ditemukan SETELAH barang masuk.
     *
     * Berbeda dari spoilage saat input barang masuk, tabel ini untuk:
     * - Busuk yang ditemukan keesokan harinya
     * - Barang yang jatuh/rusak saat di gudang
     * - Penyusutan alami (sayur layu, buah keriput, dll)
     *
     * Setiap entri akan mengurangi stok produk secara otomatis.
     */
    public function up(): void
    {
        Schema::create('product_spoilages', function (Blueprint $table) {
            $table->id();
            $table->date('date')->index();
            $table->foreignId('product_id')->constrained()->restrictOnDelete();
            $table->foreignId('incoming_good_id')->nullable()->constrained('incoming_goods')->nullOnDelete(); // link ke batch mana
            $table->decimal('spoilage_qty', 10, 4);    // jumlah busuk (kg atau pcs)
            $table->string('unit', 30);                 // satuan: 'kg' atau 'pcs'/'botol'
            $table->enum('reason', [
                'busuk',        // buah/sayur membusuk
                'rusak',        // kemasan rusak, botol pecah
                'kadaluarsa',   // lewat expired date
                'penyusutan',   // susut alami (penguapan air, dll)
                'lainnya',
            ])->default('busuk');
            $table->text('notes')->nullable();
            $table->foreignId('recorded_by')->constrained('users')->restrictOnDelete();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('product_spoilages');
    }
};
