<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     * Every expense reduces a wallet balance. Family → Wallet → Expense.
     */
    public function up(): void
    {
        Schema::create('expenses', function (Blueprint $table) {
            $table->id();
            $table->foreignId('family_id')->constrained()->cascadeOnDelete();
            $table->foreignId('wallet_id')->constrained()->cascadeOnDelete();
            $table->foreignId('category_id')->nullable()->constrained('expense_categories')->nullOnDelete();
            $table->unsignedBigInteger('project_id')->nullable();
            $table->decimal('amount', 15, 2);
            $table->string('currency_code', 3);
            $table->string('description')->nullable();
            $table->date('expense_date');
            $table->foreignId('paid_by')->nullable()->constrained('users')->nullOnDelete();
            $table->string('merchant')->nullable();
            $table->string('payment_method', 50)->nullable();
            $table->string('reference')->nullable();
            $table->boolean('is_recurring')->default(false);
            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('expenses');
    }
};
