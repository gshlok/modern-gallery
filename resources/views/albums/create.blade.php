<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">Create New Album</h2>
    </x-slot>

    <div class="py-6 max-w-lg mx-auto sm:px-6 lg:px-8">
        @if(session('success'))
            <div class="bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded mb-4">
                {{ session('success') }}
            </div>
        @endif

        <form action="{{ route('albums.store') }}" method="POST" class="space-y-4">
            @csrf

            <label for="name" class="block text-gray-700 font-semibold">Album Name</label>
            <input type="text" name="name" id="name" value="{{ old('name') }}" required
                   class="border rounded w-full p-2 @error('name') border-red-600 @enderror" />

            @error('name')
                <p class="text-red-600 text-sm mt-1">{{ $message }}</p>
            @enderror

            <button type="submit" class="bg-blue-600 text-white px-4 py-2 rounded">Create Album</button>
        </form>
    </div>
</x-app-layout>
