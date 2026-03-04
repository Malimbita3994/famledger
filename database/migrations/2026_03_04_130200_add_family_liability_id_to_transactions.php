<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::table('incomes', function (Blueprint $table) {
            if (! Schema::hasColumn('incomes', 'family_liability_id')) {
                $table->foreignId('family_liability_id')->nullable()->after('category_id')->constrained('family_liabilities')->nullOnDelete();
            }
        });

        Schema::table('expenses', function (Blueprint $table) {
            if (! Schema::hasColumn('expenses', 'family_liability_id')) {
                $table->foreignId('family_liability_id')->nullable()->after('project_id')->constrained('family_liabilities')->nullOnDelete();
            }
        });
    }

    public function down(): void
    {
        Schema::table('incomes', function (Blueprint $table) {
            if (Schema::hasColumn('incomes', 'family_liability_id')) {
                $table->dropConstrainedForeignId('family_liability_id');
            }
        });

        Schema::table('expenses', function (Blueprint $table) {
            if (Schema::hasColumn('expenses', 'family_liability_id')) {
                $table->dropConstrainedForeignId('family_liability_id');
            }
        });
    }
};

