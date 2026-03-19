<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration {
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // Shorten the string columns to avoid the 3072‑byte index limit
        Schema::table('personal_access_tokens', function (Blueprint $table) {
            // Only change if current length is >191
            $col = DB::selectOne("SHOW COLUMNS FROM `personal_access_tokens` WHERE Field = 'tokenable_type'");
            if ($col && $col->Type !== 'varchar(191)') {
                $table->string('tokenable_type', 191)->change();
            }
            $col = DB::selectOne("SHOW COLUMNS FROM `personal_access_tokens` WHERE Field = 'name'");
            if ($col && $col->Type !== 'varchar(191)') {
                $table->string('name', 191)->change();
            }
        });

        // Add the composite index if it does not already exist
        $hasIndex = collect(DB::select(
            "SHOW INDEX FROM `personal_access_tokens` WHERE Key_name = 'personal_access_tokens_lookup_index'"
        ))->isNotEmpty();
        if (! $hasIndex) {
            Schema::table('personal_access_tokens', function (Blueprint $table) {
                $table->index(['tokenable_id', 'tokenable_type', 'name'], 'personal_access_tokens_lookup_index');
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Drop the index if it exists
        $hasIndex = collect(DB::select(
            "SHOW INDEX FROM `personal_access_tokens` WHERE Key_name = 'personal_access_tokens_lookup_index'"
        ))->isNotEmpty();
        if ($hasIndex) {
            Schema::table('personal_access_tokens', function (Blueprint $table) {
                $table->dropIndex('personal_access_tokens_lookup_index');
            });
        }

        // Revert column lengths back to 255 (original Sanctum definition)
        Schema::table('personal_access_tokens', function (Blueprint $table) {
            $col = DB::selectOne("SHOW COLUMNS FROM `personal_access_tokens` WHERE Field = 'tokenable_type'");
            if ($col && $col->Type !== 'varchar(255)') {
                $table->string('tokenable_type', 255)->change();
            }
            $col = DB::selectOne("SHOW COLUMNS FROM `personal_access_tokens` WHERE Field = 'name'");
            if ($col && $col->Type !== 'varchar(255)') {
                $table->string('name', 255)->change();
            }
        });
    }
};
