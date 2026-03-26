<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * parent_id null = root row (category group or legacy flat category).
     * sort_order > 0 marks canonical top-level groups used on the income form.
     */
    public function up(): void
    {
        Schema::table('income_categories', function (Blueprint $table) {
            $table->foreignId('parent_id')->nullable()->after('family_id')->constrained('income_categories')->nullOnDelete();
            $table->unsignedTinyInteger('sort_order')->default(0)->after('name');
        });
    }

    public function down(): void
    {
        Schema::table('income_categories', function (Blueprint $table) {
            $table->dropForeign(['parent_id']);
            $table->dropColumn(['parent_id', 'sort_order']);
        });
    }
};
