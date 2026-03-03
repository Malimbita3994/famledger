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
        Schema::table('family_user', function (Blueprint $table) {
            $table->string('member_name')->nullable()->after('user_id');
            $table->string('sex', 20)->nullable()->after('member_name'); // male, female
            $table->string('member_type', 20)->nullable()->after('sex');   // adult, child
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('family_user', function (Blueprint $table) {
            $table->dropColumn(['member_name', 'sex', 'member_type']);
        });
    }
};
