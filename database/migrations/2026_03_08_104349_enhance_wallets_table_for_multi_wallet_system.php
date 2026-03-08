<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('wallets', function (Blueprint $table) {
            // Remove stored balance since we'll compute it from transactions
            if (Schema::hasColumn('wallets', 'balance')) {
                $table->dropColumn('balance');
            }
            
            // Add fields for multi-wallet system
            if (!Schema::hasColumn('wallets', 'is_liquid')) {
                $table->boolean('is_liquid')->default(true)->after('is_primary');
            }
            if (!Schema::hasColumn('wallets', 'is_wealth_wallet')) {
                $table->boolean('is_wealth_wallet')->default(false)->after('is_liquid');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('wallets', function (Blueprint $table) {
            if (Schema::hasColumn('wallets', 'is_wealth_wallet')) {
                $table->dropColumn('is_wealth_wallet');
            }
            if (Schema::hasColumn('wallets', 'is_liquid')) {
                $table->dropColumn('is_liquid');
            }
            
            // Restore balance column if needed
            if (!Schema::hasColumn('wallets', 'balance')) {
                $table->decimal('balance', 15, 2)->default(0)->after('type');
            }
        });
    }
};
