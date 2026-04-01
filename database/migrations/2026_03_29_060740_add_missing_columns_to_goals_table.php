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
        Schema::table('goals', function (Blueprint $table) {
            $table->renameColumn('image', 'image_url');
        });
        Schema::table('goals', function (Blueprint $table) {
            $table->date('target_date')->after('description')->nullable();
            $table->string('category')->after('target_date')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('goals', function (Blueprint $table) {
            $table->dropColumn(['target_date', 'category']);
            $table->renameColumn('image_url', 'image');
        });
    }
};
