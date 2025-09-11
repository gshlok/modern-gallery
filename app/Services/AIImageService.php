<?php

namespace App\Services;

use App\Models\AIGeneration;
use App\Models\Image;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Intervention\Image\Facades\Image as InterventionImage;

class AIImageService
{
    public function generateImage(string $prompt, array $options = []): AIGeneration
    {
        $generation = AIGeneration::create([
            'user_id' => auth()->id(),
            'provider' => 'huggingface',
            'model_name' => 'stabilityai/stable-diffusion-2-1',
            'prompt' => $prompt,
            'parameters' => $options,
            'status' => 'processing'
        ]);

        try {
            $image = $this->createPlaceholderImage($prompt);

            $filename = 'ai_' . Str::uuid() . '.jpg';
            $path = "images/{$filename}";

            Storage::put('public/' . $path, $image);

            $intervention = InterventionImage::make(storage_path("app/public/{$path}"));
            $thumbnail = clone $intervention;
            $thumbnail->fit(300, 300);
            $thumbnail->save(storage_path("app/public/thumbnails/{$filename}"));

            $imageRecord = Image::create([
                'filename' => $filename,
                'title' => 'AI Generated: ' . Str::limit($prompt, 50),
                'caption' => "Generated from prompt: {$prompt}",
                'mime_type' => 'image/jpeg',
                'width' => $intervention->width(),
                'height' => $intervention->height(),
                'size_bytes' => Storage::size('public/' . $path),
                'user_id' => auth()->id(),
                'privacy_level' => 'public'
            ]);

            $generation->update([
                'image_id' => $imageRecord->id,
                'status' => 'completed'
            ]);
        } catch (\Exception $e) {
            $generation->update([
                'status' => 'failed',
                'error_message' => $e->getMessage()
            ]);
        }

        return $generation->refresh();
    }

    private function createPlaceholderImage(string $prompt): string
    {
        $image = InterventionImage::canvas(512, 512, '#667eea');
        $image->fill('#f093fb');

        $words = explode(' ', $prompt);
        $lines = array_chunk($words, 3);
        $y = 200;
        foreach (array_slice($lines, 0, 5) as $line) {
            $text = implode(' ', $line);
            $image->text($text, 256, $y, function($font) {
                $font->file(public_path('fonts/arial.ttf') ?: null);
                $font->size(24);
                $font->color('#ffffff');
                $font->align('center');
                $font->valign('middle');
            });
            $y += 40;
        }
        $image->text('AI Generated Demo', 256, 450, function($font) {
            $font->size(16);
            $font->color('#ffffff');
            $font->align('center');
        });

        return $image->encode('jpg', 80);
    }
}
