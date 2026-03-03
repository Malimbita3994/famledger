<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('projects', function (Blueprint $table) {
            if (! Schema::hasColumn('projects', 'budget_id')) {
                $table->foreignId('budget_id')
                    ->nullable()
                    ->after('wallet_id')
                    ->constrained('budgets')
                    ->cascadeOnDelete();
            }
        });
    }

    public function down(): void
    {
        Schema::table('projects', function (Blueprint $table) {
            if (Schema::hasColumn('projects', 'budget_id')) {
                $table->dropConstrainedForeignId('budget_id');
            }
        });
    }
};

