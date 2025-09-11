<?php

namespace App\Http\Controllers;

use App\Models\Image;
use App\Models\Album;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use App\Services\ImageService;

class GalleryController extends Controller
{
    public function index(Request $request)
    {
        $query = Image::with(['user', 'album'])->latest();

        if ($search = $request->input('search')) {
            $query->where('title', 'like', "%{$search}%")
                  ->orWhere('caption', 'like', "%{$search}%");
        }

        if ($albumId = $request->input('album_id')) {
            $query->where('album_id', $albumId);
        }

        $images = $query->paginate(24);
        $albums = Album::withCount('images')->get();

        return view('gallery.index', compact('images', 'albums'));
    }

    public function show(string $uuid)
    {
        $image = Image::with(['user', 'album'])->where('uuid', $uuid)->firstOrFail();
        return view('gallery.show', compact('image'));
    }

    public function upload()
    {
        $albums = Album::all();
        return view('gallery.upload', compact('albums'));
    }

    public function store(Request $request, ImageService $imageService)
    {
        $request->validate([
            'images.*' => 'required|image|max:5120',
            'album_id' => 'nullable|exists:albums,id',
        ]);

        foreach ($request->file('images') as $file) {
            $imageService->uploadImage($file, $request->album_id);
        }

        return redirect()->route('gallery.index')->with('success', 'Images uploaded successfully.');
    }

    public function rename(Request $request, string $uuid)
    {
        $request->validate([
            'new_title' => 'nullable|string|max:255',
            'album_id' => 'nullable|exists:albums,id',
        ]);

        $image = Image::where('uuid', $uuid)->firstOrFail();

        $updated = false;

        if ($request->filled('new_title') && $request->new_title !== $image->title) {
            $image->title = $request->new_title;
            $updated = true;
        }

        if ($request->has('album_id') && $request->album_id != $image->album_id) {
            $image->album_id = $request->album_id;
            $updated = true;
        }

        if ($updated) {
            $image->save();
        }

        return redirect()->route('gallery.show', $uuid)->with('success', 'Image updated successfully.');
    }

    public function destroy(string $uuid)
    {
        $image = Image::where('uuid', $uuid)->firstOrFail();

        // Delete image and thumbnail files
        Storage::disk('public')->delete('images/' . $image->filename);
        Storage::disk('public')->delete('thumbnails/' . $image->filename);

        $image->delete();

        return redirect()->route('gallery.index')->with('success', 'Image deleted successfully.');
    }
}
