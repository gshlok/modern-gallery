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
        Schema::create('images', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->onDelete('cascade');
            $table->foreignId('album_id')->nullable()->constrained()->onDelete('set null');

            // Basic image info
            $table->string('title');
            $table->text('description')->nullable();
            $table->text('alt_text')->nullable();
            $table->string('slug')->unique();

            // File information
            $table->string('filename'); // Original filename
            $table->string('path'); // Storage path
            $table->string('disk')->default('local'); // Storage disk
            $table->string('mime_type');
            $table->bigInteger('file_size'); // Size in bytes
            $table->string('hash', 64); // File hash for deduplication

            // Image dimensions
            $table->integer('width');
            $table->integer('height');
            $table->decimal('aspect_ratio', 5, 3); // width/height

            // Privacy and permissions
            $table->enum('visibility', ['public', 'unlisted', 'private'])->default('public');
            $table->string('license')->nullable(); // Creative Commons, etc.
            $table->boolean('allow_download')->default(true);
            $table->boolean('is_featured')->default(false);

            // Metadata
            $table->json('exif_data')->nullable(); // Camera data, GPS, etc.
            $table->json('thumbnails')->nullable(); // Generated thumbnail paths

            // Analytics
            $table->bigInteger('view_count')->default(0);
            $table->bigInteger('download_count')->default(0);
            $table->timestamp('last_viewed_at')->nullable();

            // AI/Generation metadata (for future use)
            $table->boolean('is_ai_generated')->default(false);
            $table->text('generation_prompt')->nullable();
            $table->json('generation_metadata')->nullable(); // Provider, seed, etc.

            // Vector search (for future use)
            $table->json('vector_embedding')->nullable();
            $table->timestamp('embedding_generated_at')->nullable();

            $table->timestamps();

            // Indexes for performance
            $table->index(['user_id', 'visibility']);
            $table->index(['album_id', 'visibility']);
            $table->index(['visibility', 'is_featured']);
            $table->index('hash'); // For deduplication
            $table->index('slug');
            $table->index(['mime_type', 'visibility']);
            $table->index('view_count');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('images');
    }
};
