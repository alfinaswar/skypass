<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-blue-700 leading-tight">
            {{ __('Warehouse Rack Layout') }}
        </h2>
    </x-slot>

    <div class="py-12 bg-gray-50">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            
            <!-- TOMBOL BACK -->
            <div class="mb-6">
                <a href="{{ route('spareparts.index') }}" class="inline-flex items-center text-gray-600 hover:text-blue-700 transition font-medium">
                    <svg class="h-5 w-5 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"></path></svg>
                    Back to Spareparts
                </a>
            </div>

            <!-- ALERTS -->
            @if(session('success'))
                <div class="mb-6 bg-white border-l-4 border-green-600 p-4 shadow-sm rounded-r-md flex items-center">
                    <svg class="h-6 w-6 text-green-600 mr-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path></svg>
                    <div><p class="font-bold text-gray-800">Success</p><p class="text-sm text-gray-600">{{ session('success') }}</p></div>
                </div>
            @endif

            @if(session('error') || $errors->any())
                <div class="mb-6 bg-white border-l-4 border-red-600 p-4 shadow-sm rounded-r-md flex items-center">
                    <svg class="h-6 w-6 text-red-600 mr-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
                    <div>
                        <p class="font-bold text-gray-800">Error</p>
                        <p class="text-sm text-gray-600">{{ session('error') ?? $errors->first() }}</p>
                    </div>
                </div>
            @endif

            <!-- UPLOAD SECTION (SUPERVISOR ONLY) -->
            @if(auth()->user()->role === 'supervisor')
                <div class="bg-white p-6 rounded-lg shadow-sm border-t-4 border-blue-700 mb-8">
                    <h3 class="text-lg font-bold text-gray-900 mb-4">
                        {{ $layoutUrl ? 'Update Warehouse Layout' : 'Upload Warehouse Layout' }}
                    </h3>
                    
                    <form action="{{ route('warehouse.layout.update') }}" method="POST" enctype="multipart/form-data" class="flex flex-col md:flex-row items-end gap-4">
                        @csrf
                        <div class="w-full md:flex-grow">
                            <label class="block text-sm font-medium text-gray-700 mb-2">Select Image File (JPG/PNG, Max 5MB)</label>
                            <input type="file" name="layout_image" accept="image/*" required
                                class="block w-full text-sm text-gray-500 bg-white border border-gray-300 rounded-md shadow-sm cursor-pointer
                                file:cursor-pointer file:border-0 file:py-2.5 file:px-4 file:mr-4 
                                file:bg-blue-50 file:text-blue-700 hover:file:bg-blue-100 file:font-semibold">
                        </div>
                        <div class="w-full md:w-auto flex-shrink-0">
                            <button type="submit" class="w-full inline-flex justify-center items-center px-6 py-2.5 bg-blue-700 border border-transparent rounded-md font-bold text-white hover:bg-blue-800 transition shadow-sm whitespace-nowrap">
                                <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-8l-4-4m0 0L8 8m4-4v12"></path></svg>
                                Upload Layout
                            </button>
                        </div>
                    </form>
                </div>
            @endif

            <!-- LAYOUT VIEWER -->
            <div class="bg-white rounded-lg shadow-sm border border-gray-200 overflow-hidden">
                <div class="px-6 py-4 border-b border-gray-200 bg-gray-50 flex justify-between items-center">
                    <h3 class="font-bold text-gray-700">Current Layout Map</h3>
                    @if($layoutUrl)
                        <a href="{{ asset($layoutUrl) }}" target="_blank" class="text-sm text-blue-600 hover:underline font-semibold flex items-center" title="Open original image">
                            <svg class="w-4 h-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 21h7a2 2 0 002-2V9.414a1 1 0 00-.293-.707l-5.414-5.414A1 1 0 0012.586 3H7a2 2 0 00-2 2v11m0 5l4.879-4.879m0 0a3 3 0 104.243-4.242 3 3 0 00-4.243 4.242z"></path></svg>
                            View Original Size
                        </a>
                    @endif
                </div>

                <div class="p-6 flex justify-center items-center bg-gray-100 min-h-[400px]">
                    @if($layoutUrl)
                        <div class="w-full flex flex-col items-center">
                            <!-- Container Zoom -->
                            <div class="overflow-hidden w-full h-[600px] border border-gray-300 shadow-inner bg-white cursor-move rounded" id="panZoomContainer">
                                <img src="{{ asset($layoutUrl) }}" alt="Warehouse Layout" id="layoutImage" class="max-w-none w-full h-full object-contain origin-center transition-transform duration-100 ease-out" style="transform: scale(1);">
                            </div>
                            <p class="text-center text-xs text-gray-500 mt-4 w-full block font-medium">💡 Hint: Scroll to Zoom In/Out. Click and drag to pan.</p>
                        </div>
                    @else
                        <!-- Tampilan Default Jika Kosong -->
                        <div class="text-center py-10">
                            <svg class="mx-auto h-16 w-16 text-gray-400 mb-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 20l-5.447-2.724A1 1 0 013 16.382V5.618a1 1 0 011.447-.894L9 7m0 13l6-3m-6 3V7m6 10l4.553 2.276A1 1 0 0021 18.382V7.618a1 1 0 00-.553-.894L15 4m0 13V4m0 0L9 7"></path>
                            </svg>
                            <p class="text-gray-600 text-lg font-medium">Warehouse layout not uploaded yet.</p>
                            <p class="text-gray-400 text-sm mt-1">Please ask the supervisor to upload it first.</p>
                        </div>
                    @endif
                </div>
            </div>

        </div>
    </div>

    <!-- Script for Mouse Pan & Zoom (Aktif jika gambar ada) -->
    @if($layoutUrl)
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const container = document.getElementById('panZoomContainer');
            const img = document.getElementById('layoutImage');
            
            let scale = 1;
            let isDragging = false;
            let startX, startY;
            let translateX = 0, translateY = 0;

            // 1. Zoom using mouse wheel
            container.addEventListener('wheel', function(e) {
                e.preventDefault();
                const zoomSensitivity = 0.1;
                const delta = e.deltaY > 0 ? -1 : 1;
                
                let newScale = scale + (delta * zoomSensitivity);
                
                // Restrict scale limits
                if (newScale < 0.5) newScale = 0.5; // Min zoom out
                if (newScale > 4) newScale = 4;     // Max zoom in
                
                scale = newScale;
                updateTransform();
            }, { passive: false });

            // 2. Panning using mouse drag
            container.addEventListener('mousedown', (e) => {
                isDragging = true;
                container.style.cursor = 'grabbing';
                startX = e.pageX - translateX;
                startY = e.pageY - translateY;
            });

            container.addEventListener('mouseleave', () => {
                isDragging = false;
                container.style.cursor = 'move';
            });

            container.addEventListener('mouseup', () => {
                isDragging = false;
                container.style.cursor = 'move';
            });

            container.addEventListener('mousemove', (e) => {
                if (!isDragging) return;
                e.preventDefault();
                translateX = e.pageX - startX;
                translateY = e.pageY - startY;
                updateTransform();
            });

            function updateTransform() {
                img.style.transform = `translate(${translateX}px, ${translateY}px) scale(${scale})`;
            }
        });
    </script>
    @endif
</x-app-layout>