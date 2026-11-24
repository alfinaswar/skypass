<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-blue-700 leading-tight">
            {{ __('Good Receipt Submission Form') }}
        </h2>
    </x-slot>

    <div class="py-12 bg-gray-50 relative">
        
        <!-- POPUP SECTION -->
        @if(session('success'))
            <div id="successPopup" class="fixed inset-0 flex items-center justify-center z-50">
                <div class="absolute inset-0 bg-black opacity-40"></div>
                <div class="bg-white rounded-lg shadow-xl p-6 relative z-10 flex flex-col items-center animate-bounce-in max-w-sm w-full mx-4">
                    <div class="rounded-full bg-green-100 p-3 mb-4">
                        <svg class="h-10 w-10 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path>
                        </svg>
                    </div>
                    <h3 class="text-xl font-bold text-gray-900 mb-2">Success!</h3>
                    <p class="text-gray-600 text-center">{{ session('success') }}</p>
                </div>
            </div>
        @endif

        @if ($errors->any() || session('error'))
            <div id="errorPopup" class="fixed inset-0 flex items-center justify-center z-50">
                <div class="absolute inset-0 bg-black opacity-50" onclick="document.getElementById('errorPopup').style.display='none'"></div>
                <div class="bg-white rounded-lg shadow-xl p-6 relative z-10 max-w-md w-full mx-4 border-t-4 border-red-600">
                    <div class="flex justify-between items-start mb-4">
                        <h3 class="text-lg font-bold text-red-600">Transaction Failed!</h3>
                        <button onclick="document.getElementById('errorPopup').style.display='none'" class="text-gray-400 hover:text-gray-500">
                            <svg class="h-6 w-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path></svg>
                        </button>
                    </div>
                    <div class="text-sm text-gray-600">
                        @if(session('error')) <p class="mb-2">{{ session('error') }}</p> @endif
                        <ul class="list-disc pl-5 space-y-1">
                            @foreach ($errors->all() as $error) <li>{{ $error }}</li> @endforeach
                        </ul>
                    </div>
                </div>
            </div>
        @endif

        <!-- --- CAMERA MODAL (Hidden by Default) --- -->
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

        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <form action="{{ route('transactions.gr.store') }}" method="POST" enctype="multipart/form-data" id="grForm">
                @csrf

                <!-- SECTION 1: SOURCE INFO -->
                <div class="bg-white p-6 rounded-lg shadow-sm border-t-4 border-blue-700 mb-8">
                    <h3 class="text-xl font-bold text-gray-900 mb-6 border-b-2 border-blue-700 pb-2">
                        Source Information
                    </h3>

                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                        <!-- Date (READONLY) -->
                        <div>
                            <label class="block text-sm font-bold text-gray-700 mb-2">Transaction Date</label>
                            <input type="date" name="date" 
                                class="w-full rounded-md border-gray-300 text-gray-500 shadow-sm focus:border-blue-700 focus:ring-blue-700" 
                                value="{{ now()->timezone('Asia/Jakarta')->format('Y-m-d') }}" 
                                >
                        </div>

                        <!-- Source Type -->
                        <div>
                            <label class="block text-sm font-bold text-gray-700 mb-2">Source Type</label>
                            <select name="source_type" id="source_type" onchange="handleSourceChange()" class="w-full rounded-md border-gray-300 shadow-sm focus:border-blue-700 focus:ring-blue-700" required>
                                <option value="" disabled selected>-- Select Source --</option>
                                <option value="procurement">Procurement (Vendor)</option>
                                <option value="purchase">Purchase (Direct Buy)</option>
                                <option value="cannibalization">Cannibalization</option>
                                <option value="ibt">Inter-Branch Transfer (IBT)</option>
                            </select>
                        </div>
                    </div>

                    <!-- DYNAMIC FIELDS BASED ON SOURCE -->
                    <div class="mt-6 grid grid-cols-1 md:grid-cols-2 gap-6">
                        <div id="field_source_text" class="hidden">
                            <label id="label_source_text" class="block text-sm font-bold text-gray-700 mb-2">Vendor Name</label>
                            <input type="text" name="source_name" id="input_source_name" class="w-full rounded-md border-gray-300 shadow-sm focus:border-blue-700 focus:ring-blue-700" placeholder="Enter Name">
                        </div>

                        <div id="field_vehicle" class="hidden">
                            <label class="block text-sm font-bold text-gray-700 mb-2">Source Facility</label>
                            <select name="vehicle_id" id="input_vehicle" class="w-full rounded-md border-gray-300 shadow-sm focus:border-blue-700 focus:ring-blue-700">
                                <option value="">-- Select Facility --</option>
                                @foreach($vehicles as $v)
                                    <option value="{{ $v->id }}">{{ $v->fleet_number }}</option>
                                @endforeach
                            </select>
                        </div>

                        <!-- Upload Image/PDF (BAST / Nota) -->
                        <div id="field_image" class="hidden">
                            <label id="label_image" class="block text-sm font-bold text-gray-700 mb-2">Upload Document (Nota/BAST)</label>
                            
                            <div class="mt-1 flex justify-center px-6 pt-5 pb-6 border-2 border-gray-300 border-dashed rounded-md bg-white hover:border-blue-700 hover:bg-blue-50 transition-colors cursor-pointer group" 
                                 onclick="document.getElementById('input_image').click()">
                                <div class="space-y-1 text-center">
                                    <svg id="uploadIcon" class="mx-auto h-12 w-12 text-gray-400 group-hover:text-blue-700" stroke="currentColor" fill="none" viewBox="0 0 48 48">
                                        <path d="M28 8H12a4 4 0 00-4 4v20m32-12v8m0 0v8a4 4 0 01-4 4H12a4 4 0 01-4-4v-4m32-4l-3.172-3.172a4 4 0 00-5.656 0L28 28M8 32l9.172-9.172a4 4 0 015.656 0L28 28m0 0l4 4m4-24h8m-4-4v8m-12 4h.02" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" />
                                    </svg>
                                    
                                    <img id="imagePreview" class="hidden mx-auto h-32 w-auto object-contain rounded-md mb-2" />
                                    <!-- TAMBAHKAN onclick="event.stopPropagation()" DI SINI -->
                                    <a id="pdfPreviewLink" href="#" target="_blank" onclick="event.stopPropagation()" class="hidden mt-2 inline-flex items-center text-sm text-blue-600 hover:text-blue-800 hover:underline font-bold transition-colors">
                                        <svg class="w-4 h-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 21h7a2 2 0 002-2V9.414a1 1 0 00-.293-.707l-5.414-5.414A1 1 0 0012.586 3H7a2 2 0 00-2 2v11m0 5l4.879-4.879m0 0a3 3 0 104.243-4.242 z 3 3 0 00-4.243 4.242z"></path></svg>
                                        Preview PDF Document
                                    </a>

                                    <div class="flex text-sm text-gray-600 justify-center">
                                        <label for="input_image" class="relative cursor-pointer rounded-md font-medium text-blue-700 hover:text-blue-500 focus-within:outline-none">
                                            <span id="fileNameLabel">Upload file or (Ctrl+V)</span>
                                            <input id="input_image" name="proof_image" type="file" class="sr-only" accept=".jpg,.jpeg,.png,.pdf" onchange="validateAndUpdateFileName(this)">
                                        </label>
                                    </div>
                                    <p class="text-xs text-gray-500">PNG, JPG, JPEG, PDF up to 5MB</p>
                                </div>
                            </div>

                            <button type="button" onclick="openCameraModal()" class="mt-2 text-sm font-bold text-blue-700 hover:text-blue-900 flex items-center justify-center w-full focus:outline-none">
                                <svg class="w-5 h-5 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 9a2 2 0 012-2h.93a2 2 0 001.664-.89l.812-1.22A2 2 0 0110.07 4h3.86a2 2 0 011.664.89l.812 1.22A2 2 0 0018.07 7H19a2 2 0 012 2v9a2 2 0 01-2 2H5a2 2 0 01-2-2V9z"></path><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 13a3 3 0 11-6 0 3 3 0 016 0z"></path></svg>
                                Or use Camera
                            </button>
                        </div>
                    </div>

                    <!-- Info Box untuk Procurement -->
                    <div id="procurement_info" class="hidden mt-6 bg-blue-50 border-l-4 border-blue-500 p-4 rounded">
                        <div class="flex">
                            <div class="flex-shrink-0">
                                <svg class="h-5 w-5 text-blue-500" viewBox="0 0 20 20" fill="currentColor">
                                    <path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7-4a1 1 0 11-2 0 1 1 0 012 0zM9 9a1 1 0 000 2v3a1 1 0 001 1h1a1 1 0 100-2v-3a1 1 0 00-1-1H9z" clip-rule="evenodd"/>
                                </svg>
                            </div>
                            <div class="ml-3">
                                <p class="text-sm text-blue-700">
                                    <strong>Note:</strong> Transaksi Procurement akan disimpan sebagai <strong>Pending</strong>. Anda bisa mengupload BAST dan memasukkan Nomor PO nanti di menu <strong>Pending GR</strong>.
                                </p>
                            </div>
                        </div>
                    </div>

                </div>

                <!-- SECTION 2: ITEM LIST -->
                <div class="bg-white p-6 rounded-lg shadow-sm border-t-4 border-green-600 mb-8">
                    <h3 class="text-xl font-bold text-gray-900 mb-6 border-b-2 border-green-600 pb-2">
                        Item List
                    </h3>

                    <div class="flex flex-col md:flex-row gap-4 mb-6 items-end">
                        <div class="w-full md:w-1/2 relative">
                            <label class="block text-sm font-bold text-gray-700 mb-2">Search Item</label>
                            <div class="relative">
                                <input type="text" id="part_search" class="w-full rounded-md border-gray-300 shadow-sm focus:border-blue-700 focus:ring-blue-700 pl-10" placeholder="Type name or part number..." autocomplete="off">
                                <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                                    <svg class="h-5 w-5 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/></svg>
                                </div>
                            </div>
                            <div id="search_results" class="absolute z-50 bg-white border border-gray-300 w-full mt-1 rounded-md shadow-lg hidden max-h-60 overflow-y-auto"></div>
                        </div>

                        <div class="w-full md:w-1/4">
                            <label class="block text-sm font-bold text-gray-700 mb-2">Selected Item</label>
                            <input type="text" id="selected_part_name" disabled class="w-full rounded-md border-gray-200 bg-gray-100 text-gray-600" placeholder="No item selected">
                            
                            <!-- Hidden Fields for JS Logic -->
                            <input type="hidden" id="selected_part_id"> 
                            <input type="hidden" id="selected_part_number"> 
                            <input type="hidden" id="selected_part_category"> 
                        </div>

                        <div class="w-full md:w-1/6">
                            <label class="block text-sm font-bold text-gray-700 mb-2">Qty</label>
                            <div class="flex">
                                <input type="number" id="input_qty" min="1" class="w-full rounded-l-md border-gray-300 shadow-sm focus:border-blue-700 focus:ring-blue-700" placeholder="0">
                                <span id="unit_display" class="inline-flex items-center px-3 rounded-r-md border border-l-0 border-gray-300 bg-gray-50 text-gray-500 text-sm">-</span>
                            </div>
                        </div>

                        <div class="w-full md:w-auto">
                            <button type="button" onclick="addItemToTable()" class="w-full px-6 py-2 bg-blue-700 text-white font-bold rounded-md hover:bg-blue-800 transition shadow-md">Add</button>
                        </div>
                    </div>

                    <div class="overflow-x-auto">
                        <table class="min-w-full divide-y divide-gray-200 border">
                            <thead class="bg-gray-50">
                                <tr>
                                    <th class="px-6 py-3 text-left text-xs font-bold text-gray-500 uppercase">Part Number</th>
                                    <th class="px-6 py-3 text-left text-xs font-bold text-gray-500 uppercase">Description</th>
                                    <th class="px-6 py-3 text-left text-xs font-bold text-gray-500 uppercase">Category</th>
                                    <th class="px-6 py-3 text-center text-xs font-bold text-gray-500 uppercase">Qty</th>
                                    <th class="px-6 py-3 text-right text-xs font-bold text-gray-500 uppercase">Action</th>
                                </tr>
                            </thead>
                            <tbody id="items_table_body" class="bg-white divide-y divide-gray-200">
                                <tr id="empty_row"><td colspan="5" class="px-6 py-10 text-center text-gray-500">No items added yet. Search and add items above.</td></tr>
                            </tbody>
                        </table>
                    </div>
                </div>

                <div class="mt-8 mb-12 flex justify-end gap-4">
                    <a href="{{ route('spareparts.index') }}" class="inline-flex items-center px-8 py-4 bg-gray-200 border border-gray-300 rounded-md font-bold text-gray-700 text-lg hover:bg-gray-300 transition shadow-md">Done</a>
                    <button type="submit" class="inline-flex items-center px-8 py-4 bg-green-600 border border-transparent rounded-md font-bold text-white text-lg hover:bg-green-700 transition shadow-md">
                        <svg class="w-6 h-6 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path></svg>
                        Submit Transaction
                    </button>
                </div>
            </form>
        </div>
    </div>

    <script>
        function handleSourceChange() {
            const type = document.getElementById('source_type').value;
            const fieldText = document.getElementById('field_source_text');
            const labelText = document.getElementById('label_source_text');
            const inputText = document.getElementById('input_source_name');
            const fieldVehicle = document.getElementById('field_vehicle');
            const inputVehicle = document.getElementById('input_vehicle');
            const fieldImage = document.getElementById('field_image');
            const labelImage = document.getElementById('label_image');
            const inputImage = document.getElementById('input_image');
            const procurementInfo = document.getElementById('procurement_info');

            fieldText.classList.add('hidden');
            fieldVehicle.classList.add('hidden');
            fieldImage.classList.add('hidden');
            procurementInfo.classList.add('hidden');
            
            inputText.required = false;
            inputVehicle.required = false;
            inputImage.required = false;

            if (type === 'procurement') {
                fieldText.classList.remove('hidden');
                labelText.textContent = "Vendor Name (PT)";
                inputText.required = true;
                
                fieldImage.classList.add('hidden'); 
                inputImage.required = false;
                
                procurementInfo.classList.remove('hidden');

            } else if (type === 'purchase') {
                fieldImage.classList.remove('hidden');
                labelImage.textContent = "Upload Purchase Receipt (Nota/PDF)";
                inputImage.required = true;
            } else if (type === 'cannibalization') {
                fieldVehicle.classList.remove('hidden');
                inputVehicle.required = true;
            } else if (type === 'ibt') {
                fieldText.classList.remove('hidden');
                labelText.textContent = "Origin Location";
                inputText.required = true;
            }
        }

        const searchInput = document.getElementById('part_search');
        const resultsBox = document.getElementById('search_results');
        let searchTimeout;

        searchInput.addEventListener('input', function() {
            clearTimeout(searchTimeout);
            const query = this.value;

            if (query.length < 2) {
                resultsBox.classList.add('hidden');
                return;
            }

            searchTimeout = setTimeout(() => {
                fetch(`{{ route('api.spareparts.search') }}?query=${query}`)
                    .then(response => response.json())
                    .then(data => {
                        resultsBox.innerHTML = '';
                        if (data.length > 0) {
                            resultsBox.classList.remove('hidden');
                            data.forEach(item => {
                                const div = document.createElement('div');
                                div.className = "px-4 py-2 hover:bg-blue-50 cursor-pointer border-b last:border-b-0";
                                div.innerHTML = `
                                    <div class="font-bold text-gray-800">${item.description}</div>
                                    <div class="text-xs text-gray-500">
                                        ${item.part_number || 'No Part No'} | Stock: ${item.current_stock} ${item.unit}
                                    </div>
                                `;
                                div.onclick = () => selectItem(item);
                                resultsBox.appendChild(div);
                            });
                        } else {
                            resultsBox.classList.add('hidden');
                        }
                    });
            }, 500);
        });

        document.addEventListener('click', function(e) {
            if (!searchInput.contains(e.target) && !resultsBox.contains(e.target)) {
                resultsBox.classList.add('hidden');
            }
        });

        function selectItem(item) {
            document.getElementById('selected_part_name').value = item.description;
            document.getElementById('selected_part_id').value = item.id;
            
            document.getElementById('selected_part_number').value = item.part_number || '-';
            document.getElementById('selected_part_category').value = item.category;

            document.getElementById('unit_display').textContent = item.unit;
            document.getElementById('input_qty').focus(); 
            searchInput.value = '';
            resultsBox.classList.add('hidden');
        }

        // --- CAMERA LOGIC START ---
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
                
                const inputElement = document.getElementById('input_image');
                const dataTransfer = new DataTransfer();
                dataTransfer.items.add(file);
                inputElement.files = dataTransfer.files;

                validateAndUpdateFileName(inputElement);
                stopCamera();

                const labelContainer = inputElement.closest('.group');
                if(labelContainer) {
                    labelContainer.style.borderColor = '#006BB8';
                    labelContainer.style.backgroundColor = '#EFF6FF'; 
                    setTimeout(() => {
                        labelContainer.style.borderColor = ''; 
                        labelContainer.style.backgroundColor = '';
                    }, 500);
                }
            }, 'image/jpeg', 0.9);
        }
        // --- CAMERA LOGIC END ---

        let cartItems = [];

        function addItemToTable() {
            const id = document.getElementById('selected_part_id').value;
            const name = document.getElementById('selected_part_name').value;
            const qty = parseInt(document.getElementById('input_qty').value);
            const unit = document.getElementById('unit_display').textContent;
            
            const partNumber = document.getElementById('selected_part_number').value;
            const category = document.getElementById('selected_part_category').value;

            if (!id || !qty || qty <= 0) {
                alert("Please select an item and enter valid quantity.");
                return;
            }

            const existingItemIndex = cartItems.findIndex(item => item.id === id);

            if (existingItemIndex !== -1) {
                cartItems[existingItemIndex].qty = parseInt(cartItems[existingItemIndex].qty) + qty;
            } else {
                cartItems.push({ id, name, qty, unit, partNumber, category });
            }

            renderTable();
            resetInputArea();
        }

        function removeItem(index) {
            cartItems.splice(index, 1);
            renderTable();
        }

        function renderTable() {
            const tbody = document.getElementById('items_table_body');
            tbody.innerHTML = '';

            if (cartItems.length === 0) {
                tbody.innerHTML = `<tr id="empty_row"><td colspan="5" class="px-6 py-10 text-center text-gray-500">No items added yet. Search and add items above.</td></tr>`;
                return;
            }

            cartItems.forEach((item, index) => {
                const tr = document.createElement('tr');
                tr.className = "hover:bg-gray-50";
                
                let categoryBadge = '';
                if(item.category === 'rotating') {
                    categoryBadge = '<span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full bg-red-100 text-red-800">Rotating</span>';
                } else {
                    categoryBadge = '<span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full bg-blue-100 text-blue-800">Consumable</span>';
                }

                tr.innerHTML = `
                    <td class="px-6 py-4 text-sm text-gray-500 font-mono">
                        <input type="hidden" name="items[${index}][id]" value="${item.id}">
                        ${item.partNumber}
                    </td>
                    <td class="px-6 py-4 text-sm font-bold text-gray-900">${item.name}</td>
                    <td class="px-6 py-4 text-sm">
                        ${categoryBadge}
                    </td>
                    <td class="px-6 py-4 text-center text-sm font-bold text-blue-700">
                        ${item.qty} <span class="text-gray-400 font-normal text-xs">${item.unit}</span>
                        <input type="hidden" name="items[${index}][qty]" value="${item.qty}">
                    </td>
                    <td class="px-6 py-4 text-right">
                        <button type="button" onclick="removeItem(${index})" class="text-red-600 hover:text-red-900 font-bold text-sm">Remove</button>
                    </td>
                `;
                tbody.appendChild(tr);
            });
        }

        function resetInputArea() {
            document.getElementById('selected_part_name').value = '';
            document.getElementById('selected_part_id').value = '';
            document.getElementById('input_qty').value = '';
            document.getElementById('unit_display').textContent = '-';
            
            document.getElementById('selected_part_number').value = '';
            document.getElementById('selected_part_category').value = '';
            
            searchInput.focus(); 
        }

        const successPopup = document.getElementById('successPopup');
        if (successPopup) {
            setTimeout(function() {
                successPopup.style.transition = 'opacity 0.5s ease';
                successPopup.style.opacity = '0';
                setTimeout(function() {
                    successPopup.style.display = 'none';
                }, 500); 
            }, 2000); 
        }

        // --- 4. PREVENT ENTER KEY SUBMISSION ---
        document.getElementById('part_search').addEventListener('keydown', function(event) {
            if (event.key === 'Enter') {
                event.preventDefault();
            }
        });

        document.getElementById('input_qty').addEventListener('keydown', function(event) {
            if (event.key === 'Enter') {
                event.preventDefault();
                addItemToTable();
            }
        });

        // --- 5. VALIDATE & UPDATE FILE NAME (WITH PDF & PREVIEW) ---
        function validateAndUpdateFileName(input) {
            const label = document.getElementById('fileNameLabel');
            const preview = document.getElementById('imagePreview');
            const pdfLink = document.getElementById('pdfPreviewLink');
            const icon = document.getElementById('uploadIcon');
            
            if (input.files && input.files.length > 0) {
                const file = input.files[0];
                const fileSizeMB = file.size / 1024 / 1024;
                
                // Max limit 5MB sesuai backend
                if(fileSizeMB > 5) {
                    alert('Error: File size too large! Maximum limit is 5MB.');
                    input.value = '';
                    label.textContent = 'Upload file or (Ctrl+V)';
                    label.style.fontWeight = 'normal';
                    label.classList.remove('text-blue-900');
                    
                    preview.src = '#';
                    preview.classList.add('hidden');
                    if(pdfLink) pdfLink.classList.add('hidden');
                    icon.classList.remove('hidden');
                    return;
                }

                label.textContent = file.name;
                label.style.fontWeight = 'bold';
                label.classList.add('text-blue-900');

                if (file.type === 'application/pdf') {
                    preview.classList.add('hidden');
                    icon.classList.add('hidden');
                    if(pdfLink) {
                        pdfLink.href = URL.createObjectURL(file);
                        pdfLink.classList.remove('hidden');
                    }
                } else {
                    if(pdfLink) pdfLink.classList.add('hidden');
                    const reader = new FileReader();
                    reader.onload = function(e) {
                        preview.src = e.target.result;
                        preview.classList.remove('hidden');
                        icon.classList.add('hidden');
                    }
                    reader.readAsDataURL(file);
                }

            } else {
                label.textContent = 'Upload file or (Ctrl+V)';
                label.style.fontWeight = 'normal';
                label.classList.remove('text-blue-900');
                
                preview.src = '#';
                preview.classList.add('hidden');
                if(pdfLink) pdfLink.classList.add('hidden');
                icon.classList.remove('hidden');
            }
        }

        // --- 6. PASTE IMAGE SUPPORT ---
        const targetInputId = 'input_image';
        document.addEventListener('paste', function(e) {
            const inputContainer = document.getElementById('field_image');
            if (inputContainer.classList.contains('hidden')) return;

            const items = e.clipboardData.items;
            for (let i = 0; i < items.length; i++) {
                if (items[i].type.indexOf('image') !== -1) {
                    const blob = items[i].getAsFile();
                    const fileSizeMB = blob.size / 1024 / 1024;

                    if(fileSizeMB > 5) {
                        alert('Error: Pasted image is too large! Maximum limit is 5MB.');
                        return;
                    }

                    const fileName = "pasted_proof_" + Date.now() + ".png";
                    const file = new File([blob], fileName, { type: blob.type });

                    const inputElement = document.getElementById(targetInputId);
                    if(inputElement) {
                        const dataTransfer = new DataTransfer();
                        dataTransfer.items.add(file);
                        inputElement.files = dataTransfer.files;
                        validateAndUpdateFileName(inputElement);
                        
                        inputElement.closest('label').classList.add('border-blue-700', 'bg-blue-50');
                        setTimeout(() => inputElement.closest('label').classList.remove('border-blue-700', 'bg-blue-50'), 300);
                    }
                    break; 
                }
            }
        });
    </script>
</x-app-layout>