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
        Schema::create('family_wealth_trends', function (Blueprint $table) {
            $table->id();
            $table->foreignId('family_id')->constrained()->cascadeOnDelete();
            $table->decimal('wallet_total', 15, 2)->default(0);
            $table->decimal('property_total', 15, 2)->default(0);
            $table->decimal('project_total', 15, 2)->default(0);
            $table->decimal('liability_total', 15, 2)->default(0);
            $table->decimal('net_wealth', 15, 2)->default(0);
            $table->date('snapshot_date');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('family_wealth_trends');
    }
};
