<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     * Budgets = planning layer; they guide decisions, do not move money.
     */
    public function up(): void
    {
        Schema::create('budgets', function (Blueprint $table) {
            $table->id();
            $table->foreignId('family_id')->constrained()->cascadeOnDelete();
            $table->string('name');
            $table->string('type', 30); // family, category, wallet, project
            $table->decimal('amount', 15, 2);
            $table->string('currency_code', 3);
            $table->date('start_date');
            $table->date('end_date');
            $table->string('recurrence', 20)->default('none'); // none, weekly, monthly, yearly
            $table->unsignedBigInteger('project_id')->nullable();
            $table->string('status', 20)->default('active'); // active, archived
            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('budgets');
    }
};
