<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">Album: {{ $album->name }}</h2>
    </x-slot>

    <div class="py-6 max-w-7xl mx-auto sm:px-6 lg:px-8 space-y-6">
        @if(session('success'))
            <div class="bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded">{{ session('success') }}</div>
        @endif
        @if(session('error'))
            <div class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded">{{ session('error') }}</div>
        @endif

        <div class="bg-white p-4 rounded shadow">
            <div class="flex items-center justify-between mb-3">
                <div>
                    <div class="text-lg font-semibold">Images in this album</div>
                    <div class="text-sm text-gray-500">{{ $album->images->count() }} image(s)</div>
                </div>
                <a href="{{ route('albums.edit', $album) }}" class="bg-yellow-500 text-white px-3 py-1 rounded">Edit Album</a>
            </div>
            @if($album->images->count())
            <div class="grid grid-cols-2 md:grid-cols-4 gap-4">
                @foreach($album->images as $img)
                <div class="rounded shadow overflow-hidden bg-gray-50">
                    <a href="{{ route('gallery.show', $img->uuid) }}">
                        <img src="{{ asset('storage/thumbnails/' . $img->filename) }}" alt="{{ $img->title }}" class="w-full h-40 object-contain bg-white"/>
                    </a>
                    <div class="p-2 text-sm">
                        <div class="font-semibold truncate">{{ $img->title }}</div>
                        <form method="POST" action="{{ route('albums.images.remove', [$album, $img->uuid]) }}" class="mt-2">
                            @csrf
                            @method('DELETE')
                            <button type="submit" class="bg-purple-600 text-white px-3 py-1 rounded w-full">Remove</button>
                        </form>
                    </div>
                </div>
                @endforeach
            </div>
            @else
                <p class="text-gray-600">No images in this album yet.</p>
            @endif
        </div>

        <div class="bg-white p-4 rounded shadow">
            <div class="flex items-center justify-between mb-3">
                <div class="text-lg font-semibold">Add existing images</div>
                <form method="GET" action="{{ route('albums.show', $album) }}" class="flex gap-2">
                    <input type="text" name="search" value="{{ $search }}" placeholder="Search your images" class="border rounded p-2"/>
                    <button class="bg-blue-600 text-white px-3 py-1 rounded">Search</button>
                </form>
            </div>
            @if($availableImages->count())
            <div class="grid grid-cols-2 md:grid-cols-4 gap-4">
                @foreach($availableImages as $img)
                <div class="rounded shadow overflow-hidden bg-gray-50">
                    <a href="{{ route('gallery.show', $img->uuid) }}">
                        <img src="{{ asset('storage/thumbnails/' . $img->filename) }}" alt="{{ $img->title }}" class="w-full h-40 object-contain bg-white"/>
                    </a>
                    <div class="p-2 text-sm">
                        <div class="font-semibold truncate">{{ $img->title }}</div>
                        <form method="POST" action="{{ route('albums.images.add', [$album, $img->uuid]) }}" class="mt-2">
                            @csrf
                            <button type="submit" class="bg-green-600 text-white px-3 py-1 rounded w-full">Add to album</button>
                        </form>
                    </div>
                </div>
                @endforeach
            </div>
            <div class="mt-4">{{ $availableImages->links() }}</div>
            @else
                <p class="text-gray-600">No images found.</p>
            @endif
        </div>
    </div>
</x-app-layout>


