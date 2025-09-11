<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            🎨 AI Image Generator
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-2xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6">
                    @if(session('success'))
                        <div class="bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded mb-4">
                            {{ session('success') }}
                        </div>
                    @endif

                    @if(session('error'))
                        <div class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded mb-4">
                            {{ session('error') }}
                        </div>
                    @endif

                    <form method="POST" action="{{ route('ai.generate') }}">
                        @csrf

                        <div class="mb-6">
                            <label for="prompt" class="block text-sm font-medium text-gray-700 mb-2">
                                Describe your image
                            </label>
                            <textarea name="prompt"
                                      id="prompt"
                                      rows="4"
                                      class="w-full rounded-md border-gray-300 focus:border-blue-500 focus:ring-blue-500"
                                      placeholder="A majestic mountain landscape with snow-capped peaks and a crystal clear lake..."
                                      required>{{ old('prompt') }}</textarea>
                            @error('prompt')
                                <p class="text-red-600 text-sm mt-1">{{ $message }}</p>
                            @enderror
                            <p class="text-sm text-gray-500 mt-1">
                                Be descriptive! The more detail, the better the result.
                            </p>
                        </div>

                        <div class="mb-6">
                            <label for="style" class="block text-sm font-medium text-gray-700 mb-2">
                                Style (Optional)
                            </label>
                            <select name="style" id="style" class="w-full rounded-md border-gray-300">
                                <option value="">Default</option>
                                <option value="photorealistic">Photorealistic</option>
                                <option value="digital_art">Digital Art</option>
                                <option value="oil_painting">Oil Painting</option>
                                <option value="watercolor">Watercolor</option>
                                <option value="anime">Anime Style</option>
                                <option value="cyberpunk">Cyberpunk</option>
                                <option value="fantasy">Fantasy Art</option>
                            </select>
                        </div>

                        <div class="mb-6">
                            <label for="size" class="block text-sm font-medium text-gray-700 mb-2">
                                Image Size
                            </label>
                            <select name="size" id="size" class="w-full rounded-md border-gray-300">
                                <option value="512x512">Square (512x512) - Fastest</option>
                                <option value="768x768">Large Square (768x768)</option>
                                <option value="1024x1024">HD Square (1024x1024) - Slowest</option>
                            </select>
                        </div>

                        <div class="mb-6 p-4 bg-gray-50 rounded-lg">
                            <h4 class="font-medium text-gray-800 mb-2">💡 Example Prompts:</h4>
                            <div class="space-y-1 text-sm text-gray-600">
                                <button type="button" class="block text-left hover:text-blue-600" onclick="setPrompt('A serene Japanese garden with cherry blossoms, koi pond, and traditional stone bridge')">
                                    → A serene Japanese garden with cherry blossoms, koi pond, and traditional stone bridge
                                </button>
                                <button type="button" class="block text-left hover:text-blue-600" onclick="setPrompt('Futuristic cityscape at night with neon lights and flying cars')">
                                    → Futuristic cityscape at night with neon lights and flying cars
                                </button>
                                <button type="button" class="block text-left hover:text-blue-600" onclick="setPrompt('A wise old wizard reading a magical book in his tower library')">
                                    → A wise old wizard reading a magical book in his tower library
                                </button>
                                <button type="button" class="block text-left hover:text-blue-600" onclick="setPrompt('Abstract geometric pattern with vibrant colors and flowing shapes')">
                                    → Abstract geometric pattern with vibrant colors and flowing shapes
                                </button>
                            </div>
                        </div>

                        <div class="flex items-center justify-between">
                            <div>
                                <a href="{{ route('gallery.index') }}" class="text-gray-600 hover:text-gray-800">
                                    ← Back to Gallery
                                </a>
                                @auth
                                <a href="{{ route('ai.history') }}" class="ml-4 text-blue-600 hover:text-blue-800">
                                    View History
                                </a>
                                @endauth
                            </div>

                            <button type="submit"
                                    class="bg-gradient-to-r from-purple-500 to-pink-500 hover:from-purple-600 hover:to-pink-600 text-white font-bold py-3 px-6 rounded-lg">
                                ✨ Generate Image
                            </button>
                        </div>
                    </form>
                </div>
            </div>

            <div class="mt-6 bg-blue-50 border border-blue-200 rounded-lg p-4">
                <h4 class="font-medium text-blue-800 mb-2">🤖 How it works:</h4>
                <ul class="text-sm text-blue-700 space-y-1">
                    <li>• Enter a detailed description of what you want to create</li>
                    <li>• Choose a style and size for your image</li>
                    <li>• Click generate and watch AI create your artwork!</li>
                    <li>• Generated images are automatically added to your gallery</li>
                </ul>
            </div>
        </div>
    </div>

    <script>
        function setPrompt(prompt) {
            document.getElementById('prompt').value = prompt;
        }
    </script>
</x-app-layout>
