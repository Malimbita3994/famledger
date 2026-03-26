<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('incomes', function (Blueprint $table) {
            $table->boolean('is_taxable')->default(true)->after('is_recurring');
            $table->string('recurring_frequency', 16)->nullable()->after('is_taxable');
            $table->string('source_entity_type', 24)->nullable()->after('source');
            $table->foreignId('linked_project_id')->nullable()->after('family_liability_id')->constrained('projects')->nullOnDelete();
            $table->foreignId('linked_property_id')->nullable()->after('linked_project_id')->constrained('properties')->nullOnDelete();
        });
    }

    public function down(): void
    {
        Schema::table('incomes', function (Blueprint $table) {
            $table->dropForeign(['linked_project_id']);
            $table->dropForeign(['linked_property_id']);
            $table->dropColumn([
                'is_taxable',
                'recurring_frequency',
                'source_entity_type',
                'linked_project_id',
                'linked_property_id',
            ]);
        });
    }
};
