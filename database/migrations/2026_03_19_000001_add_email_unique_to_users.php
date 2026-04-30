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
        // Only add the unique index if it does not already exist
        $hasIndex = collect(DB::select("SHOW INDEX FROM `users` WHERE Key_name = 'users_email_unique'"))
            ->isNotEmpty();
        if (! $hasIndex) {
            Schema::table('users', function (Blueprint $table) {
                $table->unique('email');
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Drop the unique index only if it exists
        $hasIndex = collect(DB::select("SHOW INDEX FROM `users` WHERE Key_name = 'users_email_unique'"))
            ->isNotEmpty();
        if ($hasIndex) {
            Schema::table('users', function (Blueprint $table) {
                $table->dropUnique(['email']);
            });
        }
    }
};
