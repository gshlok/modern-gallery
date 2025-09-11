<?php

namespace App\Services;

use App\Models\Image as ImageModel;
use Illuminate\Http\UploadedFile;
use Intervention\Image\Facades\Image as InterventionImage;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class ImageService
{
    public function uploadImage(UploadedFile $file, array $albumIds = []): ImageModel
    {
        $filename = Str::uuid() . '.' . $file->getClientOriginalExtension();

        $file->storeAs('images', $filename, 'public');

        $image = InterventionImage::make($file);

        $thumbnail = clone $image;
        $thumbnail->resize(600, null, function ($constraint) {
            $constraint->aspectRatio();
            $constraint->upsize();
        });
        $thumbnail->save(storage_path("app/public/thumbnails/{$filename}"));

        $exif = [];
        try {
            $exif = @exif_read_data($file->path()) ?: [];
            $exif = array_filter($exif, 'is_scalar');
        } catch (\Exception) {
            //
        }

        $imageModel = ImageModel::create([
            'filename' => $filename,
            'title' => pathinfo($file->getClientOriginalName(), PATHINFO_FILENAME),
            'mime_type' => $file->getMimeType(),
            'width' => $image->width(),
            'height' => $image->height(),
            'size_bytes' => $file->getSize(),
            'exif_data' => $exif,
            'user_id' => auth()->id(),
            // legacy album_id unused for new many-to-many
        ]);

        if (!empty($albumIds)) {
            $imageModel->albums()->syncWithoutDetaching($albumIds);
        }

        return $imageModel;
    }
}
