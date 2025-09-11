<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            Dashboard
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg p-6">
                <h3 class="text-lg font-bold mb-4">Gallery Statistics</h3>

                <ul class="space-y-2 text-gray-700">
                    <li><strong>Total Images:</strong> {{ $stats['total_images'] ?? 0 }}</li>
                    <li><strong>Total Albums:</strong> {{ $stats['total_albums'] ?? 0 }}</li>
                </ul>

                <h3 class="text-lg font-bold mt-8 mb-4">Recent Images</h3>
                <div class="grid grid-cols-2 md:grid-cols-4 gap-4">
                    @foreach($stats['recent_images'] ?? [] as $image)
                        <a href="{{ route('gallery.show', $image->uuid) }}" class="block rounded overflow-hidden border hover:shadow">
                            <img src="{{ $image->thumbnail }}" alt="{{ $image->title }}" class="w-full h-32 object-cover">
                            <p class="text-center text-sm truncate mt-1 px-2">{{ $image->title }}</p>
                        </a>
                    @endforeach
                </div>
            </div>
        </div>
    </div>
</x-app-layout>
