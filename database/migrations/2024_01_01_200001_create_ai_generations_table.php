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
        Schema::create('ai_generations', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->onDelete('cascade');
            $table->foreignId('image_id')->nullable()->constrained()->onDelete('set null');

            $table->text('prompt');
            $table->text('negative_prompt')->nullable();
            $table->string('provider'); // 'openai', 'stability', 'midjourney', etc.
            $table->string('model'); // Model name/version
            $table->json('parameters'); // Seed, steps, guidance, etc.

            $table->enum('status', ['pending', 'processing', 'completed', 'failed'])->default('pending');
            $table->text('error_message')->nullable();
            $table->integer('processing_time')->nullable(); // Seconds
            $table->decimal('cost', 8, 4)->nullable(); // Generation cost

            $table->json('response_data')->nullable(); // Full API response
            $table->timestamps();

            $table->index(['user_id', 'status']);
            $table->index(['provider', 'status']);
            $table->index('status');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('ai_generations');
    }
};
