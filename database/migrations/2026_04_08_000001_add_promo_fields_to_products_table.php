<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('products', function (Blueprint $table) {
            $table->decimal('promo_price', 12, 2)->nullable()->after('price')
                ->comment('Harga promo. Jika diisi, price menjadi harga coret di katalog.');
            $table->string('promo_label', 50)->nullable()->after('promo_price')
                ->comment('Label promo opsional, e.g. "Hemat 30%" atau "Flash Sale"');
        });
    }

    public function down(): void
    {
        Schema::table('products', function (Blueprint $table) {
            $table->dropColumn(['promo_price', 'promo_label']);
        });
    }
};
