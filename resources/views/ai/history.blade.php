<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            🎨 AI Generation History
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <div class="mb-6 flex justify-between items-center">
                <div>
                    <p class="text-gray-600">Your AI-generated images</p>
                </div>
                <a href="{{ route('ai.create') }}"
                   class="bg-gradient-to-r from-purple-500 to-pink-500 hover:from-purple-600 hover:to-pink-600 text-white px-4 py-2 rounded-lg">
                    ✨ Generate New Image
                </a>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
                @forelse($generations as $generation)
                    <div class="bg-white rounded-lg shadow-sm overflow-hidden">
                        @if($generation->image)
                            <div class="aspect-square overflow-hidden">
                                <img src="{{ $generation->image->thumbnail }}"
                                     alt="{{ $generation->prompt }}"
                                     class="w-full h-full object-cover hover:scale-105 transition-transform cursor-pointer"
                                     onclick="openLightbox('{{ $generation->image->url }}', '{{ $generation->image->title }}', '{{ $generation->prompt }}')">
                            </div>
                        @endif

                        <div class="p-4">
                            <div class="flex items-center justify-between mb-2">
                                <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium
                                    {{ $generation->status === 'completed' ? 'bg-green-100 text-green-800' : 
                                       ($generation->status === 'failed' ? 'bg-red-100 text-red-800' : 
                                        'bg-yellow-100 text-yellow-800') }}">
                                    {{ ucfirst($generation->status) }}
                                </span>
                                <span class="text-xs text-gray-500">
                                    {{ $generation->created_at->diffForHumans() }}
                                </span>
                            </div>

                            <p class="text-sm text-gray-700 line-clamp-3">
                                "{{ $generation->prompt }}"
                            </p>

                            @if($generation->parameters && isset($generation->parameters['style']))
                                <p class="text-xs text-gray-500 mt-2">
                                    Style: {{ ucfirst(str_replace('_', ' ', $generation->parameters['style'])) }}
                                </p>
                            @endif

                            @if($generation->status === 'failed')
                                <p class="text-xs text-red-600 mt-2">
                                    Error: {{ $generation->error_message }}
                                </p>
                            @endif

                            @if($generation->image)
                                <div class="mt-3 flex justify-between items-center">
                                    <a href="{{ route('gallery.show', $generation->image->uuid) }}"
                                       class="text-blue-600 hover:text-blue-800 text-sm">
                                        View in Gallery →
                                    </a>
                                    <span class="text-xs text-gray-400">
                                        👁️ {{ $generation->image->view_count }} views
                                    </span>
                                </div>
                            @endif
                        </div>
                    </div>
                @empty
                    <div class="col-span-full text-center py-8">
                        <div class="text-gray-400 text-6xl mb-4">🎨</div>
                        <h3 class="text-lg font-medium text-gray-900 mb-2">No AI generations yet</h3>
                        <p class="text-gray-500 mb-4">Start creating amazing AI artwork!</p>
                        <a href="{{ route('ai.create') }}"
                           class="bg-gradient-to-r from-purple-500 to-pink-500 hover:from-purple-600 hover:to-pink-600 text-white px-6 py-2 rounded-lg">
                            Generate Your First Image
                        </a>
                    </div>
                @endforelse
            </div>

            <!-- Pagination -->
            @if($generations->hasPages())
                <div class="mt-8">
                    {{ $generations->links() }}
                </div>
            @endif
        </div>
    </div>

    <!-- Lightbox (reuse from gallery) -->
    <div id="lightbox" class="fixed inset-0 bg-black bg-opacity-90 z-50 hidden items-center justify-center p-4" onclick="closeLightbox()">
        <div class="relative max-w-4xl max-h-screen">
            <img id="lightbox-image" src="" alt="" class="max-w-full max-h-full object-contain">
            <div id="lightbox-info" class="absolute bottom-0 left-0 right-0 bg-gradient-to-t from-black/70 p-4 text-white">
                <h2 id="lightbox-title" class="text-lg font-bold"></h2>
                <p id="lightbox-caption" class="text-sm opacity-75"></p>
            </div>
            <button onclick="closeLightbox()" class="absolute top-4 right-4 text-white hover:text-gray-300 text-2xl">×</button>
        </div>
    </div>

    <script>
        function openLightbox(imageUrl, title, prompt) {
            document.getElementById('lightbox-image').src = imageUrl;
            document.getElementById('lightbox-title').textContent = title || 'AI Generated';
            document.getElementById('lightbox-caption').textContent = 'Prompt: ' + (prompt || '');
            document.getElementById('lightbox').classList.remove('hidden');
            document.getElementById('lightbox').classList.add('flex');
            document.body.style.overflow = 'hidden';
        }

        function closeLightbox() {
            document.getElementById('lightbox').classList.add('hidden');
            document.getElementById('lightbox').classList.remove('flex');
            document.body.style.overflow = 'auto';
        }

        document.addEventListener('keydown', function(e) {
            if (e.key === 'Escape') closeLightbox();
        });
    </script>
</x-app-layout>
