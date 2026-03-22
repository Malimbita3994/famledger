<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Create notification_support_contacts only. Copy from notification_page_contents
     * runs in 2026_03_21_120001 (after that table exists).
     */
    public function up(): void
    {
        if (Schema::hasTable('notification_support_contacts')) {
            return;
        }

        Schema::create('notification_support_contacts', function (Blueprint $table) {
            $table->id();
            $table->string('title', 255);
            $table->text('body');
            $table->string('link_url', 2048)->nullable();
            $table->string('link_label', 255)->nullable();
            $table->unsignedInteger('sort_order')->default(0);
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('notification_support_contacts');
    }
};
