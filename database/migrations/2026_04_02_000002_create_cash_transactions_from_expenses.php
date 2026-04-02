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
        // Add type and change category to string
        Schema::table('expenses', function (Blueprint $table) {
            $table->enum('type', ['in', 'out'])->default('out')->after('user_id');
            $table->renameColumn('expense_date', 'date');
        });

        // Modify category column (Native enum modification is tricky in SQLite/older MySQL without DBAL, 
        // so we drop and add it to be safe, or just change it to string).
        // Since Laravel 11 uses native schema modification gracefully now:
        Schema::table('expenses', function (Blueprint $table) {
            $table->string('category')->change();
        });

        // Rename table
        Schema::rename('expenses', 'cash_transactions');
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::rename('cash_transactions', 'expenses');

        Schema::table('expenses', function (Blueprint $table) {
            // Revert type and date name
            $table->dropColumn('type');
            $table->renameColumn('date', 'expense_date');
        });
    }
};
