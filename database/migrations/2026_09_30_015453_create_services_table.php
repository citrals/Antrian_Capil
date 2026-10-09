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
        Schema::create('services', function (Blueprint $table) {
            $table->id();
            $table->string('slug')->unique();
            $table->string('name');
            $table->string('subtitle')->nullable();
            $table->text('description');
            $table->string('icon')->default('id-card');
            $table->string('badge')->nullable();
            $table->unsignedInteger('quota_morning')->default(50);
            $table->unsignedInteger('quota_afternoon')->default(50);
            $table->unsignedInteger('quota_evening')->default(40);
            $table->unsignedInteger('estimated_minutes')->default(15);
            $table->json('requirements')->nullable();
            $table->boolean('is_active')->default(true);
            $table->unsignedInteger('sort_order')->default(0);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('services');
    }
};
