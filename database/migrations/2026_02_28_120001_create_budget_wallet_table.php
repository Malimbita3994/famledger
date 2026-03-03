<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Pivot: budget applies to these wallets (when type = wallet or selected_wallets).
     */
    public function up(): void
    {
        Schema::create('budget_wallet', function (Blueprint $table) {
            $table->id();
            $table->foreignId('budget_id')->constrained()->cascadeOnDelete();
            $table->foreignId('wallet_id')->constrained()->cascadeOnDelete();
            $table->timestamps();
            $table->unique(['budget_id', 'wallet_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('budget_wallet');
    }
};
