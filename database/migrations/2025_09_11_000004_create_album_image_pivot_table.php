<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('album_image', function (Blueprint $table) {
            $table->id();
            $table->foreignId('album_id')->constrained()->onDelete('cascade');
            $table->foreignId('image_id')->constrained()->onDelete('cascade');
            $table->unique(['album_id', 'image_id']);
            $table->timestamps();
        });

        // Backfill from legacy images.album_id if present
        if (Schema::hasColumn('images', 'album_id')) {
            $rows = DB::table('images')->whereNotNull('album_id')->select('id as image_id', 'album_id')->get();
            foreach ($rows as $row) {
                DB::table('album_image')->updateOrInsert(
                    ['album_id' => $row->album_id, 'image_id' => $row->image_id],
                    ['created_at' => now(), 'updated_at' => now()]
                );
            }
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('album_image');
    }
};


