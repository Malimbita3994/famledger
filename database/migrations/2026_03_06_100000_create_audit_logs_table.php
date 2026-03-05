<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     * Single table for both application audit (logins, actions) and database audit (model changes).
     */
    public function up(): void
    {
        Schema::create('audit_logs', function (Blueprint $table) {
            $table->id();
            $table->string('type', 32)->default('application'); // application | database
            $table->string('action', 64)->index(); // login, logout, created, updated, deleted, etc.
            $table->string('subject_type', 128)->nullable()->index(); // App\Models\Expense, etc.
            $table->unsignedBigInteger('subject_id')->nullable()->index();
            $table->text('description')->nullable();
            $table->json('properties')->nullable(); // old_values, new_values, extra context
            $table->foreignId('user_id')->nullable()->constrained()->nullOnDelete();
            $table->foreignId('family_id')->nullable()->constrained()->nullOnDelete();
            $table->string('ip', 45)->nullable();
            $table->text('user_agent')->nullable();
            $table->string('url', 512)->nullable();
            $table->string('request_method', 16)->nullable();
            $table->timestamps();

            $table->index(['family_id', 'created_at']);
            $table->index(['user_id', 'created_at']);
            $table->index(['type', 'created_at']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('audit_logs');
    }
};
