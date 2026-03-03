<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Pivot: budget applies to these expense categories (when type = category).
     */
    public function up(): void
    {
        Schema::create('budget_category', function (Blueprint $table) {
            $table->id();
            $table->foreignId('budget_id')->constrained()->cascadeOnDelete();
            $table->foreignId('expense_category_id')->constrained('expense_categories')->cascadeOnDelete();
            $table->timestamps();
            $table->unique(['budget_id', 'expense_category_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('budget_category');
    }
};
