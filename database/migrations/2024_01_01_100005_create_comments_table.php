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
        Schema::create('comments', function (Blueprint $table) {
            $table->id();
            $table->foreignId('image_id')->constrained()->onDelete('cascade');
            $table->foreignId('user_id')->nullable()->constrained()->onDelete('set null');
            $table->foreignId('parent_id')->nullable()->constrained('comments')->onDelete('cascade');

            $table->string('author_name')->nullable(); // For guest comments
            $table->string('author_email')->nullable(); // For guest comments
            $table->text('content');
            $table->string('ip_address', 45)->nullable();
            $table->string('user_agent')->nullable();

            $table->enum('status', ['pending', 'approved', 'rejected', 'spam'])->default('pending');
            $table->timestamp('approved_at')->nullable();
            $table->foreignId('approved_by')->nullable()->constrained('users')->onDelete('set null');

            $table->timestamps();

            $table->index(['image_id', 'status']);
            $table->index(['user_id', 'status']);
            $table->index('parent_id');
            $table->index('status');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('comments');
    }
};
