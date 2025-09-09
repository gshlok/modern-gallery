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
        Schema::create('analytics_events', function (Blueprint $table) {
            $table->id();
            $table->string('event_type'); // 'image_view', 'image_download', 'album_view', etc.
            $table->morphs('trackable'); // The entity being tracked
            $table->foreignId('user_id')->nullable()->constrained()->onDelete('set null');

            $table->string('session_id')->nullable();
            $table->string('ip_address', 45)->nullable();
            $table->string('user_agent')->nullable();
            $table->string('referer')->nullable();
            $table->json('metadata')->nullable(); // Additional context data

            $table->timestamps();

            $table->index(['trackable_id', 'trackable_type', 'event_type']);
            $table->index(['event_type', 'created_at']);
            $table->index(['user_id', 'event_type']);
            $table->index('session_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('analytics_events');
    }
};
