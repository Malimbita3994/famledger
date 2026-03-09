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
            $table->string('leave_reason')->nullable()->after('status');
            $table->text('leave_notes')->nullable()->after('leave_reason');
            $table->timestamp('leave_requested_at')->nullable()->after('leave_notes');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('family_user', function (Blueprint $table) {
            $table->dropColumn(['leave_reason', 'leave_notes', 'leave_requested_at']);
        });
    }
};

