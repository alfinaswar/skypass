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
                            <tbody class="bg-white">
                                @forelse($pendingTransactions as $trx)
                                    @php $rowCount = $trx->items->count(); @endphp
                                    @foreach($trx->items as $item)
                                        <tr class="hover:bg-gray-50 transition-colors">
                                            @if($loop->first)
                                                <!-- Date (Merged) -->
                                                <td rowspan="{{ $rowCount }}" class="px-6 py-4 whitespace-nowrap text-sm text-gray-900 align-top border-b border-gray-200">
                                                    {{ \Carbon\Carbon::parse($trx->date)->format('d M Y') }}
                                                    <div class="text-xs text-gray-400">{{ \Carbon\Carbon::parse($trx->created_at)->format('H:i') }}</div>
                                                </td>

                                                <!-- Requester / Vehicle (Merged) -->
                                                <td rowspan="{{ $rowCount }}" class="px-6 py-4 text-center align-top border-b border-gray-200">
                                                    <div class="text-sm font-bold text-gray-900">{{ $trx->requester_name }}</div>
                                                    <div class="text-xs text-blue-600 font-bold bg-blue-50 px-2 py-0.5 rounded-full inline-block mt-1 mx-auto">
                                                        {{ $trx->fleet_number }}
                                                    </div>
                                                </td>
                                            @endif

                                            <!-- Part Info -->
                                            <td class="px-6 py-4 {{ $loop->last ? 'border-b border-gray-200' : '' }}">
                                                <div class="text-sm text-gray-900 font-medium">{{ $item->part_name }}</div>
                                            </td>

                                            <!-- Qty -->
                                            <td class="px-6 py-4 text-center whitespace-nowrap {{ $loop->last ? 'border-b border-gray-200' : '' }}">
                                                <span class="px-3 py-1 inline-flex text-sm leading-5 font-bold rounded-full bg-yellow-100 text-yellow-800 whitespace-nowrap">
                                                    {{ $item->qty }} {{ $item->unit }}
                                                </span>
                                            </td>

                                            <!-- Actions -->
                                            <td class="px-6 py-4 text-right text-sm font-medium space-x-2 whitespace-nowrap {{ $loop->last ? 'border-b border-gray-200' : '' }}">
                                                <button onclick='openVerifyModal(@json($trx->id), @json($trx->invoice_no), {{ $item->qty }}, {{ $item->sparepart_id }}, @json($item->part_name))' 
                                                    class="text-blue-600 hover:text-blue-900 font-bold focus:outline-none transition">
                                                    Upload Proof
                                                </button>
                                                @if(auth()->user()->role === 'supervisor')
                                                    <button onclick='openCancelModal(@json($trx->id), @json($trx->invoice_no), {{ $item->sparepart_id }}, @json($item->part_name))' 
                                                        class="text-red-600 hover:text-red-900 font-bold ml-3 focus:outline-none transition">
                                                        Cancel
                                                    </button>
                                                @else
                                                    <button disabled class="text-gray-400 font-bold ml-3 cursor-not-allowed" title="Only Supervisor can cancel">
                                                        Cancel
                                                    </button>
                                                @endif
                                            </td>
                                        </tr>
                                    @endforeach
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
            <div class="relative transform overflow-hidden rounded-lg bg-white text-left shadow-xl transition-all sm:my-8 sm:w-full sm:max-w-4xl" style="max-width: 56rem;">
                <form id="verifyForm" method="POST" enctype="multipart/form-data">
                    @csrf
                    @method('PUT')
                    <input type="hidden" name="action" value="verify">
                    <input type="hidden" name="sparepart_id" id="verify_sparepart_id" value="">
                    
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
                                    <div id="unitProofs" class="space-y-4 max-h-[60vh] overflow-y-auto"></div>
                                    <template id="unitProofTemplate">
                                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                                        
                                        <!-- KOTAK KIRI: BEFORE -->
                                        <div class="border border-gray-200 rounded-lg p-3 bg-gray-50 flex flex-col justify-between" id="containerBefore">
                                            <div>
                                                <label class="block text-xs font-bold text-gray-800 mb-1 flex items-center">
                                                    <span class="w-2 h-2 rounded-full bg-amber-500 mr-2"></span>1. Foto Sebelum
                                                </label>
                                                
                                                <div class="mt-1 flex justify-center px-4 pt-4 pb-4 border-2 border-gray-300 border-dashed rounded-md bg-white hover:border-blue-700 hover:bg-blue-50 transition-colors cursor-pointer group" 
                                                     onclick="document.getElementById('verify_proof_image_before').click()">
                                                    <div class="space-y-1 text-center w-full">
                                                        <svg id="uploadIconBefore" class="mx-auto h-8 w-8 text-gray-400 group-hover:text-blue-700" stroke="currentColor" fill="none" viewBox="0 0 24 24">
                                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 16a4 4 0 01-.88-7.903A5 5 0 1115.9 6L16 6a5 5 0 011 9.9M15 13l-3-3m0 0l-3 3m3-3v12"></path>
                                                        </svg>
                                                        <img id="imagePreviewBefore" class="hidden mx-auto h-24 w-auto object-contain rounded-md mb-1" />
                                                        
                                                        <div class="flex text-xs text-gray-600 justify-center">
                                                            <span id="verifyFileNameBefore" class="upload-file-label font-medium text-blue-700">Click/Paste (Multiple)</span>
                                                        </div>
                                                        <input id="verify_proof_image_before" name="proof_image_before[]" type="file" multiple class="sr-only" accept=".jpg,.jpeg,.png,.pdf" required onchange="validateAndUpdateVerifyFileName(this, 'before')">
                                                    </div>
                                                </div>
                                                <div class="proof-preview-list grid grid-cols-3 gap-2 mt-2 hidden"></div>
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
                                                    <div class="space-y-1 text-center w-full">
                                                        <svg id="uploadIconAfter" class="mx-auto h-8 w-8 text-gray-400 group-hover:text-blue-700" stroke="currentColor" fill="none" viewBox="0 0 24 24">
                                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 16a4 4 0 01-.88-7.903A5 5 0 1115.9 6L16 6a5 5 0 011 9.9M15 13l-3-3m0 0l-3 3m3-3v12"></path>
                                                        </svg>
                                                        <img id="imagePreviewAfter" class="hidden mx-auto h-24 w-auto object-contain rounded-md mb-1" />
                                                        
                                                        <div class="flex text-xs text-gray-600 justify-center">
                                                            <span id="verifyFileNameAfter" class="upload-file-label font-medium text-blue-700">Click/Paste (Multiple)</span>
                                                        </div>
                                                        <input id="verify_proof_image" name="proof_image[]" type="file" multiple class="sr-only" accept=".jpg,.jpeg,.png,.pdf" required onchange="validateAndUpdateVerifyFileName(this, 'after')">
                                                    </div>
                                                </div>
                                                <div class="proof-preview-list grid grid-cols-3 gap-2 mt-2 hidden"></div>
                                            </div>
                                            <button type="button" onclick="openCameraModal('after')" class="mt-2 text-xs font-bold text-blue-700 hover:text-blue-900 flex items-center justify-center w-full focus:outline-none py-1 border border-gray-300 rounded bg-white shadow-sm hover:bg-gray-100">
                                                <svg class="w-4 h-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 9a2 2 0 012-2h.93a2 2 0 001.664-.89l.812-1.22A2 2 0 0110.07 4h3.86a2 2 0 011.664.89l.812 1.22A2 2 0 0018.07 7H19a2 2 0 012 2v9a2 2 0 01-2 2H5a2 2 0 01-2-2V9z"></path><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 13a3 3 0 11-6 0 3 3 0 016 0z"></path></svg>
                                                Camera
                                            </button>
                                        </div>

                                    </div>
                                    </template>
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
                    <input type="hidden" name="sparepart_id" id="cancel_sparepart_id" value="">
                    
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
        let proofFileBuffers = {};

        function openVerifyModal(id, invoice, qty, sparepartId = '', partName = '') {
            proofFileBuffers = {};
            document.getElementById('verifyInvoice').textContent = partName ? `${invoice} — ${partName}` : invoice;
            document.getElementById('verify_sparepart_id').value = sparepartId || '';
            const url = "{{ url('/transactions/pending') }}/" + id;
            document.getElementById('verifyForm').action = url;
            const proofs = document.getElementById('unitProofs');
            proofs.replaceChildren();
            const unit = 0;
            const fragment = document.getElementById('unitProofTemplate').content.cloneNode(true);
            fragment.querySelectorAll('.border-gray-200.rounded-lg').forEach((container, index) => {
                const target = index === 0 ? 'before' : 'after';
                const targetKey = `${unit}-${target}`;
                proofFileBuffers[targetKey] = new DataTransfer();
                container.id = `container-${targetKey}`;
                container.querySelector('label').lastChild.textContent = ` ${index + 1}. Foto ${target === 'before' ? 'Sebelum' : 'Sesudah'}`;
                const input = container.querySelector('input[type="file"]');
                input.id = `proof-${targetKey}`;
                input.name = `proofs[${unit}][${target}][]`;
                input.setAttribute('onchange', `handleProofFilesSelected(this, '${targetKey}')`);
                container.querySelector('[onclick*="document.getElementById"]').setAttribute('onclick', `document.getElementById('proof-${targetKey}').click()`);
                container.querySelector('img').id = `preview-${targetKey}`;
                container.querySelector('svg').id = `icon-${targetKey}`;
                container.querySelector('.upload-file-label').id = `label-${targetKey}`;
                container.querySelector('.proof-preview-list').id = `preview-list-${targetKey}`;
                container.querySelector('button').setAttribute('onclick', `openCameraModal('${targetKey}')`);
            });
            proofs.append(fragment);
            document.getElementById('verifyModal').classList.remove('hidden');
        }

        function closeVerifyModal() {
            document.getElementById('verifyModal').classList.add('hidden');
            proofFileBuffers = {};
            document.getElementById('unitProofs').replaceChildren();
        }

        function openCancelModal(id, invoice, sparepartId = '', partName = '') {
            document.getElementById('cancelInvoice').textContent = partName ? `${invoice} — ${partName}` : invoice;
            document.getElementById('cancel_sparepart_id').value = sparepartId || '';
            const url = "{{ url('/transactions/pending') }}/" + id;
            document.getElementById('cancelForm').action = url;
            document.getElementById('cancelModal').classList.remove('hidden');
        }

        function closeCancelModal() {
            document.getElementById('cancelModal').classList.add('hidden');
        }

        // --- LOGIKA MULTI UPLOAD (BEFORE & AFTER) ---
        let currentCameraTarget = '0-before';
        let activePasteTarget = '0-before';
        let videoStream = null;

        document.getElementById('unitProofs')?.addEventListener('mouseover', event => {
            const container = event.target.closest('[id^="container-"]');
            if (container) activePasteTarget = container.id.replace('container-', '');
        });

        function handleProofFilesSelected(input, target) {
            if (!input.files || input.files.length === 0) return;
            addFilesToProofBuffer(target, Array.from(input.files));
        }

        function addFilesToProofBuffer(target, files) {
            if (!proofFileBuffers[target]) {
                proofFileBuffers[target] = new DataTransfer();
            }
            const dt = proofFileBuffers[target];

            for (const file of files) {
                if (file.size / 1024 / 1024 > 5) {
                    alert(`File "${file.name}" melebihi batas maksimal 5MB!`);
                    continue;
                }
                dt.items.add(file);
            }

            syncProofInputAndPreviews(target);
        }

        function removeProofFile(target, index) {
            const oldDt = proofFileBuffers[target];
            if (!oldDt) return;
            const newDt = new DataTransfer();
            Array.from(oldDt.files).forEach((file, i) => {
                if (i !== index) newDt.items.add(file);
            });
            proofFileBuffers[target] = newDt;
            syncProofInputAndPreviews(target);
        }

        function syncProofInputAndPreviews(target) {
            const input = document.getElementById(`proof-${target}`);
            const label = document.getElementById(`label-${target}`);
            const list = document.getElementById(`preview-list-${target}`);
            const dt = proofFileBuffers[target] || new DataTransfer();

            if (input) input.files = dt.files;

            const count = dt.files.length;
            if (label) {
                label.textContent = count > 0 ? `${count} file dipilih (+ tambah lagi)` : 'Click/Paste (Multiple)';
            }

            if (!list) return;
            list.innerHTML = '';

            if (count === 0) {
                list.classList.add('hidden');
                list.style.display = 'none';
                return;
            }

            list.classList.remove('hidden');
            list.style.display = 'grid';
            list.style.gridTemplateColumns = 'repeat(3, minmax(0, 1fr))';
            list.style.gap = '0.5rem';
            list.style.marginTop = '0.75rem';
            list.style.maxHeight = '16rem';
            list.style.overflowY = 'auto';

            Array.from(dt.files).forEach((file, idx) => {
                const card = document.createElement('div');
                card.className = 'relative border border-gray-200 rounded bg-white p-1 flex flex-col items-center';
                card.style.position = 'relative';
                card.style.overflow = 'hidden';

                const removeBtn = document.createElement('button');
                removeBtn.type = 'button';
                removeBtn.innerHTML = '&times;';
                removeBtn.title = 'Hapus file';
                removeBtn.className = 'bg-red-600 text-white rounded-full flex items-center justify-center shadow hover:bg-red-700';
                removeBtn.style.position = 'absolute';
                removeBtn.style.top = '0.25rem';
                removeBtn.style.right = '0.25rem';
                removeBtn.style.width = '1.25rem';
                removeBtn.style.height = '1.25rem';
                removeBtn.style.fontSize = '12px';
                removeBtn.style.lineHeight = '1';
                removeBtn.style.zIndex = '10';
                removeBtn.onclick = (e) => {
                    e.stopPropagation();
                    removeProofFile(target, idx);
                };
                card.appendChild(removeBtn);

                if (file.type === 'application/pdf') {
                    const pdfBadge = document.createElement('div');
                    pdfBadge.className = 'w-full flex items-center justify-center bg-red-50 text-red-600 rounded text-xs font-bold';
                    pdfBadge.style.height = '4.5rem';
                    pdfBadge.textContent = 'PDF';
                    card.appendChild(pdfBadge);
                } else {
                    const img = document.createElement('img');
                    img.className = 'w-full rounded';
                    img.style.height = '4.5rem';
                    img.style.width = '100%';
                    img.style.objectFit = 'cover';
                    const reader = new FileReader();
                    reader.onload = (e) => { img.src = e.target.result; };
                    reader.readAsDataURL(file);
                    card.appendChild(img);
                }

                const nameEl = document.createElement('div');
                nameEl.className = 'text-[10px] text-gray-600 truncate w-full text-center mt-1';
                nameEl.textContent = file.name;
                card.appendChild(nameEl);

                list.appendChild(card);
            });
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
                
                addFilesToProofBuffer(currentCameraTarget, [file]);
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
                    
                    addFilesToProofBuffer(activePasteTarget, [file]);
                    
                    // Efek visual border biru berkedip
                    const container = document.getElementById(`container-${activePasteTarget}`);
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
