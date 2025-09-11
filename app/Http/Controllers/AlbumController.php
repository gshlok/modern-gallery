<?php

namespace App\Http\Controllers;

use App\Models\Album;
use App\Models\Image;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;

class AlbumController extends Controller
{
    public function index()
    {
        $albums = Album::where('user_id', auth()->id())->withCount('images')->orderBy('name')->get();
        return view('albums.manage', compact('albums'));
    }
    public function create()
    {
        return view('albums.create');
    }

    public function show(Request $request, Album $album)
    {
        $this->authorize('manage', $album);

        $album->load(['images' => function ($q) {
            $q->latest();
        }]);

        $search = $request->input('search');
        $availableImages = Image::where('user_id', auth()->id())
            ->whereDoesntHave('albums', function ($q) use ($album) { $q->where('albums.id', $album->id); })
            ->when($search, function ($q) use ($search) {
                $q->where('title', 'like', "%{$search}%");
            })
            ->latest()
            ->paginate(12)
            ->withQueryString();

        return view('albums.show', compact('album', 'availableImages', 'search'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255|unique:albums,name',
        ]);

        Album::create([
            'name' => $request->name,
            'user_id' => auth()->id(),
        ]);

        return redirect()->route('gallery.index')->with('success', 'Album created successfully.');
    }

    public function edit(Album $album)
    {
        $this->authorize('manage', $album);
        return view('albums.edit', compact('album'));
    }

    public function update(Request $request, Album $album)
    {
        $this->authorize('manage', $album);

        $request->validate([
            'name' => 'required|string|max:255|unique:albums,name,' . $album->id,
            'description' => 'nullable|string',
        ]);

        $album->name = $request->name;
        if ($request->has('description')) {
            $album->description = $request->description;
        }
        $album->save();

        return redirect()->route('albums.index')->with('success', 'Album updated successfully.');
    }

    public function destroy(Request $request, Album $album)
    {
        $this->authorize('delete', $album);

        $request->validate([
            'confirm_name' => 'required|string',
        ]);

        if ($request->input('confirm_name') !== $album->name) {
            return back()->withErrors(['confirm_name' => 'Album name does not match.'])->withInput();
        }

        DB::transaction(function () use ($album) {
            $images = $album->images()->get();
            foreach ($images as $image) {
                // detach from this album
                $album->images()->detach($image->id);
                // if image is not in any other album, optionally delete files and record
                if (! $image->albums()->exists()) {
                    Storage::disk('public')->delete('images/' . $image->filename);
                    Storage::disk('public')->delete('thumbnails/' . $image->filename);
                    $image->delete();
                }
            }
            $album->delete();
        });

        return redirect()->route('gallery.index')->with('success', 'Album deleted. Images retained if in other albums.');
    }

    public function addImage(Request $request, Album $album, string $uuid)
    {
        $this->authorize('manage', $album);
        $image = Image::where('uuid', $uuid)->firstOrFail();
        $this->authorize('manage', $image);

        $image->albums()->syncWithoutDetaching([$album->id]);

        return back()->with('success', 'Image added to album.');
    }

    public function removeImage(Request $request, Album $album, string $uuid)
    {
        $this->authorize('manage', $album);
        $image = Image::where('uuid', $uuid)->firstOrFail();
        $this->authorize('manage', $image);

        if (! $image->albums()->where('albums.id', $album->id)->exists()) {
            return back()->with('error', 'Image is not in this album.');
        }
        $image->albums()->detach($album->id);

        return back()->with('success', 'Image removed from album.');
    }
}
