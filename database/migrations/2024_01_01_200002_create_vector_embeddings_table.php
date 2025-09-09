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
        Schema::create('vector_embeddings', function (Blueprint $table) {
            $table->id();
            $table->morphs('embeddable'); // embeddable_id, embeddable_type

            $table->json('vector'); // The actual vector data
            $table->integer('dimensions'); // Vector dimensions (e.g., 512, 1024)
            $table->string('model'); // Model used to generate (e.g., 'clip-vit-b-32')
            $table->string('provider'); // Provider (e.g., 'openai', 'pinecone', 'local')

            $table->json('metadata')->nullable(); // Additional metadata
            $table->timestamps();

            // Indexes for efficient querying
            $table->index(['embeddable_type', 'embeddable_id']);
            $table->index(['model', 'provider']);
            $table->index('dimensions');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('vector_embeddings');
    }
};
