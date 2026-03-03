<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Link adjustment income/expense to the reconciliation that created them.
     */
    public function up(): void
    {
        Schema::table('incomes', function (Blueprint $table) {
            $table->foreignId('reconciliation_id')->nullable()->after('created_by')->constrained('wallet_reconciliations')->nullOnDelete();
        });
        Schema::table('expenses', function (Blueprint $table) {
            $table->foreignId('reconciliation_id')->nullable()->after('created_by')->constrained('wallet_reconciliations')->nullOnDelete();
        });
    }

    public function down(): void
    {
        Schema::table('incomes', function (Blueprint $table) {
            $table->dropForeign(['reconciliation_id']);
        });
        Schema::table('expenses', function (Blueprint $table) {
            $table->dropForeign(['reconciliation_id']);
        });
    }
};
