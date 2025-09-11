<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">My Albums</h2>
    </x-slot>

    <div class="py-6 max-w-4xl mx-auto sm:px-6 lg:px-8">
        @if(session('success'))
            <div class="bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded mb-4">{{ session('success') }}</div>
        @endif
        @if($errors->any())
            <div class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded mb-4">
                <ul class="list-disc ml-5">
                    @foreach($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        @endif
        <div class="bg-white rounded shadow p-4 mb-6">
            <form action="{{ route('albums.store') }}" method="POST" class="flex flex-col sm:flex-row gap-2 items-start">
                @csrf
                <div class="flex-1 w-full">
                    <label for="name" class="sr-only">Album Name</label>
                    <input id="name" name="name" type="text" class="border rounded p-2 w-full" placeholder="New album name" required />
                </div>
                <button type="submit" class="bg-blue-600 text-white px-4 py-2 rounded border border-blue-700">Create Album</button>
            </form>
        </div>

        <div class="bg-white rounded shadow divide-y">
            @forelse($albums as $album)
                <div class="p-4 flex items-center justify-between">
                    <div>
                        <div class="font-semibold"><a class="underline" href="{{ route('albums.show', $album) }}">{{ $album->name }}</a></div>
                        <div class="text-sm text-gray-500">{{ $album->images_count }} image(s)</div>
                    </div>
                    <div class="flex items-center space-x-2">
                        <a href="{{ route('albums.edit', $album) }}" class="bg-yellow-500 text-white px-3 py-1 rounded border border-yellow-700">Edit</a>
                        <form method="POST" action="{{ route('albums.destroy', $album) }}" class="flex items-center space-x-2"
                              onsubmit="return confirmDeleteAlbum('{{ $album->name }}');">
                            @csrf
                            @method('DELETE')
                            <input type="hidden" name="confirm_name" id="confirm_name_{{ $album->id }}">
                            <button type="submit" class="bg-red-600 text-white px-3 py-1 rounded border border-red-700">Delete</button>
                        </form>
                    </div>
                </div>
            @empty
                <div class="p-6 text-gray-600">You have no albums yet.</div>
            @endforelse
        </div>
    </div>

    <script>
    function confirmDeleteAlbum(albumName) {
        const input = prompt('Type the album name to confirm deletion: ' + albumName);
        if (input === null) return false;
        if (input !== albumName) {
            alert('Album name does not match. Deletion cancelled.');
            return false;
        }
        // set the hidden value on the form that triggered the submit
        const active = document.activeElement;
        const form = active && active.form ? active.form : null;
        if (form) {
            const hidden = form.querySelector('input[name="confirm_name"]');
            if (hidden) hidden.value = input;
        }
        return true;
    }
    </script>
</x-app-layout>


