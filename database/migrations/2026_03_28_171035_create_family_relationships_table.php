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
        Schema::create('family_relationships', function (Blueprint $table) {
            $table->id();
            $table->foreignId('family_id')->constrained()->onDelete('cascade');
            $table->foreignId('user_id')->constrained('users')->onDelete('cascade'); // mhusika
            $table->foreignId('related_user_id')->constrained('users')->onDelete('cascade'); // mwingine
            $table->enum('type', ['parent', 'child', 'spouse', 'sibling'])->default('parent');
            $table->timestamps();

            // hakikisha hakuna duplicate
            $table->unique(['family_id', 'user_id', 'related_user_id', 'type'], 'family_rel_unique');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('family_relationships');
    }
};
