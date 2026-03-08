<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     * Ledger table for all wallet transactions to enable computed balances.
     */
    public function up(): void
    {
        Schema::create('wallet_transactions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('wallet_id')->constrained()->cascadeOnDelete();
            $table->string('transaction_type', 50); // income, expense, transfer_in, transfer_out, savings_contribution, savings_allocation, etc.
            $table->decimal('amount', 15, 2);
            $table->text('description')->nullable();
            $table->string('reference_type', 50)->nullable(); // income, expense, transfer, savings_contribution, savings_budget_allocation, etc.
            $table->unsignedBigInteger('reference_id')->nullable();
            $table->decimal('balance_after', 15, 2)->nullable(); // Computed balance after this transaction
            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();
            
            $table->index(['wallet_id', 'created_at']);
            $table->index(['reference_type', 'reference_id']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('wallet_transactions');
    }
};
