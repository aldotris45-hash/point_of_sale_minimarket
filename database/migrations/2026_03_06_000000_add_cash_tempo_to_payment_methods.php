<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // extend enum columns to include cash_tempo
        $driver = DB::getDriverName();
        
        if ($driver === 'mysql') {
            DB::statement("ALTER TABLE transactions MODIFY payment_method ENUM('cash','qris','cash_tempo') NOT NULL DEFAULT 'cash';");
            DB::statement("ALTER TABLE payments MODIFY method ENUM('cash','qris','cash_tempo') NOT NULL DEFAULT 'qris';");
        } else if ($driver === 'sqlite') {
            // SQLite doesn't support ENUM directly; the values are stored as strings
            // No migration needed for SQLite as it allows any string value in TEXT/VARCHAR columns
            // Just ensure the Enum class has the correct values
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        $driver = DB::getDriverName();
        
        if ($driver === 'mysql') {
            DB::statement("ALTER TABLE transactions MODIFY payment_method ENUM('cash','qris') NOT NULL DEFAULT 'cash';");
            DB::statement("ALTER TABLE payments MODIFY method ENUM('cash','qris') NOT NULL DEFAULT 'qris';");
        }
        // SQLite doesn't need reversal as it doesn't enforce enum constraints
    }
};
