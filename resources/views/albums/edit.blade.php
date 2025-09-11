<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">Edit Album</h2>
    </x-slot>

    <div class="py-6 max-w-lg mx-auto sm:px-6 lg:px-8">
        @if(session('success'))
            <div class="bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded mb-4">
                {{ session('success') }}
            </div>
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

        <form action="{{ route('albums.update', $album) }}" method="POST" class="space-y-4">
            @csrf
            @method('PUT')

            <label for="name" class="block text-gray-700 font-semibold">Album Name</label>
            <input type="text" name="name" id="name" value="{{ old('name', $album->name) }}" required
                   class="border rounded w-full p-2 @error('name') border-red-600 @enderror" />

            <label for="description" class="block text-gray-700 font-semibold">Description (optional)</label>
            <textarea name="description" id="description" rows="4" class="border rounded w-full p-2">{{ old('description', $album->description) }}</textarea>

            <div class="flex items-center space-x-2">
                <a href="{{ route('albums.index') }}" class="px-4 py-2 rounded border border-gray-300">Cancel</a>
                <button type="submit" class="bg-blue-600 text-white px-4 py-2 rounded border border-blue-700">Save Changes</button>
            </div>
        </form>
    </div>
</x-app-layout>


