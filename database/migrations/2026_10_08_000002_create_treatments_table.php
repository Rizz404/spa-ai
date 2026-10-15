<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * The spa's treatments. Prices live here and nowhere else: the AI Spa Concierge may
     * only quote a price it has just read through a tool call.
     */
    public function up(): void
    {
        Schema::create('treatments', function (Blueprint $table) {
            $table->id();
            $table->foreignId('spa_id')->constrained()->cascadeOnDelete();
            $table->string('category'); // massage | body | facial | ritual | package
            $table->string('name');
            $table->string('slug');
            $table->text('description')->nullable();
            $table->json('translations')->nullable();
            $table->decimal('price', 12, 2);
            $table->string('image_url')->nullable();
            $table->json('tags')->nullable(); // vegan, vegetarian, gluten_free, signature, spicy, ...
            $table->json('contraindications')->nullable(); // milk, gluten, egg, nuts, soy, ...
            $table->string('duration_label')->nullable(); // hot | iced | hot_iced | shareable ...
            $table->unsignedSmallInteger('duration_minutes')->default(60);
            $table->boolean('is_featured')->default(false);
            $table->boolean('is_unavailable')->default(false);
            $table->boolean('is_active')->default(true);
            $table->unsignedSmallInteger('sort_order')->default(0);
            $table->softDeletes();
            $table->timestamps();
            $table->unique(['spa_id', 'slug']);
            $table->index(['spa_id', 'category']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('treatments');
    }
};
