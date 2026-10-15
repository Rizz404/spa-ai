<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('reservations', function (Blueprint $table) {
            $table->id();
            $table->string('reference', 20)->unique();
            $table->foreignId('spa_id')->constrained()->cascadeOnDelete();
            $table->foreignId('treatment_room_id')->constrained()->cascadeOnDelete();
            $table->foreignId('treatment_id')->constrained()->restrictOnDelete();
            $table->string('treatment_name');
            $table->unsignedSmallInteger('duration_minutes');
            $table->decimal('treatment_price', 12, 2);
            $table->foreignId('conversation_id')->nullable()->constrained()->nullOnDelete();
            $table->string('guest_name');
            $table->string('guest_email')->nullable();
            $table->string('guest_phone')->nullable();
            $table->string('contact_type', 20)->nullable(); // whatsapp | phone | email
            $table->date('reservation_date');
            $table->string('time_slot', 5);
            $table->unsignedTinyInteger('guests')->default(2);
            $table->string('occasion', 30)->nullable();
            $table->decimal('quoted_total', 12, 2)->default(0);
            $table->string('status')->default('pending'); // pending | confirmed | cancelled
            $table->text('notes')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('reservations');
    }
};
