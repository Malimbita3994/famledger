<?php

use Illuminate\Database\Migrations\Migration;

return new class extends Migration {
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // This migration is now a no‑op because the index is created safely in
        // 2026_03_19_000003_shorten_personal_access_tokens_columns.php.
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // No action needed – the index is managed by the newer migration.
    }
};
