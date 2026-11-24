<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-blue-700 leading-tight">
            {{ __('Pending Good Receipt & Purchase Order Assignment') }}
        </h2>
    </x-slot>

    <div class="py-12 bg-gray-50 relative">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            
            <!-- ALERT SECTION -->
            <div id="alertContainer">
                @if(session('success'))
                    <div class="mb-6 bg-white border-l-4 border-green-600 p-4 shadow-sm rounded-r-md flex items-center">
                        <svg class="h-6 w-6 text-green-600 mr-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path></svg>
                        <div><p class="font-bold text-gray-800">Success</p><p class="text-sm text-gray-600">{{ session('success') }}</p></div>
                    </div>
                @endif
                @if(session('error'))
                    <div class="mb-6 bg-white border-l-4 border-red-600 p-4 shadow-sm rounded-r-md flex items-center">
                        <svg class="h-6 w-6 text-red-600 mr-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
                        <div><p class="font-bold text-gray-800">Error</p><p class="text-sm text-gray-600">{{ session('error') }}</p></div>
                    </div>
                @endif
            </div>

            <!-- FORM CONTAINER -->
            <form id="pendingGrForm" action="{{ route('transactions.pendingGR.process') }}" method="POST" enctype="multipart/form-data">
                @csrf
                <input type="hidden" name="replace_bast" id="replace_bast" value="0">

                <!-- 1. INPUT AREA (STICKY TOP) -->
                <div class="bg-white p-6 rounded-lg shadow-md border-t-4 border-blue-700 mb-8 sticky top-4 z-30">
                    <div class="flex flex-col md:flex-row gap-4 items-start">
                        
                        <!-- PO Input -->
                        <div class="w-full md:w-1/4">
                            <label class="block text-sm font-bold text-gray-700 mb-2">Purchase Order No. (PO)</label>
                            <input type="text" name="purchase_order" id="purchase_order" 
                                class="w-full rounded-md border-gray-300 shadow-sm focus:border-blue-700 focus:ring-blue-700 uppercase" 
                                placeholder="e.g. PO25102026" required>
                            <p class="text-[10px] text-gray-500 mt-1">Enter existing PO to merge, or new PO to create.</p>
                        </div>

                        <!-- New Date Input -->
                        <div class="w-full md:w-1/4">
                            <label class="block text-sm font-bold text-gray-700 mb-2">Transaction Date <span class="text-gray-400 font-normal">(Optional)</span></label>
                            <input type="date" name="new_transaction_date" id="new_transaction_date" 
                                class="w-full rounded-md border-gray-300 shadow-sm focus:border-blue-700 focus:ring-blue-700">
                            <p class="text-[10px] text-gray-500 mt-1">Updates all selected items to this date.</p>
                        </div>

                        <!-- BAST Upload -->
                        <div class="w-full md:w-1/4">
                            <label class="block text-sm font-bold text-gray-700 mb-2">BAST Document</label>
                            <div class="flex items-center gap-2">
                                <label for="bast_image" class="flex-grow flex items-center justify-center px-4 py-2 border border-gray-300 rounded-md shadow-sm text-sm font-medium text-gray-700 bg-white hover:bg-gray-50 cursor-pointer group">
                                    <svg class="w-5 h-5 mr-2 text-gray-400 group-hover:text-blue-700 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-8l-4-4m0 0L8 8m4-4v12"></path></svg>
                                    <span id="fileNameLabel" class="truncate max-w-[80px] xl:max-w-[100px]">Upload / Paste</span>
                                    <input id="bast_image" name="bast_image" type="file" class="sr-only" accept=".jpg,.jpeg,.png,.pdf" onchange="validateAndUpdateFileName(this)">
                                </label>
                                <!-- Camera Button -->
                                <button type="button" onclick="openCameraModal()" class="flex-shrink-0 p-2 bg-blue-100 text-blue-700 rounded-md hover:bg-blue-200">
                                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 9a2 2 0 012-2h.93a2 2 0 001.664-.89l.812-1.22A2 2 0 0110.07 4h3.86a2 2 0 011.664.89l.812 1.22A2 2 0 0018.07 7H19a2 2 0 012 2v9a2 2 0 01-2 2H5a2 2 0 01-2-2V9z"></path><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 13a3 3 0 11-6 0 3 3 0 016 0z"></path></svg>
                                </button>
                            </div>
                            
                            <!-- Preview Image -->
                            <img id="imagePreview" class="hidden mt-2 h-20 w-auto object-contain rounded border border-gray-200" />
                            
                            <!-- BARU: PDF Preview Link -->
                            <a id="pdfPreviewLink" href="#" target="_blank" class="hidden mt-2 inline-flex items-center text-sm text-blue-600 hover:text-blue-800 hover:underline font-bold transition-colors">
                                <svg class="w-4 h-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 21h7a2 2 0 002-2V9.414a1 1 0 00-.293-.707l-5.414-5.414A1 1 0 0012.586 3H7a2 2 0 00-2 2v11m0 5l4.879-4.879m0 0a3 3 0 104.243-4.242 3 3 0 00-4.243 4.242z"></path></svg>
                                Preview PDF
                            </a>
                        </div>

                        <!-- Submit Button -->
                        <div class="w-full md:w-1/4">
                            <label class="block text-sm font-bold text-transparent mb-2 select-none">Action</label>
                            
                            <button type="button" onclick="validateAndSubmit()" 
                                class="w-full inline-flex justify-center items-center px-4 py-2 bg-blue-700 border border-transparent rounded-md font-bold text-white hover:bg-blue-800 focus:outline-none transition shadow-md">
                                <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
                                Process Selected
                            </button>
                        </div>
                    </div>
                </div>

                <!-- DIVIDER & TITLE -->
                <div class="mb-6 border-b-2 border-gray-200 pb-2">
                    <h3 class="text-lg font-bold text-gray-700">
                        Select GR Transactions to include in this Purchase Order
                    </h3>
                </div>

                <!-- 2. LIST OF TRANSACTIONS (VERTICAL STACK) -->
                @if($pendingGR->isEmpty())
                    <div class="text-center py-10 bg-white rounded-lg shadow-sm border border-gray-200">
                        <svg class="h-16 w-16 text-gray-300 mx-auto mb-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path></svg>
                        <p class="text-lg font-medium text-gray-500">No pending receipts found.</p>
                        <p class="text-sm text-gray-400">All procurement items have been assigned to PO.</p>
                    </div>
                @else
                    <!-- Container Stack (Vertical) -->
                    <div class="space-y-4">
                        <!-- Grouping by Transaction ID -->
                        @foreach($pendingGR->groupBy('trx_id') as $trxId => $items)
                            @php $header = $items->first(); @endphp
                            
                            <!-- Card Item (Horizontal Layout) -->
                            <div class="bg-white rounded-lg shadow-sm border border-gray-200 hover:shadow-md hover:border-blue-300 transition duration-150 relative group">
                                <div class="p-4 flex flex-col md:flex-row items-start md:items-center justify-between gap-4">
                                    
                                    <!-- A. Checkbox & Invoice Info -->
                                    <div class="flex items-center gap-4 w-full md:w-1/4 min-w-0">
                                        @if(auth()->user()->role === 'supervisor')
                                            <div class="flex-shrink-0">
                                                <button type="button" onclick="deletePendingGR('{{ $trxId }}')" class="text-gray-400 hover:text-red-600 transition" title="Cancel & Revert Stock">
                                                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"></path></svg>
                                                </button>
                                            </div>
                                        @endif
                                        <div class="flex-shrink-0">
                                            <input type="checkbox" name="selected_ids[]" value="{{ $trxId }}" 
                                                data-vendor="{{ $header->vendor_name }}"
                                                class="h-5 w-5 text-blue-600 focus:ring-blue-500 border-gray-300 rounded cursor-pointer transition">
                                        </div>
                                        <div class="min-w-0">
                                            <div class="text-sm font-bold text-blue-700 truncate" title="Invoice: {{ $header->invoice_no }}">
                                                {{ $header->invoice_no }}
                                            </div>
                                            <div class="text-[12px] text-gray-500 flex items-center mt-0.5">
                                                <svg class="w-3 h-3 mr-1 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"></path></svg>
                                                {{ \Carbon\Carbon::parse($header->date)->format('d M Y') }}
                                                
                                                <span class="ml-1 border-l pl-1 border-gray-300">{{ \Carbon\Carbon::parse($header->created_at)->format('H:i') }}</span>
                                            </div>
                                        </div>
                                    </div>

                                    <!-- B. Vendor Info -->
                                    <div class="w-full md:w-1/4 min-w-0 border-l border-gray-100 pl-0 md:pl-4">
                                        <p class="text-[10px] text-gray-400 uppercase font-bold mb-0.5">Vendor</p>
                                        <p class="text-sm font-medium text-gray-900 truncate" title="{{ $header->vendor_name }}">
                                            {{ $header->vendor_name }}
                                        </p>
                                    </div>

                                    <!-- C. Items List (Preview) -->
                                    <div class="w-full md:w-2/5 min-w-0 border-l border-gray-100 pl-0 md:pl-4" x-data="{ expanded: false }">
                                        <p class="text-[10px] text-gray-400 uppercase font-bold mb-1">
                                            Items ({{ $items->count() }})
                                        </p>
                                        <div class="space-y-1">
                                            @foreach($items as $index => $item)
                                                <div class="flex justify-between items-center text-sm" 
                                                     @if($index >= 2) x-show="expanded" style="display: none;" @endif>
                                                    <span class="text-gray-700 truncate pr-2 w-3/4" title="{{ $item->item_name }}">{{ $item->item_name }}</span>
                                                    <span class="font-bold text-blue-700 text-xs whitespace-nowrap bg-blue-50 px-1.5 py-0.5 rounded">
                                                        {{ $item->qty }} {{ $item->unit }}
                                                    </span>
                                                </div>
                                            @endforeach
                                            
                                            @if($items->count() > 2)
                                                <div class="mt-1">
                                                    <button type="button" @click="expanded = true" x-show="!expanded" class="text-xs text-blue-600 hover:text-blue-800 hover:underline italic focus:outline-none">
                                                        + {{ $items->count() - 2 }} more items...
                                                    </button>
                                                    <button type="button" @click="expanded = false" x-show="expanded" style="display: none;" class="text-xs text-gray-500 hover:text-gray-700 hover:underline italic focus:outline-none">
                                                        Show less
                                                    </button>
                                                </div>
                                            @endif
                                        </div>
                                    </div>
                                </div>
                            </div>
                        @endforeach
                    </div>
                @endif
            </form>

            <!-- Hidden Delete Form -->
            <form id="deleteForm" method="POST" style="display: none;">
                @csrf
                @method('DELETE')
            </form>

        </div>
    </div>

    <!-- --- CAMERA MODAL --- -->
    <div id="cameraModal" class="fixed inset-0 z-[60] hidden bg-black bg-opacity-75 flex items-center justify-center">
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

    <!-- --- CONFIRMATION MODAL (HIDDEN) --- -->
    <div id="confirmModal" class="fixed inset-0 z-50 hidden bg-black bg-opacity-50 flex items-center justify-center">
        <div class="bg-white rounded-lg shadow-xl p-6 max-w-md w-full mx-4">
            <h3 class="text-lg font-bold text-gray-900 mb-2" id="confirmTitle">Confirm Action</h3>
            <p class="text-gray-600 mb-6" id="confirmMessage">Message goes here...</p>
            
            <!-- Actions for Replace BAST -->
            <div id="replaceActions" class="hidden flex justify-end gap-3">
                <button type="button" onclick="submitForm(0)" class="px-4 py-2 bg-gray-200 text-gray-800 rounded hover:bg-gray-300 font-semibold">Keep Previous BAST</button>
                <button type="button" onclick="submitForm(1)" class="px-4 py-2 bg-blue-700 text-white rounded hover:bg-blue-800 font-semibold">Replace BAST</button>
            </div>

            <!-- Actions for Error/Alert (OK only) -->
            <div id="alertActions" class="hidden flex justify-end">
                <button type="button" id="btnAlertOk" onclick="closeConfirmModal()" class="px-4 py-2 bg-blue-700 text-white rounded hover:bg-blue-800 font-semibold w-24">OK</button>
            </div>
        </div>
    </div>

    <!-- --- PREVIEW MODAL (NEW) --- -->
    <div id="previewModal" class="fixed inset-0 z-[55] hidden bg-black bg-opacity-50 flex items-center justify-center">
        <div class="bg-white rounded-lg shadow-xl p-6 max-w-4xl w-full mx-4 max-h-[80vh] flex flex-col">
            <h3 class="text-lg font-bold text-gray-900 mb-4">Confirm PO Assignment</h3>
            <div class="mb-4">
                <p class="text-sm text-gray-600">The following items will be assigned to <strong>Purchase Order: <span id="previewPONumber" class="text-blue-700"></span></strong></p>
            </div>
            
            <div class="overflow-y-auto flex-grow border border-gray-200 rounded-lg">
                <table class="min-w-full divide-y divide-gray-200">
                    <thead class="bg-gray-50 sticky top-0">
                        <tr>
                            <th class="px-4 py-2 text-left text-xs font-bold text-gray-500 uppercase">Part Info</th>
                            <th class="px-4 py-2 text-left text-xs font-bold text-gray-500 uppercase">Invoice & Date</th>
                            <th class="px-4 py-2 text-center text-xs font-bold text-gray-500 uppercase">Qty</th>
                        </tr>
                    </thead>
                    <tbody id="previewTableBody" class="bg-white divide-y divide-gray-200">
                        <!-- Dynamic Rows -->
                    </tbody>
                </table>
            </div>

            <div class="mt-6 flex justify-end gap-3 pt-4 border-t border-gray-100">
                <button type="button" onclick="document.getElementById('previewModal').classList.add('hidden')" class="px-4 py-2 bg-gray-200 text-gray-800 rounded hover:bg-gray-300 font-semibold">Cancel</button>
                <button type="button" onclick="confirmSubmission()" class="px-4 py-2 bg-green-600 text-white rounded hover:bg-green-700 font-semibold">Confirm & Process</button>
            </div>
        </div>
    </div>

    <!-- --- SUCCESS MODAL (NEW) --- -->
    <div id="successModal" class="fixed inset-0 z-50 hidden bg-black bg-opacity-50 flex items-center justify-center">
        <div class="bg-white rounded-lg shadow-xl p-6 max-w-md w-full mx-4 flex flex-col items-center">
            <div class="rounded-full bg-green-100 p-3 mb-4">
                <svg class="h-10 w-10 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path>
                </svg>
            </div>
            <h3 class="text-lg font-bold text-gray-900 mb-2">Success!</h3>
            <p class="text-gray-600 mb-6 text-center" id="successMessage">PO Assignment Success</p>
            
            <div class="flex justify-center w-full">
                <button type="button" id="btnSuccessOk" onclick="closeSuccessModal()" class="px-6 py-2 bg-green-600 text-white rounded hover:bg-green-700 font-semibold w-full sm:w-auto">OK</button>
            </div>
        </div>
    </div>

    <!-- SCRIPTS -->
    <script>
        // Prepare data for JS
        const pendingTransactionsData = @json($pendingGR->groupBy('trx_id'));

        // --- 0. SUCCESS POPUP HANDLER (FROM SESSION) ---
        document.addEventListener('DOMContentLoaded', function() {
            @if(session('success'))
                showSuccessModal("{{ session('success') }}");
            @endif
        });

        function showSuccessModal(message) {
            const modal = document.getElementById('successModal');
            document.getElementById('successMessage').innerText = message;
            modal.classList.remove('hidden');
        }

        function closeSuccessModal() {
            document.getElementById('successModal').classList.add('hidden');
        }

        // --- GLOBAL KEYDOWN LISTENER FOR ENTER KEY ---
        document.addEventListener('keydown', function(event) {
            if (event.key === 'Enter') {
                const confirmModal = document.getElementById('confirmModal');
                if (!confirmModal.classList.contains('hidden')) {
                    const alertActions = document.getElementById('alertActions');
                    if (!alertActions.classList.contains('hidden')) {
                         event.preventDefault();
                         closeConfirmModal();
                    }
                }
                const successModal = document.getElementById('successModal');
                if (!successModal.classList.contains('hidden')) {
                     event.preventDefault();
                     closeSuccessModal();
                }
            }
        });

        // --- 1. MAIN VALIDATION LOGIC ---
        async function validateAndSubmit() {
            const poInput = document.getElementById('purchase_order').value.trim();
            const bastInput = document.getElementById('bast_image');
            const checkboxes = document.querySelectorAll('input[name="selected_ids[]"]:checked');

            // Basic Validation
            if (!poInput) {
                alert("Please enter Purchase Order No.");
                return;
            }
            if (checkboxes.length === 0) {
                alert("Please select at least one transaction to process.");
                return;
            }

            // --- 1A. VALIDASI VENDOR (Client-Side) ---
            const vendors = new Set();
            checkboxes.forEach(chk => {
                vendors.add(chk.getAttribute('data-vendor'));
            });

            if (vendors.size > 1) {
                showModal('alert', 'Cannot add transactions with multiple vendors to one Purchase Order.');
                return;
            }
            
            // Simpan vendor yang dipilih untuk cek API
            const currentVendor = vendors.values().next().value;

            // AJAX Check PO Existence
            try {
                const response = await fetch(`{{ url('/api/check-po') }}?purchase_order=${poInput}`);
                const data = await response.json();
                
                const poExists = data.exists;
                const poVendor = data.vendor;
                const hasNewImage = bastInput.files.length > 0;

                // --- 1B. VALIDASI VENDOR EXISTING PO ---
                if (poExists && poVendor && poVendor !== currentVendor) {
                     showModal('alert', `Vendor Mismatch! Existing PO '${poInput}' belongs to '${poVendor}', but selected items are from '${currentVendor}'.`);
                     return;
                }

                // Scenario 1: PO New + No Image
                if (!poExists && !hasNewImage) {
                    showModal('alert', 'Purchase Order doesn\'t exist, please upload BAST to submit new PO.');
                    return;
                }

                // Scenario 2: PO Exists + Has New Image -> Ask to Replace
                if (poExists && hasNewImage) {
                    showModal('replace', 'Purchase Order already exists. Do you want to replace BAST with newly uploaded image?');
                    return;
                }

                // Scenario 3: PO Exists + No Image -> Proceed (Keep old)
                // Scenario 4: PO New + Has Image -> Proceed (New)
                preparePreview(0);

            } catch (error) {
                console.error("Error checking PO:", error);
                alert("System error checking PO status.");
            }
        }

        function showModal(type, message) {
            const modal = document.getElementById('confirmModal');
            document.getElementById('confirmMessage').innerText = message;
            
            document.getElementById('replaceActions').classList.add('hidden');
            document.getElementById('alertActions').classList.add('hidden');

            if (type === 'replace') {
                document.getElementById('confirmTitle').innerText = "PO Exists";
                document.getElementById('replaceActions').classList.remove('hidden');
            } else {
                document.getElementById('confirmTitle').innerText = "Action Required";
                document.getElementById('alertActions').classList.remove('hidden');
                setTimeout(() => document.getElementById('btnAlertOk').focus(), 100);
            }
            modal.classList.remove('hidden');
        }

        function closeConfirmModal() {
            document.getElementById('confirmModal').classList.add('hidden');
        }

        function submitForm(replaceBastValue) {
            document.getElementById('replace_bast').value = replaceBastValue;
            document.getElementById('pendingGrForm').submit();
        }

        // --- NEW: DELETE FUNCTION (SUPERVISOR ONLY) ---
        function deletePendingGR(id) {
            if(confirm('Cancel this GR? Stock will be reverted.')) {
                const form = document.getElementById('deleteForm');
                
                // Cek apakah form ditemukan sebelum set action
                if (form) {
                    form.action = "{{ url('/transactions/pending-gr') }}/" + id;
                    form.submit();
                } else {
                    console.error("Error: Element #deleteForm not found!");
                    alert("System Error: Delete form is missing from the page.");
                }
            }
        }

        // --- PREVIEW LOGIC (UPDATED WITH OVERRIDE DATE) ---
        function preparePreview(replaceBastValue) {
            document.getElementById('replace_bast').value = replaceBastValue;
            closeConfirmModal(); // Close confirm modal if open
            
            const poInput = document.getElementById('purchase_order').value.trim();
            const newDateInput = document.getElementById('new_transaction_date').value; 
            document.getElementById('previewPONumber').innerText = poInput;
            
            const checkboxes = document.querySelectorAll('input[name="selected_ids[]"]:checked');
            const tbody = document.getElementById('previewTableBody');
            tbody.innerHTML = '';

            checkboxes.forEach(chk => {
                const trxId = chk.value;
                const items = pendingTransactionsData[trxId]; 
                
                if (items) {
                    items.forEach(item => {
                         const dateObj = newDateInput ? new Date(newDateInput) : new Date(item.date);
                         const dateStr = dateObj.toLocaleDateString('en-GB', { day: '2-digit', month: 'short', year: 'numeric' }); 
                         
                         const createdObj = new Date(item.created_at);
                         const timeStr = createdObj.toLocaleTimeString('en-GB', { hour: '2-digit', minute: '2-digit', hour12: false }); 

                         const tr = document.createElement('tr');
                         tr.className = "hover:bg-gray-50";
                         tr.innerHTML = `
                            <td class="px-4 py-2 text-sm text-gray-900 truncate max-w-xs" title="${item.item_name}">${item.item_name}</td>
                            <td class="px-4 py-2 text-sm text-gray-500 font-mono">
                                ${item.invoice_no}
                                <div class="text-[12px] text-gray-500 mt-0.5">
                                    ${dateStr} <span class="mx-1 border-l border-gray-300"></span> ${timeStr}
                                </div>
                            </td>
                            <td class="px-4 py-2 text-center text-sm font-bold text-blue-700 whitespace-nowrap">${item.qty} ${item.unit}</td>
                         `;
                         tbody.appendChild(tr);
                    });
                }
            });

            document.getElementById('previewModal').classList.remove('hidden');
        }

        function confirmSubmission() {
            document.getElementById('pendingGrForm').submit();
        }

        // --- 2. CAMERA & FILE LOGIC (REUSED) ---
        let videoStream;
        async function openCameraModal() {
            const modal = document.getElementById('cameraModal');
            const video = document.getElementById('cameraVideo');
            modal.classList.remove('hidden');
            try {
                videoStream = await navigator.mediaDevices.getUserMedia({ video: { facingMode: 'environment' } });
                video.srcObject = videoStream;
            } catch (err) {
                alert("Cannot access camera: " + err.message); closeCameraModal(); 
            }
        }
        function stopCamera() {
            const modal = document.getElementById('cameraModal');
            const video = document.getElementById('cameraVideo');
            modal.classList.add('hidden');
            if (videoStream) videoStream.getTracks().forEach(track => track.stop());
            video.srcObject = null;
        }
        function capturePhoto() {
            const video = document.getElementById('cameraVideo');
            const canvas = document.createElement('canvas');
            canvas.width = video.videoWidth; canvas.height = video.videoHeight;
            canvas.getContext('2d').drawImage(video, 0, 0);
            canvas.toBlob(function(blob) {
                const file = new File([blob], "camera_capture_" + Date.now() + ".jpg", { type: "image/jpeg" });
                const input = document.getElementById('bast_image');
                const dt = new DataTransfer(); dt.items.add(file); input.files = dt.files;
                validateAndUpdateFileName(input);
                stopCamera();
            }, 'image/jpeg', 0.9);
        }
        
        function validateAndUpdateFileName(input) {
            const label = document.getElementById('fileNameLabel');
            const preview = document.getElementById('imagePreview');
            const pdfLink = document.getElementById('pdfPreviewLink'); // Ambil elemen link PDF

            if (input.files && input.files.length > 0) {
                const file = input.files[0];
                if(file.size/1024/1024 > 5) { alert('Max 5MB!'); input.value=''; return; }
                label.textContent = file.name;
                label.classList.add('text-blue-700', 'font-bold');
                
                // BARU: Cek jika PDF, sembunyikan gambar dan tampilkan link Preview PDF
                if (file.type === 'application/pdf') {
                    preview.classList.add('hidden'); // Sembunyikan tag img
                    
                    // Buat blob URL sementara dari file yang dipilih dan setel ke href
                    pdfLink.href = URL.createObjectURL(file);
                    pdfLink.classList.remove('hidden'); // Tampilkan Link Preview
                } else {
                    const reader = new FileReader();
                    reader.onload = function(e) { 
                        preview.src = e.target.result; 
                        preview.classList.remove('hidden'); 
                    }
                    reader.readAsDataURL(file);
                    
                    // Sembunyikan Link PDF jika yang diupload adalah gambar
                    pdfLink.classList.add('hidden');
                }
            } else {
                // Jika input kosong/direset
                preview.classList.add('hidden');
                pdfLink.classList.add('hidden');
            }
        }
        
        // Paste Support
        document.addEventListener('paste', function(e) {
            const items = e.clipboardData.items;
            for (let i = 0; i < items.length; i++) {
                if (items[i].type.indexOf('image') !== -1) {
                    const blob = items[i].getAsFile();
                    const file = new File([blob], "pasted_bast_" + Date.now() + ".png", { type: blob.type });
                    const input = document.getElementById('bast_image');
                    const dt = new DataTransfer(); dt.items.add(file); input.files = dt.files;
                    validateAndUpdateFileName(input);
                }
            }
        });

        // --- 3. PREVENT ENTER KEY SUBMISSION & TRIGGER VALIDATION ---
        document.getElementById('purchase_order').addEventListener('keydown', function(event) {
            if (event.key === 'Enter') {
                event.preventDefault(); // Stop form submit default
                validateAndSubmit(); // Jalankan validasi JS kita
            }
        });
    </script>
</x-app-layout>