<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('properties', function (Blueprint $table) {
            $table->id();
            $table->foreignId('family_id')->constrained()->cascadeOnDelete();
            $table->string('property_code')->unique();
            $table->string('name');
            $table->foreignId('category_id')->nullable()->constrained('property_categories')->nullOnDelete();
            $table->foreignId('subcategory_id')->nullable()->constrained('property_categories')->nullOnDelete();
            $table->string('ownership_type')->nullable(); // individual / joint / family_trust
            $table->unsignedBigInteger('owner_family_member_id')->nullable(); // family_user pivot id

            // Financial
            $table->date('acquisition_date')->nullable();
            $table->string('acquisition_method')->nullable(); // purchase / inheritance / gift / exchange
            $table->decimal('purchase_price', 18, 2)->nullable();
            $table->decimal('current_estimated_value', 18, 2)->nullable();
            $table->date('valuation_date')->nullable();
            $table->string('currency_code', 3)->nullable();
            $table->string('depreciation_method')->nullable();
            $table->unsignedInteger('useful_life_years')->nullable();

            // Status
            $table->string('status')->default('active');
            $table->string('insurance_status')->nullable();
            $table->string('legal_status')->nullable();

            // Location
            $table->string('country')->nullable();
            $table->string('region_city')->nullable();
            $table->string('address')->nullable();
            $table->decimal('gps_lat', 10, 7)->nullable();
            $table->decimal('gps_lng', 10, 7)->nullable();

            // Documentation
            $table->string('title_number')->nullable();
            $table->text('notes')->nullable();

            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->foreignId('updated_by')->nullable()->constrained('users')->nullOnDelete();

            $table->timestamps();
            $table->softDeletes();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('properties');
    }
};

