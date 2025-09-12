<?php

namespace App\Services;

use App\Models\AIGeneration;
use App\Models\Image;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Intervention\Image\Facades\Image as InterventionImage;

class AIImageService
{
    /**
     * Process the AI image generation, called by the background job.
     */
    public function processGeneration(AIGeneration $generation): void
    {
        $apiKey = config('services.gemini.key');
        if (!$apiKey) {
            throw new \Exception('Gemini API key is not configured.');
        }

        // === REAL API CALL (currently using a placeholder for testing) ===
        // To use the real API, comment out the placeholder line and uncomment/adapt the API call block.
        /*
        $response = Http::withToken($apiKey)
            ->post('https://generativelanguage.googleapis.com/v1beta/models/gemini-pro:generateContent', [
                // Adapt this payload to your specific AI image generation model's requirements
                'contents' => [
                    ['parts' => [['text' => $generation->prompt]]]
                ]
            ]);

        $response->throw(); // Throw an exception if the request failed
        $base64Image = $response->json('...'); // Parse the correct key for the base64 image data
        */

        // For this example to be testable, we use a placeholder base64 string (a small purple square).
        $base64Image = 'iVBORw0KGgoAAAANSUhEUgAAAAEAAAABCAYAAAAfFcSJAAAADUlEQVR42mP8/5+hHgAHggJ/PchI7wAAAABJRU5ErkJggg==';

        if (empty($base64Image)) {
            throw new \Exception('API did not return a valid image.');
        }

        $imageData = base64_decode($base64Image);
        $filename = 'ai_' . Str::uuid() . '.png';
        $path = "images/{$filename}";
        $thumbnailPath = "thumbnails/{$filename}";

        // Store the main image and thumbnail
        Storage::disk('public')->put($path, $imageData);
        $thumbnail = InterventionImage::make($imageData)->fit(300, 300)->encode('png', 80);
        Storage::disk('public')->put($thumbnailPath, (string) $thumbnail);

        // Create the image record in the database
        $imageRecord = Image::create([
            'filename' => $filename,
            'title' => 'AI: ' . Str::limit($generation->prompt, 100),
            'caption' => "Generated from prompt: {$generation->prompt}",
            'mime_type' => 'image/png',
            'width' => 512, // Or get from actual image data
            'height' => 512, // Or get from actual image data
            'size_bytes' => Storage::disk('public')->size($path),
            'user_id' => $generation->user_id,
            'privacy_level' => 'public'
        ]);

        // Link the image to the generation record and mark as completed
        $generation->update([
            'image_id' => $imageRecord->id,
            'status' => 'completed',
            'error_message' => null,
        ]);
    }
}