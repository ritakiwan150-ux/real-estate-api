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

            $table->foreignId('owner_id')->constrained('users')->cascadeOnDelete();
            $table->foreignId('type_id')->constrained('property_types');
            $table->foreignId('city_id')->constrained('cities');

            $table->string('title_ar', 255)->nullable();
            $table->string('title_en', 255)->nullable();
            $table->string('slug', 255)->unique();

            $table->text('description_ar')->nullable();
            $table->text('description_en')->nullable();

            $table->enum('original_language', ['ar', 'en']);
            $table->enum('translation_status', ['pending', 'completed', 'failed'])->default('pending');

            $table->enum('operation', ['rent', 'sale']);
            $table->decimal('price', 12, 2);
            $table->decimal('area', 8, 2);

            $table->unsignedTinyInteger('bedrooms')->nullable();
            $table->unsignedTinyInteger('bathrooms')->nullable();

            $table->string('district', 255)->nullable();
            $table->decimal('latitude', 10, 7);
            $table->decimal('longitude', 10, 7);

            $table->enum('status', ['pending', 'published', 'rejected', 'archived'])->default('pending');
            $table->text('rejection_reason')->nullable();

            $table->boolean('is_featured')->default(false);
            $table->unsignedInteger('views_count')->default(0);

            $table->timestamps();
            $table->softDeletes();

            $table->index(['city_id', 'type_id', 'operation', 'price']);
            $table->index('status');
            $table->index('is_featured');
            $table->fullText(['title_ar', 'description_ar']);
            $table->fullText(['title_en', 'description_en']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('properties');
    }
};
