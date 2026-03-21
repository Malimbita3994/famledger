<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('notification_faqs', function (Blueprint $table) {
            $table->text('question')->change();
        });
    }

    public function down(): void
    {
        Schema::table('notification_faqs', function (Blueprint $table) {
            $table->string('question', 500)->change();
        });
    }
};
