<?php

namespace App\Http\Controllers;

use App\Models\Album;
use Illuminate\Http\Request;

class AlbumController extends Controller
{
    public function create()
    {
        return view('albums.create');
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
}
