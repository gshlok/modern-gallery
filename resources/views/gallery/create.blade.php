<x-app-layout>
    <x-slot name="header">
        <h2>Create New Album</h2>
    </x-slot>

    <div class="max-w-md mx-auto p-4 bg-white rounded shadow">
        <form method="POST" action="{{ route('albums.store') }}">
            @csrf
            <label class="block mb-1" for="name">Album Name</label>
            <input id="name" name="name" type="text" required class="w-full border p-2 rounded" />
            @error('name') <p class="text-red-600">{{ $message }}</p> @enderror

            <button type="submit" class="mt-4 bg-blue-600 text-white px-4 py-2 rounded">Create Album</button>
        </form>
    </div>
</x-app-layout>
