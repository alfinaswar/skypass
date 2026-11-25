<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-blue-700 leading-tight">
            {{ __('Good Issue Submission Form') }}
        </h2>
    </x-slot>

    <div class="py-12 bg-gray-50 relative">
        
        <!-- --- 1. POPUP SUKSES --- -->
        @if(session('success'))
            <div id="successPopup" class="fixed inset-0 flex items-center justify-center z-50">
                <div class="absolute inset-0 bg-black opacity-40"></div>
                <div class="bg-white rounded-lg shadow-xl p-6 relative z-10 flex flex-col items-center animate-bounce-in max-w-sm w-full mx-4">
                    <div class="rounded-full bg-green-100 p-3 mb-4">
                        <svg class="h-10 w-10 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path></svg>
                    </div>
                    <h3 class="text-xl font-bold text-gray-900 mb-2">Success!</h3>
                    <p class="text-gray-600 text-center">{{ session('success') }}</p>
                </div>
            </div>
        @endif

        <!-- --- 2. POPUP ERROR --- -->
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
                        @if(session('error')) <p class="mb-2 font-semibold">{{ session('error') }}</p> @endif
                        <ul class="list-disc pl-5 space-y-1">
                            @foreach ($errors->all() as $error) <li>{{ $error }}</li> @endforeach
                        </ul>
                    </div>
                </div>
            </div>
        @endif

        <!-- --- 3. POPUP WARNING 5 HARI (SPESIAL) --- -->
        @if(session('warning_5_day'))
            <div id="warningPopup" class="fixed inset-0 flex items-center justify-center z-50">
                <div class="absolute inset-0 bg-black opacity-60"></div>
                <div class="bg-white rounded-lg shadow-xl p-6 relative z-10 max-w-md w-full mx-4 border-t-4 border-yellow-500">
                    <div class="flex items-start mb-4">
                        <div class="rounded-full bg-yellow-100 p-2 mr-3">
                            <svg class="h-8 w-8 text-yellow-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"></path></svg>
                        </div>
                        <div>
                            <h3 class="text-xl font-bold text-gray-900">Warning: Possible Recurring Issue</h3>
                            <p class="text-sm text-gray-600 mt-2">{{ session('warning_5_day') }}</p>
                        </div>
                    </div>
                    
                    <div class="mt-6 flex justify-end gap-3">
                        <a href="{{ route('transactions.gi.create') }}" class="px-4 py-2 bg-gray-200 text-gray-800 rounded hover:bg-gray-300 font-semibold transition">
                            Cancel
                        </a>
                        <!-- Tombol ini memicu fungsi JS untuk submit ulang dengan paksaan -->
                        <button onclick="submitForce()" class="px-4 py-2 bg-red-600 text-white rounded hover:bg-red-700 font-semibold transition shadow-md">
                            Proceed Anyway
                        </button>
                    </div>
                </div>
            </div>
        @endif

        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <form action="{{ route('transactions.gi.store') }}" method="POST" id="giForm" onsubmit="return validateGiForm()">
                @csrf
                <!-- Input Hidden untuk Force Submit (Default kosong) -->
                <input type="hidden" name="force_submit" id="force_submit_input" value="">

                <!-- BAGIAN 1: INFO KENDARAAN -->
                <div class="bg-white p-6 rounded-lg shadow-sm border-t-4 border-blue-700 mb-8">
                    <h3 class="text-xl font-bold text-gray-900 mb-6 border-b-2 border-blue-700 pb-2">
                        Transaction Info
                    </h3>

                    <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
                        <!-- Date -->
                        <div>
                            <label class="block text-sm font-bold text-gray-700 mb-2">Date</label>
                            <input type="date" name="date" 
                                class="w-full rounded-md border-gray-300 text-gray-500 shadow-sm " 
                                value="{{ now()->timezone('Asia/Jakarta')->format('Y-m-d') }}" 
                                >
                        </div>

                        <!-- Facility (Searchable Dropdown) -->
                        <div id="field_vehicle">
                            
                            <label class="block text-sm font-bold text-gray-700 mb-2">Facility (Target)</label>
                            <div class="relative">
                                <input type="hidden" name="vehicle_id" id="hidden_vehicle_id" required>
                                <input type="text" id="vehicle_search" 
                                    class="w-full rounded-md border-gray-300 shadow-sm focus:border-blue-700 focus:ring-blue-700" 
                                    placeholder="Type to search facility..." autocomplete="off" required>
                                
                                <div id="vehicle_dropdown" class="absolute z-50 w-full bg-white border border-gray-300 mt-1 rounded-md shadow-lg hidden max-h-48 overflow-y-auto">
                                    @foreach($vehicles as $v)
                                        <div class="px-4 py-2 hover:bg-blue-50 cursor-pointer vehicle-option text-sm" 
                                             data-id="{{ $v->id }}" data-name="{{ $v->fleet_number }}">
                                            {{ $v->fleet_number }}
                                        </div>
                                    @endforeach
                                </div>
                            </div>
                        </div>

                        <!-- Technician (Autocomplete Custom UI) -->
                        <div class="relative"> <!-- PERBAIKAN: Tambah class relative di sini -->
                            <label class="block text-sm font-bold text-gray-700 mb-2">Technician</label>
                            <!-- PERBAIKAN: Hapus list="mechanic_list" -->
                            <input type="text" name="requester_name" id="mechanic_input" 
                                class="w-full rounded-md border-gray-300 shadow-sm focus:border-blue-700 focus:ring-blue-700" 
                                placeholder="Who requested?" value="{{ old('requester_name') }}" required autocomplete="off">
                            
                            
                            <div id="mechanic_dropdown" class="absolute z-50 w-full bg-white border border-gray-300 mt-1 rounded-md shadow-lg hidden max-h-48 overflow-y-auto">
                            </div>
                        </div>
                    </div>
                </div>

                <!-- BAGIAN 2: ITEM LIST -->
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

                    <div class="overflow-x-auto mb-6">
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

                    <!-- INFO BOX PENGGANTI UPLOAD -->
                    <div class="bg-blue-50 border-l-4 border-blue-500 p-4 rounded">
                        <div class="flex">
                            <div class="flex-shrink-0">
                                <svg class="h-5 w-5 text-blue-500" viewBox="0 0 20 20" fill="currentColor">
                                    <path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7-4a1 1 0 11-2 0 1 1 0 012 0zM9 9a1 1 0 000 2v3a1 1 0 001 1h1a1 1 0 100-2v-3a1 1 0 00-1-1H9z" clip-rule="evenodd"/>
                                </svg>
                            </div>
                            <div class="ml-3">
                                <p class="text-sm text-blue-700">
                                    Note: This transaction will be saved as <strong>Pending</strong>. Proof of installation will be required later in the <strong>Pending Issues</strong> menu.
                                </p>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- ACTIONS -->
                <div class="mt-8 mb-12 flex justify-end gap-4">
                    <a href="{{ route('dashboard') }}" class="inline-flex items-center px-8 py-4 bg-gray-200 border border-gray-300 rounded-md font-bold text-gray-700 text-lg hover:bg-gray-300 transition shadow-md">
                        Done
                    </a>
                    <button type="submit" class="inline-flex items-center px-8 py-4 bg-blue-700 border border-transparent rounded-md font-bold text-white text-lg hover:bg-blue-800 transition shadow-md">
                        <svg class="w-6 h-6 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path></svg>
                        Submit Issue
                    </button>
                </div>
            </form>
        </div>
    </div>

    <!-- JAVASCRIPT LOGIC -->
    <script>

        // SEARCHABLE DROPDOWN FACILITY
        const vehicleSearch = document.getElementById('vehicle_search');
        const vehicleDropdown = document.getElementById('vehicle_dropdown');
        const hiddenVehicleId = document.getElementById('hidden_vehicle_id');
        const vehicleOptions = document.querySelectorAll('.vehicle-option');

        if (vehicleSearch) {
            vehicleSearch.addEventListener('focus', () => {
                vehicleDropdown.classList.remove('hidden');
            });

            vehicleSearch.addEventListener('input', function() {
                const filter = this.value.toLowerCase();
                vehicleDropdown.classList.remove('hidden');
                
                vehicleOptions.forEach(option => {
                    const name = option.getAttribute('data-name').toLowerCase();
                    option.style.display = name.includes(filter) ? 'block' : 'none';
                });
            });

            vehicleOptions.forEach(option => {
                option.addEventListener('click', function() {
                    vehicleSearch.value = this.getAttribute('data-name');
                    hiddenVehicleId.value = this.getAttribute('data-id');
                    vehicleDropdown.classList.add('hidden');
                });
            });
        }

        // AUTOCOMPLETE MECHANIC (LOCAL STORAGE)
        const mechanicInput = document.getElementById('mechanic_input');
        const mechanicDropdown = document.getElementById('mechanic_dropdown');
        const giForm = document.getElementById('giForm'); 
        let savedMechanics = JSON.parse(localStorage.getItem('savedMechanics')) || [];

        function renderMechanicDropdown(filterText = '') {
            mechanicDropdown.innerHTML = ''; 
            const filtered = savedMechanics.filter(name => name.toLowerCase().includes(filterText.toLowerCase()));
            
            if (filtered.length > 0) {
                filtered.forEach(name => {
                    const div = document.createElement('div');
                    div.className = "px-4 py-2 hover:bg-blue-50 cursor-pointer text-sm text-gray-800 border-b last:border-b-0";
                    div.textContent = name;
                    div.onclick = function() {
                        mechanicInput.value = name;
                        mechanicDropdown.classList.add('hidden');
                    };
                    mechanicDropdown.appendChild(div);
                });
            } else {
                const div = document.createElement('div');
                div.className = "px-4 py-2 text-sm text-gray-400 italic";
                div.textContent = "Type new name...";
                mechanicDropdown.appendChild(div);
            }
        }

        if (mechanicInput && mechanicDropdown) {
            // Tampilkan custom dropdown saat input diklik/fokus
            mechanicInput.addEventListener('focus', () => {
                renderMechanicDropdown(mechanicInput.value);
                mechanicDropdown.classList.remove('hidden');
            });

            // Filter isi dropdown saat user mengetik
            mechanicInput.addEventListener('input', () => {
                renderMechanicDropdown(mechanicInput.value);
                mechanicDropdown.classList.remove('hidden');
            });

            // Simpan nama baru ke LocalStorage saat tombol Submit ditekan
            if (giForm) {
                giForm.addEventListener('submit', function(e) {
                    const newName = mechanicInput.value.trim();
                    if (newName && !savedMechanics.includes(newName)) {
                        savedMechanics.push(newName);
                        localStorage.setItem('savedMechanics', JSON.stringify(savedMechanics));
                    }
                });
            }
        }

        // --- 1. LIVE SEARCH LOGIC ---
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
                                div.className = "px-4 py-3 hover:bg-blue-50 cursor-pointer border-b last:border-b-0";
                                
                                // Logic warna stok (Merah jika 0)
                                const stockClass = item.current_stock <= 0 ? 'text-red-600 font-bold' : 'text-green-600 font-bold';
                                
                                div.innerHTML = `
                                    <div class="flex justify-between items-center">
                                        <div>
                                            <div class="font-bold text-gray-800">${item.description}</div>
                                            <div class="text-xs text-gray-500">${item.part_number || '-'}</div>
                                        </div>
                                        <div class="text-right text-xs">
                                            Stock: <span class="${stockClass}">${item.current_stock}</span> ${item.unit}
                                        </div>
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

        // Hide results if clicked outside (Diperbarui untuk mengakomodasi Dropdown Facility juga)
        document.addEventListener('click', function(e) {
            if (searchInput && resultsBox && !searchInput.contains(e.target) && !resultsBox.contains(e.target)) {
                resultsBox.classList.add('hidden');
            }
            
            if (vehicleSearch && vehicleDropdown && !vehicleSearch.contains(e.target) && !vehicleDropdown.contains(e.target)) {
                vehicleDropdown.classList.add('hidden');
            }

            // Tambahan untuk klik luar area Mechanic Dropdown
            if (mechanicInput && mechanicDropdown && !mechanicInput.contains(e.target) && !mechanicDropdown.contains(e.target)) {
                mechanicDropdown.classList.add('hidden');
            }
        });

        function selectItem(item) {
            document.getElementById('selected_part_name').value = item.description;
            document.getElementById('selected_part_id').value = item.id;
            document.getElementById('selected_part_number').value = item.part_number || '-';
            document.getElementById('selected_part_category').value = item.category || '';

            document.getElementById('unit_display').textContent = item.unit;
            document.getElementById('input_qty').focus();

            searchInput.value = '';
            resultsBox.classList.add('hidden');
        }

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
                if (item.category === 'rotating') {
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

        // --- 2. FORCE SUBMIT (5 DAYS RULE) ---
        function submitForce() {
            // Set hidden input value to 1
            document.getElementById('force_submit_input').value = '1';
            // Submit form again
            document.getElementById('giForm').submit();
        }

            // --- VALIDASI TAMBAHAN FRONTEND UNTUK SPAREPART ---
        function validateGiForm() {

            // Cek apakah facility valid
            const hiddenVehicleId = document.getElementById('hidden_vehicle_id').value;
            if(!hiddenVehicleId || hiddenVehicleId === '') {
                alert('Warning! Please select a valid Facility from the dropdown list.');
                document.getElementById('vehicle_search').focus();
                return false;
            }

            if (cartItems.length === 0) {
                alert('Warning! No parts added for submission. Please add minimum of 1 sparepart to proceed.');
                document.getElementById('part_search').focus();
                return false;
            }
            
            return true; // Lanjutkan submit jika sudah dipilih
        }

        // --- 3. AUTO CLOSE SUCCESS ---
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

        // --- 4. AUTO-FILL DARI TOMBOL ISSUE (ADMIN) ---
        // Jika Controller mengirimkan data $preselectedPart, otomatis jalankan selectItem
        @if(isset($preselectedPart) && $preselectedPart)
            document.addEventListener('DOMContentLoaded', function() {
                const preItem = {
                    id: "{{ $preselectedPart->id }}",
                    description: "{{ $preselectedPart->description }}",
                    part_number: "{{ $preselectedPart->part_number ?? '-' }}",
                    unit: "{{ $preselectedPart->unit }}",
                    current_stock: {{ $preselectedPart->current_stock }}
                };
                selectItem(preItem);
            });
        @endif
    </script>
</x-app-layout>