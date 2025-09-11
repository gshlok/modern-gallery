<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">Upload Images</h2>
    </x-slot>

    <div class="py-6 max-w-3xl mx-auto sm:px-6 lg:px-8">
        @if(session('success'))
            <div class="bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded mb-4" role="alert">{{ session('success') }}</div>
        @endif
        @if($errors->any())
            <div class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded mb-4" role="alert">
                <ul class="list-disc ml-5">
                    @foreach($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        @endif

        <form method="POST" action="{{ route('gallery.store') }}" enctype="multipart/form-data" class="space-y-4" id="uploadForm">
            @csrf

            <label for="images" class="block font-semibold mb-1">Select Images (or drag and drop here)</label>
            <div id="drop-area" class="border-dashed border-4 border-gray-300 rounded p-6 text-center cursor-pointer">
                <input id="images" name="images[]" type="file" multiple accept="image/*" required class="hidden" />
                <p id="drop-message">Drag & drop images here or click to select files</p>
            </div>

            <div id="preview-container" class="mt-4 grid grid-cols-4 gap-4"></div>
            <p id="file-list" class="mt-2 text-gray-600"></p>

            @error('images.*')
            <p class="text-red-600 text-sm mt-1">{{ $message }}</p>
            @enderror

            <label for="album_ids" class="block font-semibold mt-4">Add to Albums (optional)</label>
            <div class="grid grid-cols-2 gap-2">
                @foreach($albums as $album)
                    <label class="flex items-center gap-2 border rounded p-2">
                        <input type="checkbox" name="album_ids[]" value="{{ $album->id }}" />
                        <span>{{ $album->name }}</span>
                    </label>
                @endforeach
            </div>

            <button type="submit" class="bg-blue-600 text-white px-4 py-2 rounded border border-blue-700">Upload</button>
        </form>
    </div>

    <script>
    const dropArea = document.getElementById('drop-area');
    const fileInput = document.getElementById('images');
    const previewContainer = document.getElementById('preview-container');
    const fileListText = document.getElementById('file-list');

    dropArea.addEventListener('click', () => fileInput.click());

    dropArea.addEventListener('dragenter', e => {
        e.preventDefault();
        dropArea.classList.add('border-blue-400', 'bg-blue-50');
    });

    dropArea.addEventListener('dragleave', e => {
        e.preventDefault();
        dropArea.classList.remove('border-blue-400', 'bg-blue-50');
    });

    dropArea.addEventListener('dragover', e => {
        e.preventDefault();
    });

    dropArea.addEventListener('drop', e => {
        e.preventDefault();
        dropArea.classList.remove('border-blue-400', 'bg-blue-50');

        const dt = e.dataTransfer;
        const files = dt.files;
        fileInput.files = files;

        updatePreviews(files);
    });

    fileInput.addEventListener('change', e => {
        updatePreviews(e.target.files);
    });

    function updatePreviews(files) {
        previewContainer.innerHTML = '';
        let count = 0;
        [...files].forEach(file => {
            if (!file.type.startsWith('image/')) return;
            count++;
            const reader = new FileReader();
            reader.onload = e => {
                const img = document.createElement('img');
                img.src = e.target.result;
                img.className = 'rounded shadow w-full h-24 object-cover';
                previewContainer.appendChild(img);
            };
            reader.readAsDataURL(file);
        });
        fileListText.textContent = count > 0 ? `${count} image(s) selected` : 'No files selected';
    }
    </script>
</x-app-layout>
