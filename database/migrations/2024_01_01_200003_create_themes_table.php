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
        Schema::create('themes', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->nullable()->constrained()->onDelete('cascade');
            $table->string('name');
            $table->text('description')->nullable();
            $table->boolean('is_public')->default(false);
            $table->boolean('is_default')->default(false);

            // Color palette (CSS custom properties)
            $table->json('colors'); // Primary, secondary, accent, backgrounds, etc.
            $table->json('typography')->nullable(); // Font families, sizes, etc.
            $table->json('spacing')->nullable(); // Custom spacing scale
            $table->json('layout')->nullable(); // Layout preferences

            $table->integer('usage_count')->default(0);
            $table->timestamps();

            $table->index(['is_public', 'is_default']);
            $table->index(['user_id', 'is_public']);
            $table->index('usage_count');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('themes');
    }
};
