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
        Schema::create('albums', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->onDelete('cascade');
            $table->string('title');
            $table->text('description')->nullable();
            $table->string('slug')->unique();
            $table->string('cover_image')->nullable();
            $table->enum('visibility', ['public', 'unlisted', 'private'])->default('public');
            $table->boolean('is_featured')->default(false);
            $table->integer('sort_order')->default(0);
            $table->json('settings')->nullable(); // Custom album settings
            $table->timestamps();

            $table->index(['user_id', 'visibility']);
            $table->index(['visibility', 'is_featured']);
            $table->index('slug');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('albums');
    }
};
