<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::create('images', function (Blueprint $table) {
            $table->id();
            $table->string('uuid')->unique();
            $table->string('filename');
            $table->string('title')->nullable();
            $table->text('caption')->nullable();
            $table->string('mime_type');
            $table->integer('width');
            $table->integer('height');
            $table->bigInteger('size_bytes');
            $table->json('exif_data')->nullable();
            $table->string('privacy_level')->default('public');
            $table->foreignId('user_id')->constrained()->onDelete('cascade');
            $table->integer('view_count')->default(0);
            $table->timestamps();
        });
    }

    public function down()
    {
        Schema::dropIfExists('images');
    }
};