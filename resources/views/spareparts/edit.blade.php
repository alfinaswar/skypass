<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-blue-700 leading-tight">
            {{ __('Edit Sparepart Data') }}
        </h2>
    </x-slot>

    <div class="py-12 bg-gray-50 relative">
        
        <!-- ERROR POPUP (Manual Close) -->
        @if ($errors->any() || session('error'))
            <div id="errorPopup" class="fixed inset-0 flex items-center justify-center z-50">
                <div class="absolute inset-0 bg-black opacity-50" onclick="closeErrorPopup()"></div>
                <div class="bg-white rounded-lg shadow-xl p-6 relative z-10 max-w-md w-full mx-4 border-t-4 border-red-600">
                    <div class="flex justify-between items-start mb-4">
                        <div class="flex items-center">
                            <div class="rounded-full bg-red-100 p-2 mr-3">
                                <svg class="h-6 w-6 text-red-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path></svg>
                            </div>
                            <h3 class="text-lg font-bold text-red-600">Failed to Update!</h3>
                        </div>
                        <button onclick="closeErrorPopup()" class="text-gray-400 hover:text-gray-500 focus:outline-none">
                            <svg class="h-6 w-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path></svg>
                        </button>
                    </div>
                    <div class="text-sm text-gray-600 ml-11">
                        @if(session('error')) <p class="mb-2">{{ session('error') }}</p> @endif
                        <ul class="list-disc pl-5 space-y-1">
                            @foreach ($errors->all() as $error) <li>{{ $error }}</li> @endforeach
                        </ul>
                    </div>
                    <div class="mt-6 flex justify-end">
                        <button onclick="closeErrorPopup()" class="px-4 py-2 bg-gray-200 text-gray-800 rounded hover:bg-gray-300 font-semibold transition">Close</button>
                    </div>
                </div>
            </div>
        @endif

        <!-- CAMERA MODAL -->
        <div id="cameraModal" class="fixed inset-0 z-50 hidden bg-black bg-opacity-75 flex items-center justify-center">
            <div class="bg-white p-4 rounded-lg shadow-lg w-full max-w-lg mx-4">
                <h3 class="text-lg font-bold text-gray-900 mb-2">Take Photo</h3>
                <div class="relative bg-black rounded-lg overflow-hidden aspect-video mb-4">
                    <video id="cameraVideo" autoplay playsinline class="w-full h-full object-cover"></video>
                </div>
                <div class="flex justify-end gap-2">
                    <button type="button" onclick="stopCamera()" class="px-4 py-2 bg-gray-200 text-gray-800 rounded hover:bg-gray-300 font-semibold">Cancel</button>
                    <button type="button" onclick="capturePhoto()" class="px-4 py-2 bg-blue-700 text-white rounded hover:bg-blue-800 font-semibold">Capture</button>
                </div>
            </div>
        </div>

        <!-- MAIN FORM -->
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <form action="{{ route('spareparts.update', $sparepart->id) }}" method="POST" enctype="multipart/form-data">
                @csrf
                @method('PUT') <!-- PENTING: Untuk Update Data -->

                <!-- SECTION 1: ITEM INFORMATION -->
                <div class="mb-10">
                    <h3 class="text-2xl font-bold text-blue-700 mb-6 border-b-2 border-blue-700 pb-2">
                        Item Information
                    </h3>

                    <div class="grid grid-cols-1 gap-6">
                        <!-- Description -->
                        <div>
                            <label for="description" class="block text-sm font-bold text-gray-700 mb-2">Sparepart Description</label>
                            <input type="text" name="description" id="description" 
                                class="w-full rounded-md border-gray-300 bg-white shadow-sm focus:border-blue-700 focus:ring-blue-700 py-3 px-4" 
                                value="{{ old('description', $sparepart->description) }}" required>
                        </div>

                        <!-- Category -->
                        <div>
                            <label class="block text-sm font-bold text-gray-700 mb-3">Category</label>
                            <div class="flex flex-wrap gap-10">
                                <label class="flex items-center cursor-pointer group bg-white px-4 py-2 rounded-md border border-gray-200 shadow-sm hover:border-red-600 transition-colors">
                                    <input type="radio" name="category" value="rotating" class="h-5 w-5 text-red-600 focus:ring-red-600 border-gray-300" 
                                        onclick="togglePartNumber(true)"
                                        {{ old('category', $sparepart->category) == 'rotating' ? 'checked' : '' }}>
                                    <span class="ml-3 text-gray-700 font-medium group-hover:text-red-600 transition-colors">Rotating</span>
                                </label>
                                <label class="flex items-center cursor-pointer group bg-white px-4 py-2 rounded-md border border-gray-200 shadow-sm hover:border-blue-700 transition-colors">
                                    <input type="radio" name="category" value="consumable" class="h-5 w-5 text-blue-700 focus:ring-blue-700 border-gray-300" 
                                        onclick="togglePartNumber(false)"
                                        {{ old('category', $sparepart->category) == 'consumable' ? 'checked' : '' }}>
                                    <span class="ml-3 text-gray-700 font-medium group-hover:text-blue-700 transition-colors">Consumable</span>
                                </label>
                            </div>
                        </div>

                        <!-- Part Number -->
                        <div class="transition-all duration-300" id="partNumberContainer">
                            <label for="part_number" class="block text-sm font-bold text-gray-700 mb-2">Part Number</label>
                            <input type="text" name="part_number" id="part_number" 
                                class="w-full rounded-md border-gray-300 bg-white shadow-sm focus:border-blue-700 focus:ring-blue-700 disabled:bg-gray-200 disabled:text-gray-400 py-3 px-4" 
                                value="{{ old('part_number', $sparepart->part_number) }}">
                            <p class="text-xs text-red-600 mt-1 font-semibold" id="partNumberHint">*Only active for Rotating category</p>
                        </div>

                        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                            <!-- Unit -->
                            <div>
                                <label for="unit" class="block text-sm font-bold text-gray-700 mb-2">Unit</label>
                                <input type="text" name="unit" id="unit" 
                                    class="w-full rounded-md border-gray-300 bg-white shadow-sm focus:border-blue-700 focus:ring-blue-700 py-3 px-4" 
                                    value="{{ old('unit', $sparepart->unit) }}" required>
                            </div>

                            <!-- Location -->
                            <div>
                                <label for="rack_location" class="block text-sm font-bold text-gray-700 mb-2">Rack Location</label>
                                <input type="text" name="rack_location" id="rack_location" 
                                    class="w-full rounded-md border-gray-300 bg-white shadow-sm focus:border-blue-700 focus:ring-blue-700 py-3 px-4" 
                                    value="{{ old('rack_location', $sparepart->rack_location) }}" required>
                            </div>
                        </div>

                        <!-- Image -->
                        <div>
                            <label for="image" class="block text-sm font-bold text-gray-700 mb-2">Sparepart Photo</label>
                            
                            <div class="mt-1 flex justify-center px-6 pt-5 pb-6 border-2 border-gray-300 border-dashed rounded-md bg-white hover:border-blue-700 hover:bg-blue-50 transition-colors cursor-pointer group" 
                                 onclick="document.getElementById('image').click()">
                                <div class="space-y-1 text-center">
                                    
                                    <!-- LOGIC PREVIEW: Show existing image if no new file selected -->
                                    <div id="previewContainer">
                                        @if($sparepart->image_url && file_exists(public_path($sparepart->image_url)))
                                            <img id="imagePreview" src="{{ asset($sparepart->image_url) }}" class="mx-auto h-32 w-auto object-contain rounded-md mb-2 shadow-sm border border-gray-200" />
                                            <p id="currentImageText" class="text-xs text-gray-500 mb-2">(Current Image)</p>
                                        @else
                                            <!-- If no image exists, show icon (hidden by JS if new image selected) -->
                                            <svg id="uploadIcon" class="mx-auto h-12 w-12 text-gray-400 group-hover:text-blue-700" stroke="currentColor" fill="none" viewBox="0 0 48 48">
                                                <path d="M28 8H12a4 4 0 00-4 4v20m32-12v8m0 0v8a4 4 0 01-4 4H12a4 4 0 01-4-4v-4m32-4l-3.172-3.172a4 4 0 00-5.656 0L28 28M8 32l9.172-9.172a4 4 0 015.656 0L28 28m0 0l4 4m4-24h8m-4-4v8m-12 4h.02" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" />
                                            </svg>
                                            <img id="imagePreview" class="hidden mx-auto h-32 w-auto object-contain rounded-md mb-2" />
                                        @endif
                                    </div>

                                    <div class="flex text-sm text-gray-600 justify-center">
                                        <label for="image" class="relative cursor-pointer rounded-md font-medium text-blue-700 hover:text-blue-500 focus-within:outline-none">
                                            <span id="fileNameLabel">Change image (Click, Cam, or Ctrl+V)</span>
                                            <input id="image" name="image" type="file" class="sr-only" accept="image/*" onchange="validateAndUpdateFileName(this)">
                                        </label>
                                    </div>
                                    <p class="text-xs text-gray-500">Leave empty to keep current image</p>
                                </div>
                            </div>

                            <button type="button" onclick="openCameraModal()" class="mt-2 text-sm font-bold text-blue-700 hover:text-blue-900 flex items-center justify-center w-full focus:outline-none">
                                <svg class="w-5 h-5 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 9a2 2 0 012-2h.93a2 2 0 001.664-.89l.812-1.22A2 2 0 0110.07 4h3.86a2 2 0 011.664.89l.812 1.22A2 2 0 0018.07 7H19a2 2 0 012 2v9a2 2 0 01-2 2H5a2 2 0 01-2-2V9z"></path><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 13a3 3 0 11-6 0 3 3 0 016 0z"></path></svg>
                                Or use Camera
                            </button>
                        </div>
                    </div>
                </div>

                <!-- SECTION 2: DATA SETTINGS -->
                <div class="mb-10 mt-16">
                    <h3 class="text-2xl font-bold text-red-600 mb-6 border-b-2 border-red-600 pb-2">
                        Data Settings
                    </h3>
                    
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                        <!-- Initial Stock (Usually Admin can fix stock manually here) -->
                        <div>
                            <label for="current_stock" class="block text-sm font-medium text-gray-700 mb-2">Current Stock</label>
                            <input type="number" name="current_stock" 
                                class="w-full rounded-md border-gray-300 bg-white shadow-sm focus:border-blue-700 focus:ring-blue-700 py-3 px-4" 
                                value="{{ old('current_stock', $sparepart->current_stock) }}" min="0" required>
                        </div>

                        <!-- Minimum Stock -->
                        <div>
                            <label for="min_stock" class="block text-sm font-medium text-gray-700 mb-2">Minimum Stock</label>
                            <input type="number" name="min_stock" 
                                class="w-full rounded-md border-gray-300 bg-white shadow-sm focus:border-blue-700 focus:ring-blue-700 py-3 px-4" 
                                value="{{ old('min_stock', $sparepart->min_stock) }}" min="0" required>
                        </div>

                        <!-- High Frequency -->
                        <div>
                            <label for="limit_high_usage" class="block text-sm font-medium text-gray-700 mb-2">High Frequency <span class="text-red-600 font-bold">(Red)</span></label>
                            <input type="number" name="limit_high_usage" 
                                class="w-full rounded-md border-gray-300 bg-white shadow-sm focus:border-red-600 focus:ring-red-600 py-3 px-4" 
                                value="{{ old('limit_high_usage', $sparepart->limit_high_usage) }}" min="0" required>
                        </div>

                        <!-- Medium Frequency -->
                        <div>
                            <label for="limit_medium_usage" class="block text-sm font-medium text-gray-700 mb-2">Medium Frequency <span class="text-yellow-600 font-bold">(Yellow)</span></label>
                            <input type="number" name="limit_medium_usage" 
                                class="w-full rounded-md border-gray-300 bg-white shadow-sm focus:border-yellow-500 focus:ring-yellow-500 py-3 px-4" 
                                value="{{ old('limit_medium_usage', $sparepart->limit_medium_usage) }}" min="0" required>
                        </div>
                    </div>
                </div>

                <!-- ACTION BUTTONS -->
                <div class="mt-8 mb-12 flex justify-end gap-4">
                    <a href="{{ route('spareparts.index') }}" 
                       class="inline-flex items-center px-8 py-4 bg-gray-200 border border-gray-300 rounded-md font-bold text-gray-700 text-lg hover:bg-gray-300 hover:text-gray-900 focus:outline-none transition transform hover:scale-105 shadow-md">
                        <svg class="w-6 h-6 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"></path></svg>
                        Back
                    </a>

                    <button type="submit" 
                        class="inline-flex items-center px-8 py-4 bg-blue-700 border border-transparent rounded-md font-bold text-white text-lg hover:bg-blue-800 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-700 transition transform hover:scale-105 shadow-md">
                        <svg class="w-6 h-6 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-8l-4-4m0 0L8 8m4-4v12"></path></svg>
                        Update
                    </button>
                </div>

            </form>
        </div>
    </div>

    <!-- UI Scripts -->
    <script>
        function togglePartNumber(isRotating) {
            const input = document.getElementById('part_number');
            const container = document.getElementById('partNumberContainer');
            
            if (isRotating) {
                input.disabled = false;
                input.required = true;
                container.classList.remove('opacity-50');
            } else {
                input.disabled = true;
                input.required = false;
                input.value = ''; 
                container.classList.add('opacity-50');
            }
        }

        // Camera Logic
        let videoStream;
        async function openCameraModal() {
            const modal = document.getElementById('cameraModal');
            const video = document.getElementById('cameraVideo');
            modal.classList.remove('hidden');
            try {
                videoStream = await navigator.mediaDevices.getUserMedia({ video: { facingMode: 'environment' } });
                video.srcObject = videoStream;
            } catch (err) {
                alert("Cannot access camera: " + err.message);
                closeCameraModal(); 
            }
        }
        function stopCamera() {
            const modal = document.getElementById('cameraModal');
            const video = document.getElementById('cameraVideo');
            modal.classList.add('hidden');
            if (videoStream) {
                videoStream.getTracks().forEach(track => track.stop());
            }
            video.srcObject = null;
        }
        function capturePhoto() {
            const video = document.getElementById('cameraVideo');
            const canvas = document.createElement('canvas');
            canvas.width = video.videoWidth;
            canvas.height = video.videoHeight;
            const context = canvas.getContext('2d');
            context.drawImage(video, 0, 0, canvas.width, canvas.height);
            canvas.toBlob(function(blob) {
                const fileName = "camera_capture_" + Date.now() + ".jpg";
                const file = new File([blob], fileName, { type: "image/jpeg" });
                const inputElement = document.getElementById('image');
                const dataTransfer = new DataTransfer();
                dataTransfer.items.add(file);
                inputElement.files = dataTransfer.files;
                validateAndUpdateFileName(inputElement);
                stopCamera();
                // Blink Feedback
                const labelContainer = inputElement.closest('.group');
                if(labelContainer) {
                    labelContainer.style.borderColor = '#006BB8';
                    setTimeout(() => { labelContainer.style.borderColor = ''; }, 500);
                }
            }, 'image/jpeg', 0.9);
        }

        // Validation & Preview
        function validateAndUpdateFileName(input) {
            const label = document.getElementById('fileNameLabel');
            const preview = document.getElementById('imagePreview');
            const icon = document.getElementById('uploadIcon');
            const currentTxt = document.getElementById('currentImageText');
            
            if (input.files && input.files.length > 0) {
                const file = input.files[0];
                const fileSizeMB = file.size / 1024 / 1024;
                
                if(fileSizeMB > 2) {
                    alert('Error: File size too large! Maximum limit is 2MB.');
                    input.value = '';
                    label.textContent = 'Change image (Click, Cam, or Ctrl+V)';
                    label.style.fontWeight = 'normal';
                    label.classList.remove('text-blue-900');
                    return; // Keep existing preview if upload fails
                }

                label.textContent = file.name;
                label.style.fontWeight = 'bold';
                label.classList.add('text-blue-900');

                // Update Preview with New Image
                const reader = new FileReader();
                reader.onload = function(e) {
                    preview.src = e.target.result;
                    preview.classList.remove('hidden');
                    if(icon) icon.classList.add('hidden');
                    if(currentTxt) currentTxt.classList.add('hidden'); // Hide "Current Image" text
                }
                reader.readAsDataURL(file);

            } else {
                label.textContent = 'Change image (Click, Cam, or Ctrl+V)';
                label.style.fontWeight = 'normal';
                label.classList.remove('text-blue-900');
            }
        }

        function closeErrorPopup() {
            const popup = document.getElementById('errorPopup');
            if(popup) popup.style.display = 'none';
        }

        // Paste Logic
        const targetInputId = 'image';
        document.addEventListener('paste', function(e) {
            const items = e.clipboardData.items;
            for (let i = 0; i < items.length; i++) {
                if (items[i].type.indexOf('image') !== -1) {
                    const blob = items[i].getAsFile();
                    const fileSizeMB = blob.size / 1024 / 1024;
                    if(fileSizeMB > 2) {
                        alert('Error: Pasted image is too large! Maximum limit is 2MB.');
                        return;
                    }
                    const fileName = "pasted_image_" + Date.now() + ".png";
                    const file = new File([blob], fileName, { type: blob.type });
                    const inputElement = document.getElementById(targetInputId);
                    if(inputElement) {
                        const dataTransfer = new DataTransfer();
                        dataTransfer.items.add(file);
                        inputElement.files = dataTransfer.files;
                        validateAndUpdateFileName(inputElement);
                        inputElement.closest('.group').style.borderColor = '#006BB8';
                        setTimeout(() => inputElement.closest('.group').style.borderColor = '', 300);
                    }
                    break;
                }
            }
        });

        // Init State
        document.addEventListener('DOMContentLoaded', function() {
            const isRotating = document.querySelector('input[name="category"]:checked')?.value === 'rotating';
            togglePartNumber(isRotating);
        });
    </script>
</x-app-layout>