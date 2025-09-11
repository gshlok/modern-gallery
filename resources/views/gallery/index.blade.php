<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">Gallery</h2>
    </x-slot>

    <div class="py-6 max-w-7xl mx-auto sm:px-6 lg:px-8">
        <form method="GET" action="{{ route('gallery.index') }}" class="mb-4 flex space-x-4">
            <input type="text" name="search" placeholder="Search images..." value="{{ request('search') }}" class="border rounded p-2 flex-grow"/>
            <select name="album_id" class="border rounded p-2">
                <option value="">All Albums</option>
                @foreach ($albums as $album)
                    <option value="{{ $album->id }}" {{ request('album_id') == $album->id ? 'selected' : '' }}>
                        {{ $album->name }} ({{ $album->images_count }})
                    </option>
                @endforeach
            </select>
            <button type="submit" class="bg-blue-600 text-white px-4 rounded">Filter</button>
        </form>

        @if($images->count())
        <div class="grid grid-cols-2 md:grid-cols-4 gap-4">
            @foreach ($images as $galleryImage)
            <a href="{{ route('gallery.show', $galleryImage->uuid) }}" class="block rounded shadow hover:shadow-lg overflow-hidden">
                <img src="{{ asset('storage/thumbnails/' . $galleryImage->filename) }}" alt="{{ $galleryImage->title }}" class="w-full h-48 object-contain"/>
                <div class="p-2 bg-white">
                    <h3 class="font-semibold text-sm truncate">{{ $galleryImage->title }}</h3>
                    <p class="text-xs text-gray-500 truncate">Uploaded by {{ $galleryImage->user?->name ?? 'Unknown' }}</p>
                    <div class="mt-1 flex flex-wrap gap-1">
                        @forelse($galleryImage->albums as $al)
                            <span class="text-[10px] bg-gray-100 text-gray-700 px-2 py-0.5 rounded">{{ $al->name }}</span>
                        @empty
                            <span class="text-[10px] text-gray-400">No Albums</span>
                        @endforelse
                    </div>
                </div>
            </a>
            @endforeach
        </div>

        <div class="mt-6">
            {{ $images->withQueryString()->links() }}
        </div>
        @else
            <p>No images found.</p>
        @endif
    </div>
</x-app-layout>
