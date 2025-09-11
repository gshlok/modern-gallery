<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">{{ $image->title }}</h2>
    </x-slot>

    <div class="py-6 max-w-5xl mx-auto sm:px-6 lg:px-8">
        <div class="bg-white p-6 rounded shadow">
            <img src="{{ asset('storage/images/' . $image->filename) }}" alt="{{ $image->title }}" class="w-full max-h-[600px] object-contain mx-auto"/>

            <div class="mt-4 space-y-2 text-gray-700">
                <p><strong>Uploaded by:</strong> {{ $image->user?->name ?? 'Unknown' }}</p>
                <p><strong>Album:</strong> {{ $image->album?->name ?? 'None' }}</p>
                <p><strong>Dimensions:</strong> {{ $image->width }} x {{ $image->height }}</p>
                <p><strong>Size:</strong> {{ number_format($image->size_bytes / 1024, 2) }} KB</p>
                <p><strong>MIME Type:</strong> {{ $image->mime_type }}</p>
                <p><strong>Uploaded on:</strong> {{ $image->created_at->format('M d, Y') }}</p>

                @if(!empty($image->exif_data))
                    <h3 class="mt-4 font-semibold">EXIF Data</h3>
                    <table class="table-auto w-full text-left text-sm text-gray-600">
                        <tbody>
                            @foreach($image->exif_data as $key => $value)
                                <tr>
                                    <td class="border px-2 py-1 font-mono">{{ $key }}</td>
                                    <td class="border px-2 py-1">{{ $value }}</td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                @endif

                @auth
                <div class="mt-6 flex flex-col sm:flex-row sm:space-x-4 space-y-2 sm:space-y-0">
                    <form method="POST" action="{{ route('gallery.rename', $image->uuid) }}" class="w-full sm:w-auto">
                        @csrf

                        <label for="new_title" class="block font-semibold mb-1">Rename Image</label>
                        <input id="new_title" type="text" name="new_title" value="{{ old('new_title', $image->title) }}" required class="border rounded p-1 w-full" />

                        <label for="album_id" class="block font-semibold mt-3 mb-1">Move To Album</label>
                        <select id="album_id" name="album_id" class="border rounded p-2 w-full">
                            <option value="">No Album</option>
                            @foreach(App\Models\Album::all() as $album)
                                <option value="{{ $album->id }}" {{ $album->id == $image->album_id ? 'selected' : '' }}>
                                    {{ $album->name }}
                                </option>
                            @endforeach
                        </select>

                        <button type="submit" class="bg-yellow-500 hover:bg-yellow-600 text-white px-3 py-1 rounded mt-3 w-full">
                            Update
                        </button>
                    </form>

                    <form method="POST" action="{{ route('gallery.destroy', $image->uuid) }}" onsubmit="return confirm('Are you sure you want to delete this image?');" class="mt-4 sm:mt-0 w-full sm:w-auto">
                        @csrf
                        @method('DELETE')
                        <button type="submit" class="bg-red-600 hover:bg-red-700 text-white px-3 py-1 rounded w-full">
                            Delete
                        </button>
                    </form>
                </div>
                @endauth

            </div>
        </div>
    </div>
</x-app-layout>
