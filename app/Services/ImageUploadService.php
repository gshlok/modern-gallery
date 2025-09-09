<?php

namespace App\Services;

use App\Models\Image;
use App\Models\User;
use App\Jobs\GenerateThumbnailsJob;
use App\Jobs\ExtractExifDataJob;
use App\Jobs\GenerateEmbeddingJob;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Intervention\Image\Facades\Image as InterventionImage;

/**
 * Image Upload Service
 * 
 * Handles image uploads, processing, and metadata extraction.
 */
class ImageUploadService
{
    public function uploadImage(UploadedFile $file, array $data, User $user): Image
    {
        // Validate file
        $this->validateFile($file);

        // Generate unique filename
        $filename = $this->generateFilename($file);
        $path = 'images/' . date('Y/m/') . $filename;

        // Store the file
        $disk = config('media.disk', 'local');
        Storage::disk($disk)->put($path, file_get_contents($file->getRealPath()));

        // Get image dimensions
        $imageInfo = getimagesize($file->getRealPath());
        $width = $imageInfo[0];
        $height = $imageInfo[1];

        // Calculate file hash for deduplication
        $hash = hash_file('sha256', $file->getRealPath());

        // Check for duplicates
        $existingImage = Image::where('hash', $hash)->first();
        if ($existingImage && $existingImage->user_id === $user->id) {
            // Delete the uploaded file since it's a duplicate
            Storage::disk($disk)->delete($path);
            throw new \Exception('This image has already been uploaded.');
        }

        // Create image record
        $image = Image::create([
            'user_id' => $user->id,
            'album_id' => $data['album_id'] ?? null,
            'title' => $data['title'] ?? $this->generateTitle($file),
            'description' => $data['description'] ?? null,
            'alt_text' => $data['alt_text'] ?? $data['title'] ?? $this->generateTitle($file),
            'filename' => $file->getClientOriginalName(),
            'path' => $path,
            'disk' => $disk,
            'mime_type' => $file->getMimeType(),
            'file_size' => $file->getSize(),
            'hash' => $hash,
            'width' => $width,
            'height' => $height,
            'visibility' => $data['visibility'] ?? 'public',
            'license' => $data['license'] ?? null,
            'allow_download' => $data['allow_download'] ?? true,
        ]);

        // Dispatch background jobs for processing
        GenerateThumbnailsJob::dispatch($image);
        ExtractExifDataJob::dispatch($image);

        if (config('vector_search.enabled', false)) {
            GenerateEmbeddingJob::dispatch($image);
        }

        return $image;
    }

    public function uploadMultipleImages(array $files, array $commonData, User $user): array
    {
        $uploadedImages = [];
        $errors = [];

        foreach ($files as $index => $file) {
            try {
                $data = array_merge($commonData, [
                    'title' => $commonData['title'] ?? $this->generateTitle($file),
                ]);

                $uploadedImages[] = $this->uploadImage($file, $data, $user);
            } catch (\Exception $e) {
                $errors[$index] = $e->getMessage();
            }
        }

        return [
            'images' => $uploadedImages,
            'errors' => $errors,
        ];
    }

    private function validateFile(UploadedFile $file): void
    {
        $maxSize = config('media.max_size', 10240) * 1024; // Convert KB to bytes
        $allowedMimes = config('media.allowed_mimes', ['jpeg', 'jpg', 'png', 'gif', 'webp']);

        if ($file->getSize() > $maxSize) {
            throw new \Exception('File size exceeds the maximum allowed size.');
        }

        $extension = strtolower($file->getClientOriginalExtension());
        if (!in_array($extension, $allowedMimes)) {
            throw new \Exception('File type not allowed. Allowed types: ' . implode(', ', $allowedMimes));
        }

        // Validate that it's actually an image
        if (!getimagesize($file->getRealPath())) {
            throw new \Exception('Invalid image file.');
        }
    }

    private function generateFilename(UploadedFile $file): string
    {
        $extension = $file->getClientOriginalExtension();
        return Str::uuid() . '.' . $extension;
    }

    private function generateTitle(UploadedFile $file): string
    {
        $filename = pathinfo($file->getClientOriginalName(), PATHINFO_FILENAME);
        return Str::title(str_replace(['_', '-'], ' ', $filename));
    }
}
