<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Speed up family-scoped transaction lists ordered by date.
     */
    public function up(): void
    {
        Schema::table('incomes', function (Blueprint $table) {
            $table->index(['family_id', 'received_date'], 'incomes_family_received_date_index');
        });

        Schema::table('expenses', function (Blueprint $table) {
            $table->index(['family_id', 'expense_date'], 'expenses_family_expense_date_index');
        });
    }

    public function down(): void
    {
        Schema::table('incomes', function (Blueprint $table) {
            $table->dropIndex('incomes_family_received_date_index');
        });

        Schema::table('expenses', function (Blueprint $table) {
            $table->dropIndex('expenses_family_expense_date_index');
        });
    }
};
