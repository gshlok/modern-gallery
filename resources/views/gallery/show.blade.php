<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">{{ $image->title }}</h2>
    </x-slot>

    <div class="py-6 max-w-5xl mx-auto sm:px-6 lg:px-8">
        <div class="bg-white p-6 rounded shadow">
            <img src="{{ asset('storage/images/' . $image->filename) }}" alt="{{ $image->title }}" class="w-full max-h-[600px] object-contain mx-auto"/>

            <div class="mt-4 space-y-2 text-gray-700">
                <p><strong>Uploaded by:</strong> {{ $image->user?->name ?? 'Unknown' }}</p>
                <p><strong>Albums:</strong>
                    @if($image->albums->count())
                        @foreach($image->albums as $al)
                            <span class="inline-block text-xs bg-gray-100 text-gray-700 px-2 py-0.5 rounded mr-1">{{ $al->name }}</span>
                        @endforeach
                    @else
                        <span class="text-gray-500">None</span>
                    @endif
                </p>
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
                    <div class="w-full sm:w-auto bg-gray-50 p-3 rounded border">
                        <div class="font-semibold mb-2">Album Actions</div>
                        @foreach($image->albums as $al)
                            <form method="POST" action="{{ route('albums.images.remove', [$al, $image->uuid]) }}" class="mb-2">
                                @csrf
                                @method('DELETE')
                                <button type="submit" class="bg-purple-600 hover:bg-purple-700 text-white px-3 py-1 rounded w-full border border-purple-700">Remove from "{{ $al->name }}"</button>
                            </form>
                        @endforeach
                        <form method="POST" action="" onsubmit="return ensureAlbumSelectedAdd('{{ $image->uuid }}');">
                            @csrf
                            <label for="album_select" class="block text-sm mb-1">Add to album</label>
                            <select id="album_select" name="album_id" class="border rounded p-2 w-full">
                                <option value="">Select Album</option>
                                @foreach(App\Models\Album::where('user_id', auth()->id())->orderBy('name')->get() as $album)
                                    <option value="{{ $album->id }}">{{ $album->name }}</option>
                                @endforeach
                            </select>
                            <button type="submit" class="mt-2 bg-green-600 hover:bg-green-700 text-white px-3 py-1 rounded w-full border border-green-700">Add</button>
                        </form>
                        <div class="mt-3 text-sm">
                            <a class="text-blue-600 underline" href="{{ route('albums.index') }}">Manage Albums</a>
                        </div>
                    </div>
                    <form method="POST" action="{{ route('gallery.rename', $image->uuid) }}" class="w-full sm:w-auto">
                        @csrf

                        <label for="new_title" class="block font-semibold mb-1">Rename Image</label>
                        <input id="new_title" type="text" name="new_title" value="{{ old('new_title', $image->title) }}" required class="border rounded p-1 w-full" />

                        <div class="mt-3">
                            <div class="block font-semibold mb-1">Albums</div>
                            <div class="grid grid-cols-2 gap-2">
                                @foreach(App\Models\Album::where('user_id', auth()->id())->orderBy('name')->get() as $album)
                                    <label class="flex items-center gap-2 border rounded p-2">
                                        <input type="checkbox" name="album_ids[]" value="{{ $album->id }}" {{ $image->albums->contains('id', $album->id) ? 'checked' : '' }} />
                                        <span>{{ $album->name }}</span>
                                    </label>
                                @endforeach
                            </div>
                        </div>

                        <button type="submit" class="bg-yellow-500 hover:bg-yellow-600 text-white px-3 py-1 rounded mt-3 w-full border border-yellow-700">
                            Update
                        </button>
                    </form>

                    <form method="POST" action="{{ route('gallery.destroy', $image->uuid) }}" onsubmit="return confirm('Are you sure you want to delete this image?');" class="mt-4 sm:mt-0 w-full sm:w-auto">
                        @csrf
                        @method('DELETE')
                        <button type="submit" class="bg-red-600 hover:bg-red-700 text-white px-3 py-1 rounded w-full border border-red-700">
                            Delete
                        </button>
                    </form>
                </div>
                @endauth

            </div>
        </div>
    </div>
    <script>
    function ensureAlbumSelectedAdd(imageUuid) {
        const select = document.getElementById('album_select');
        if (!select.value) {
            alert('Please select an album first.');
            return false;
        }
        const form = select.closest('form');
        const base = '{{ url('/albums') }}';
        form.setAttribute('action', base + '/' + select.value + '/images/' + imageUuid);
        return true;
    }
    </script>
</x-app-layout>
