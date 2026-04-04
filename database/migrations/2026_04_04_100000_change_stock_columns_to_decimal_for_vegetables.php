<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // Ubah struktur stock dan quantity menjadi desimal karena tipe barang (sayur) bisa satuan gram (0.5 kg dsb)
        DB::statement('ALTER TABLE products MODIFY stock DECIMAL(10, 3) NOT NULL DEFAULT 0');
        DB::statement('ALTER TABLE products MODIFY min_stock DECIMAL(10, 3) NOT NULL DEFAULT 0');
        
        DB::statement('ALTER TABLE incoming_goods MODIFY quantity DECIMAL(10, 3) NOT NULL DEFAULT 0');
        DB::statement('ALTER TABLE transaction_details MODIFY quantity DECIMAL(10, 3) NOT NULL DEFAULT 0');
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        DB::statement('ALTER TABLE products MODIFY stock INT UNSIGNED NOT NULL DEFAULT 0');
        DB::statement('ALTER TABLE products MODIFY min_stock INT UNSIGNED NOT NULL DEFAULT 0');

        DB::statement('ALTER TABLE incoming_goods MODIFY quantity INT NOT NULL DEFAULT 0');
        DB::statement('ALTER TABLE transaction_details MODIFY quantity INT NOT NULL DEFAULT 0');
    }
};
