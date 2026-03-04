<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::table('family_liabilities', function (Blueprint $table) {
            if (! Schema::hasColumn('family_liabilities', 'wallet_id')) {
                $table->foreignId('wallet_id')->nullable()->constrained()->nullOnDelete();
            }
            if (! Schema::hasColumn('family_liabilities', 'project_id')) {
                $table->foreignId('project_id')->nullable()->constrained()->nullOnDelete();
            }
            if (! Schema::hasColumn('family_liabilities', 'property_id')) {
                $table->foreignId('property_id')->nullable()->constrained()->nullOnDelete();
            }
            if (! Schema::hasColumn('family_liabilities', 'budget_id')) {
                $table->foreignId('budget_id')->nullable()->constrained()->nullOnDelete();
            }
            if (! Schema::hasColumn('family_liabilities', 'savings_goal_id')) {
                $table->foreignId('savings_goal_id')->nullable()->constrained('savings_goals')->nullOnDelete();
            }
        });
    }

    public function down(): void
    {
        Schema::table('family_liabilities', function (Blueprint $table) {
            if (Schema::hasColumn('family_liabilities', 'wallet_id')) {
                $table->dropConstrainedForeignId('wallet_id');
            }
            if (Schema::hasColumn('family_liabilities', 'project_id')) {
                $table->dropConstrainedForeignId('project_id');
            }
            if (Schema::hasColumn('family_liabilities', 'property_id')) {
                $table->dropConstrainedForeignId('property_id');
            }
            if (Schema::hasColumn('family_liabilities', 'budget_id')) {
                $table->dropConstrainedForeignId('budget_id');
            }
            if (Schema::hasColumn('family_liabilities', 'savings_goal_id')) {
                $table->dropConstrainedForeignId('savings_goal_id');
            }
        });
    }
};

