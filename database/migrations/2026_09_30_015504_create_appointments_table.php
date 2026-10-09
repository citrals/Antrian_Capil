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
        Schema::create('appointments', function (Blueprint $table) {
            $table->id();
            $table->string('ticket_number')->unique();
            $table->string('queue_number');
            $table->foreignId('service_id')->constrained('services')->cascadeOnDelete();
            $table->string('nik', 16);
            $table->string('full_name');
            $table->string('phone', 25);
            $table->string('email')->nullable();
            $table->date('appointment_date');
            $table->enum('shift', ['pagi', 'siang', 'sore']);
            $table->enum('status', ['confirmed', 'checked_in', 'serving', 'completed', 'cancelled'])->default('confirmed');
            $table->text('notes')->nullable();
            $table->timestamps();

            $table->index(['appointment_date', 'shift', 'service_id']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('appointments');
    }
};
