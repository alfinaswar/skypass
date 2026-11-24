<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-blue-700 leading-tight">
            {{ __('Pending GI') }}
        </h2>
    </x-slot>

    <div class="py-12 bg-gray-50">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            
            <!-- ALERT SECTION -->
            @if(session('success'))
                <div class="mb-6 bg-white border-l-4 border-green-600 p-4 shadow-sm rounded-r-md flex items-center">
                    <svg class="h-6 w-6 text-green-600 mr-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path></svg>
                    <div>
                        <p class="font-bold text-gray-800">Success</p>
                        <p class="text-sm text-gray-600">{{ session('success') }}</p>
                    </div>
                </div>
            @endif

            @if(session('error'))
                <div class="mb-6 bg-white border-l-4 border-red-600 p-4 shadow-sm rounded-r-md flex items-center">
                    <svg class="h-6 w-6 text-red-600 mr-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
                    <div>
                        <p class="font-bold text-gray-800">Error</p>
                        <p class="text-sm text-gray-600">{{ session('error') }}</p>
                    </div>
                </div>
            @endif

            <!-- --- CAMERA MODAL (Hidden by Default) --- -->
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

            <!-- TABLE CONTAINER -->
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg border-t-4 border-yellow-500">
                <div class="p-6 bg-white border-b border-gray-200">
                    <div class="overflow-x-auto">
                        <table class="min-w-full divide-y divide-gray-200">
                            <thead class="bg-gray-50">
                                <tr>
                                    <th class="px-6 py-3 text-left text-xs font-bold text-gray-500 uppercase tracking-wider">Date</th>
                                    <!-- <th class="px-6 py-3 text-center text-xs font-bold text-gray-500 uppercase tracking-wider">Invoice</th> -->
                                    <th class="px-6 py-3 text-center text-xs font-bold text-gray-500 uppercase tracking-wider">Requester</th>
                                    <th class="px-6 py-3 text-left text-xs font-bold text-gray-500 uppercase tracking-wider">Part Info</th>
                                    <th class="px-6 py-3 text-center text-xs font-bold text-gray-500 uppercase tracking-wider">Qty</th>
                                    <th class="px-6 py-3 text-right text-xs font-bold text-gray-500 uppercase tracking-wider">Actions</th>
                                </tr>
                            </thead>
                            <tbody class="bg-white divide-y divide-gray-200">
                                @forelse($pendingTransactions as $trx)
                                    <tr class="hover:bg-gray-50 transition-colors">
                                        <!-- Date -->
                                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                                            {{ \Carbon\Carbon::parse($trx->date)->format('d M Y') }}
                                            <div class="text-xs text-gray-400">{{ \Carbon\Carbon::parse($trx->created_at)->format('H:i') }}</div>
                                        </td>
                                        
                                        <!-- Invoice
                                        <td class="px-6 py-4 whitespace-nowrap text-sm font-mono text-gray-500">
                                            {{ $trx->invoice_no }}
                                        </td> -->

                                        <!-- Requester / Vehicle -->
                                        <td class="px-6 py-4 text-center">
                                            <div class="text-sm font-bold text-gray-900">{{ $trx->requester_name }}</div>
                                            <div class="text-xs text-blue-600 font-bold bg-blue-50 px-2 py-0.5 rounded-full inline-block mt-1 mx-auto">
                                                {{ $trx->fleet_number }}
                                            </div>
                                        </td>

                                        <!-- Part Info -->
                                        <td class="px-6 py-4">
                                            <div class="text-sm text-gray-900 font-medium">{{ $trx->part_name }}</div>
                                        </td>

                                        <!-- Qty -->
                                        <td class="px-6 py-4 text-center whitespace-nowrap">
                                            <span class="px-3 py-1 inline-flex text-sm leading-5 font-bold rounded-full bg-yellow-100 text-yellow-800 whitespace-nowrap">
                                                {{ $trx->qty }} {{ $trx->unit }}
                                            </span>
                                        </td>

                                        <!-- Actions -->
                                        <td class="px-6 py-4 text-right text-sm font-medium space-x-2">
                                            <!-- Tombol Verify (Upload Proof) -->
                                            <button onclick="openVerifyModal('{{ $trx->id }}', '{{ $trx->invoice_no }}')" 
                                                class="text-blue-600 hover:text-blue-900 font-bold focus:outline-none transition">
                                                Upload Proof
                                            </button>
                                            
                                            <!-- Tombol Cancel (Supervisor Only) -->
                                            @if(auth()->user()->role === 'supervisor')
                                                <button onclick="openCancelModal('{{ $trx->id }}', '{{ $trx->invoice_no }}')" 
                                                    class="text-red-600 hover:text-red-900 font-bold ml-3 focus:outline-none transition">
                                                    Cancel
                                                </button>
                                            @else
                                                <!-- Disabled Button for Non-Supervisor -->
                                                <button disabled class="text-gray-400 font-bold ml-3 cursor-not-allowed" title="Only Supervisor can cancel">
                                                    Cancel
                                                </button>
                                            @endif
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="6" class="px-6 py-10 text-center text-gray-500">
                                            <div class="flex flex-col items-center justify-center">
                                                <svg class="h-12 w-12 text-gray-300 mb-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
                                                <p class="text-lg font-medium">No pending GI found.</p>
                                                <p class="text-sm mt-1">All GI transactions have been verified.</p>
                                            </div>
                                        </td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- --- MODAL VERIFY (UPLOAD PROOF) --- -->
    <div id="verifyModal" class="fixed inset-0 z-50 hidden overflow-y-auto" aria-labelledby="modal-title" role="dialog" aria-modal="true">
        <div class="fixed inset-0 bg-gray-500 bg-opacity-75 transition-opacity" onclick="closeVerifyModal()"></div>

        <div class="flex min-h-full items-end justify-center p-4 text-center sm:items-center sm:p-0">
            <!-- Diperlebar dari sm:max-w-lg menjadi sm:max-w-2xl agar muat 2 kotak -->
            <div class="relative transform overflow-hidden rounded-lg bg-white text-left shadow-xl transition-all sm:my-8 sm:w-full sm:max-w-2xl">
                <form id="verifyForm" method="POST" enctype="multipart/form-data">
                    @csrf
                    @method('PUT')
                    <input type="hidden" name="action" value="verify">
                    
                    <div class="bg-white p-6">
                        <div class="sm:flex sm:items-start">
                            <div class="mx-auto flex h-12 w-12 flex-shrink-0 items-center justify-center rounded-full bg-blue-100 sm:mx-0 sm:h-10 sm:w-10">
                                <svg class="h-6 w-6 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-8l-4-4m0 0L8 8m4-4v12"></path></svg>
                            </div>
                            <div class="mt-3 text-center sm:ml-4 sm:mt-0 sm:text-left w-full">
                                <h3 class="text-base font-semibold leading-6 text-gray-900" id="modal-title">Verify Transaction: <span id="verifyInvoice" class="text-blue-700"></span></h3>
                                
                                <div class="mt-4">
                                    <label class="block text-sm font-medium text-gray-700 mb-2">Upload Installation Photos (Bukti Pasang)</label>
                                    
                                    <!-- Grid 2 Kolom untuk Before & After -->
                                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                                        
                                        <!-- KOTAK KIRI: BEFORE -->
                                        <div class="border border-gray-200 rounded-lg p-3 bg-gray-50 flex flex-col justify-between" id="containerBefore">
                                            <div>
                                                <label class="block text-xs font-bold text-gray-800 mb-1 flex items-center">
                                                    <span class="w-2 h-2 rounded-full bg-amber-500 mr-2"></span>1. Foto Sebelum
                                                </label>
                                                
                                                <div class="mt-1 flex justify-center px-4 pt-4 pb-4 border-2 border-gray-300 border-dashed rounded-md bg-white hover:border-blue-700 hover:bg-blue-50 transition-colors cursor-pointer group" 
                                                     onclick="document.getElementById('verify_proof_image_before').click()">
                                                    <div class="space-y-1 text-center">
                                                        <svg id="uploadIconBefore" class="mx-auto h-8 w-8 text-gray-400 group-hover:text-blue-700" stroke="currentColor" fill="none" viewBox="0 0 24 24">
                                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 16a4 4 0 01-.88-7.903A5 5 0 1115.9 6L16 6a5 5 0 011 9.9M15 13l-3-3m0 0l-3 3m3-3v12"></path>
                                                        </svg>
                                                        <img id="imagePreviewBefore" class="hidden mx-auto h-24 w-auto object-contain rounded-md mb-1" />
                                                        
                                                        <div class="flex text-xs text-gray-600 justify-center">
                                                            <span id="verifyFileNameBefore" class="font-medium text-blue-700">Click/Paste</span>
                                                        </div>
                                                        <input id="verify_proof_image_before" name="proof_image_before" type="file" class="sr-only" accept=".jpg,.jpeg,.png,.pdf" required onchange="validateAndUpdateVerifyFileName(this, 'before')">
                                                    </div>
                                                </div>
                                            </div>
                                            <button type="button" onclick="openCameraModal('before')" class="mt-2 text-xs font-bold text-blue-700 hover:text-blue-900 flex items-center justify-center w-full focus:outline-none py-1 border border-gray-300 rounded bg-white shadow-sm hover:bg-gray-100">
                                                <svg class="w-4 h-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 9a2 2 0 012-2h.93a2 2 0 001.664-.89l.812-1.22A2 2 0 0110.07 4h3.86a2 2 0 011.664.89l.812 1.22A2 2 0 0018.07 7H19a2 2 0 012 2v9a2 2 0 01-2 2H5a2 2 0 01-2-2V9z"></path><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 13a3 3 0 11-6 0 3 3 0 016 0z"></path></svg>
                                                Camera
                                            </button>
                                        </div>

                                        <!-- KOTAK KANAN: AFTER -->
                                        <div class="border border-gray-200 rounded-lg p-3 bg-gray-50 flex flex-col justify-between" id="containerAfter">
                                            <div>
                                                <label class="block text-xs font-bold text-gray-800 mb-1 flex items-center">
                                                    <span class="w-2 h-2 rounded-full bg-green-500 mr-2"></span>2. Foto Sesudah
                                                </label>
                                                
                                                <div class="mt-1 flex justify-center px-4 pt-4 pb-4 border-2 border-gray-300 border-dashed rounded-md bg-white hover:border-blue-700 hover:bg-blue-50 transition-colors cursor-pointer group" 
                                                     onclick="document.getElementById('verify_proof_image').click()">
                                                    <div class="space-y-1 text-center">
                                                        <svg id="uploadIconAfter" class="mx-auto h-8 w-8 text-gray-400 group-hover:text-blue-700" stroke="currentColor" fill="none" viewBox="0 0 24 24">
                                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 16a4 4 0 01-.88-7.903A5 5 0 1115.9 6L16 6a5 5 0 011 9.9M15 13l-3-3m0 0l-3 3m3-3v12"></path>
                                                        </svg>
                                                        <img id="imagePreviewAfter" class="hidden mx-auto h-24 w-auto object-contain rounded-md mb-1" />
                                                        
                                                        <div class="flex text-xs text-gray-600 justify-center">
                                                            <span id="verifyFileNameAfter" class="font-medium text-blue-700">Click/Paste</span>
                                                        </div>
                                                        <input id="verify_proof_image" name="proof_image" type="file" class="sr-only" accept=".jpg,.jpeg,.png,.pdf" required onchange="validateAndUpdateVerifyFileName(this, 'after')">
                                                    </div>
                                                </div>
                                            </div>
                                            <button type="button" onclick="openCameraModal('after')" class="mt-2 text-xs font-bold text-blue-700 hover:text-blue-900 flex items-center justify-center w-full focus:outline-none py-1 border border-gray-300 rounded bg-white shadow-sm hover:bg-gray-100">
                                                <svg class="w-4 h-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 9a2 2 0 012-2h.93a2 2 0 001.664-.89l.812-1.22A2 2 0 0110.07 4h3.86a2 2 0 011.664.89l.812 1.22A2 2 0 0018.07 7H19a2 2 0 012 2v9a2 2 0 01-2 2H5a2 2 0 01-2-2V9z"></path><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 13a3 3 0 11-6 0 3 3 0 016 0z"></path></svg>
                                                Camera
                                            </button>
                                        </div>

                                    </div>
                                    <p class="text-[10px] text-gray-400 mt-2 text-center">Format: PNG, JPG, PDF maks. 5MB</p>
                                </div>
                            </div>
                        </div>
                    </div>
                    
                    <div class="bg-gray-50 px-4 py-3 sm:flex sm:flex-row-reverse sm:px-6">
                        <button type="submit" class="inline-flex w-full justify-center rounded-md bg-blue-700 px-3 py-2 text-sm font-semibold text-white shadow-sm hover:bg-blue-800 sm:ml-3 sm:w-auto">Save & Verify</button>
                        <button type="button" onclick="closeVerifyModal()" class="mt-3 inline-flex w-full justify-center rounded-md bg-white px-3 py-2 text-sm font-semibold text-gray-900 shadow-sm ring-1 ring-inset ring-gray-300 hover:bg-gray-50 sm:mt-0 sm:w-auto">Cancel</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- --- MODAL CANCEL (SUPERVISOR ONLY) --- -->
    <div id="cancelModal" class="fixed inset-0 z-50 hidden overflow-y-auto">
        <div class="fixed inset-0 bg-gray-500 bg-opacity-75 transition-opacity" onclick="closeCancelModal()"></div>

        <div class="flex min-h-full items-end justify-center p-4 text-center sm:items-center sm:p-0">
            <div class="relative transform overflow-hidden rounded-lg bg-white text-left shadow-xl transition-all sm:my-8 sm:w-full sm:max-w-lg">
                <form id="cancelForm" method="POST">
                    @csrf
                    @method('PUT')
                    <input type="hidden" name="action" value="cancel">
                    
                    <div class="bg-white p-6">
                        <div class="sm:flex sm:items-start">
                            <div class="mx-auto flex h-12 w-12 flex-shrink-0 items-center justify-center rounded-full bg-red-100 sm:mx-0 sm:h-10 sm:w-10">
                                <svg class="h-6 w-6 text-red-600" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M12 9v3.75m-9.303 3.376c-.866 1.5.217 3.374 1.948 3.374h14.71c1.73 0 2.813-1.874 1.948-3.374L13.949 3.378c-.866-1.5-3.032-1.5-3.898 0L2.697 16.126zM12 15.75h.007v.008H12v-.008z" />
                                </svg>
                            </div>
                            <div class="mt-3 text-center sm:ml-4 sm:mt-0 sm:text-left">
                                <h3 class="text-base font-semibold leading-6 text-gray-900">Cancel Transaction: <span id="cancelInvoice" class="text-red-600"></span></h3>
                                <div class="mt-2">
                                    <p class="text-sm text-gray-500">Are you sure you want to cancel this issue? <br><strong>Inventory stock will be returned (increased).</strong> This action cannot be undone.</p>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="bg-gray-50 px-4 py-3 sm:flex sm:flex-row-reverse sm:px-6">
                        <button type="submit" class="inline-flex w-full justify-center rounded-md bg-red-600 px-3 py-2 text-sm font-semibold text-white shadow-sm hover:bg-red-500 sm:ml-3 sm:w-auto">Yes, Cancel Issue</button>
                        <button type="button" onclick="closeCancelModal()" class="mt-3 inline-flex w-full justify-center rounded-md bg-white px-3 py-2 text-sm font-semibold text-gray-900 shadow-sm ring-1 ring-inset ring-gray-300 hover:bg-gray-50 sm:mt-0 sm:w-auto">Back</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- Scripts -->
    <script>
        // FUNGSI UMUM MODAL
        function openVerifyModal(id, invoice) {
            document.getElementById('verifyInvoice').textContent = invoice;
            const url = "{{ url('/transactions/pending') }}/" + id;
            document.getElementById('verifyForm').action = url;
            document.getElementById('verifyModal').classList.remove('hidden');
        }

        function closeVerifyModal() {
            document.getElementById('verifyModal').classList.add('hidden');
            
            // Reset kedua input & preview saat modal ditutup
            ['before', 'after'].forEach(target => {
                const preview = document.getElementById('imagePreview' + (target === 'before' ? 'Before' : 'After'));
                const icon = document.getElementById('uploadIcon' + (target === 'before' ? 'Before' : 'After'));
                const label = document.getElementById('verifyFileName' + (target === 'before' ? 'Before' : 'After'));
                const input = document.getElementById(target === 'before' ? 'verify_proof_image_before' : 'verify_proof_image');
                
                if(preview) { preview.src = '#'; preview.classList.add('hidden'); }
                if(icon) { icon.classList.remove('hidden'); }
                if(label) { label.textContent = 'Click/Paste'; }
                if(input) { input.value = ''; }
            });
        }

        function openCancelModal(id, invoice) {
            document.getElementById('cancelInvoice').textContent = invoice;
            const url = "{{ url('/transactions/pending') }}/" + id;
            document.getElementById('cancelForm').action = url;
            document.getElementById('cancelModal').classList.remove('hidden');
        }

        function closeCancelModal() {
            document.getElementById('cancelModal').classList.add('hidden');
        }

        // --- LOGIKA MULTI UPLOAD (BEFORE & AFTER) ---
        
        // Menyimpan state kotak mana yang sedang aktif untuk Camera / Paste
        let currentCameraTarget = 'before'; 
        let activePasteTarget = 'before';   
        let videoStream = null;

        // Listener hover & klik agar saat Ctrl+V tahu mau masuk ke kotak mana
        document.getElementById('containerBefore')?.addEventListener('mouseenter', () => { activePasteTarget = 'before'; });
        document.getElementById('containerBefore')?.addEventListener('click', () => { activePasteTarget = 'before'; });
        document.getElementById('containerAfter')?.addEventListener('mouseenter', () => { activePasteTarget = 'after'; });
        document.getElementById('containerAfter')?.addEventListener('click', () => { activePasteTarget = 'after'; });

        // Fungsi membaca dan memvalidasi gambar yang diupload
        function validateAndUpdateVerifyFileName(input, target) {
            const isBefore = target === 'before';
            const label = document.getElementById(isBefore ? 'verifyFileNameBefore' : 'verifyFileNameAfter');
            const preview = document.getElementById(isBefore ? 'imagePreviewBefore' : 'imagePreviewAfter');
            const icon = document.getElementById(isBefore ? 'uploadIconBefore' : 'uploadIconAfter');
            
            if (input.files && input.files.length > 0) {
                const file = input.files[0];
                
                // Validasi ukuran max 5MB
                if(file.size / 1024 / 1024 > 5) {
                    alert('Max file size is 5MB!');
                    input.value = '';
                    label.textContent = 'Click/Paste';
                    return;
                }

                // Ganti teks label dengan nama file yang terpotong rapi
                label.textContent = file.name.substring(0, 15) + (file.name.length > 15 ? '...' : '');

                // Logika untuk file PDF
                if (file.type === 'application/pdf') {
                    preview.classList.add('hidden');
                    icon.classList.remove('hidden');
                    icon.innerHTML = '<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path>';
                } else {
                    // Logika untuk file Gambar
                    const reader = new FileReader();
                    reader.onload = function(e) {
                        preview.src = e.target.result;
                        preview.classList.remove('hidden');
                        icon.classList.add('hidden');
                    }
                    reader.readAsDataURL(file);
                }
            } else {
                label.textContent = 'Click/Paste';
                preview.src = '#';
                preview.classList.add('hidden');
                icon.classList.remove('hidden');
            }
        }

        // --- LOGIKA KAMERA ---
        async function openCameraModal(target) {
            currentCameraTarget = target;
            const modal = document.getElementById('cameraModal');
            const video = document.getElementById('cameraVideo');
            if (!modal || !video) return;

            modal.classList.remove('hidden');
            try {
                videoStream = await navigator.mediaDevices.getUserMedia({ video: { facingMode: 'environment' } });
                video.srcObject = videoStream;
            } catch (err) {
                alert("Cannot access camera: " + err.message);
                stopCamera(); 
            }
        }

        function capturePhoto() {
            const video = document.getElementById('cameraVideo');
            const canvas = document.createElement('canvas');
            canvas.width = video.videoWidth; 
            canvas.height = video.videoHeight;
            canvas.getContext('2d').drawImage(video, 0, 0);
            
            canvas.toBlob(function(blob) {
                const fileName = "camera_" + currentCameraTarget + "_" + Date.now() + ".jpg";
                const file = new File([blob], fileName, { type: "image/jpeg" });
                
                // Pilih input mana yang diisi file
                const inputId = currentCameraTarget === 'before' ? 'verify_proof_image_before' : 'verify_proof_image';
                const input = document.getElementById(inputId);
                
                const dt = new DataTransfer(); 
                dt.items.add(file); 
                input.files = dt.files;
                
                validateAndUpdateVerifyFileName(input, currentCameraTarget);
                stopCamera();
            }, 'image/jpeg', 0.9);
        }

        function stopCamera() {
            const modal = document.getElementById('cameraModal');
            const video = document.getElementById('cameraVideo');
            if (modal) modal.classList.add('hidden');
            if (videoStream) videoStream.getTracks().forEach(track => track.stop());
            if (video) video.srcObject = null;
        }

        // --- LOGIKA PASTE (Ctrl+V) ---
        document.addEventListener('paste', function(e) {
            const modal = document.getElementById('verifyModal');
            if (!modal || modal.classList.contains('hidden')) return;

            const items = e.clipboardData.items;
            for (let i = 0; i < items.length; i++) {
                if (items[i].type.indexOf('image') !== -1) {
                    const blob = items[i].getAsFile();
                    
                    // Cek ukuran sebelum diproses
                    if (blob.size / 1024 / 1024 > 5) {
                        alert('Max file size is 5MB!');
                        return;
                    }

                    const file = new File([blob], "pasted_" + activePasteTarget + "_" + Date.now() + ".png", { type: blob.type });
                    
                    const inputId = activePasteTarget === 'before' ? 'verify_proof_image_before' : 'verify_proof_image';
                    const input = document.getElementById(inputId);
                    
                    const dt = new DataTransfer(); 
                    dt.items.add(file); 
                    input.files = dt.files;
                    
                    validateAndUpdateVerifyFileName(input, activePasteTarget);
                    
                    // Efek visual border biru berkedip
                    const containerId = activePasteTarget === 'before' ? 'containerBefore' : 'containerAfter';
                    const container = document.getElementById(containerId);
                    if (container) {
                        container.style.borderColor = '#1d4ed8';
                        setTimeout(() => container.style.borderColor = '', 300);
                    }
                    break;
                }
            }
        });
    </script>
</x-app-layout>