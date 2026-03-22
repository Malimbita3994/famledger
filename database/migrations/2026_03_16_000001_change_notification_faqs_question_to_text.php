<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasTable('notification_faqs')) {
            return;
        }

        Schema::table('notification_faqs', function (Blueprint $table) {
            $table->text('question')->change();
        });
    }

    public function down(): void
    {
        if (! Schema::hasTable('notification_faqs')) {
            return;
        }

        Schema::table('notification_faqs', function (Blueprint $table) {
            $table->string('question', 500)->change();
        });
    }
};
