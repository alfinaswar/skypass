<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-blue-700 leading-tight">
            {{ __('Transaction History') }}
        </h2>
    </x-slot>

    <div class="py-12 bg-gray-50">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            
            <!-- TABS NAVIGATION & DOWNLOAD BUTTON -->
            <div class="flex justify-between items-end border-b border-gray-300 mb-6">
                <!-- Left: Tabs -->
                <div class="flex">
                    <!-- Tab: Good Issued (OUT) -->
                    <a href="{{ route('transactions.index', ['tab' => 'OUT']) }}" 
                       class="px-6 py-3 font-bold text-sm rounded-t-lg transition-colors border-t border-l border-r 
                       {{ $tab === 'OUT' ? 'bg-white text-blue-700 border-gray-300 -mb-px shadow-sm' : 'bg-gray-200 text-gray-500 hover:bg-gray-100 border-transparent' }}">
                       Good Issued (Stock OUT)
                    </a>

                    <!-- Tab: Good Receipt (IN) -->
                    <a href="{{ route('transactions.index', ['tab' => 'IN']) }}" 
                       class="px-6 py-3 font-bold text-sm rounded-t-lg transition-colors border-t border-l border-r ml-2
                       {{ $tab === 'IN' ? 'bg-white text-green-700 border-gray-300 -mb-px shadow-sm' : 'bg-gray-200 text-gray-500 hover:bg-gray-100 border-transparent' }}">
                       Good Receipt (Stock IN)
                    </a>
                </div>

                <!-- Right: Download Button -->
                <div class="mb-2 flex gap-2">
                    @if($tab === 'OUT')
                        <!-- BARU: Tombol Cetak BAST Massal -->
                        <button type="button" onclick="openBastModal()" class="inline-flex items-center px-4 py-2 bg-emerald-600 text-white rounded-md font-bold text-sm hover:bg-emerald-700 transition shadow-sm">
                            <svg class="h-4 w-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 17h2a2 2 0 002-2v-4a2 2 0 00-2-2H5a2 2 0 00-2 2v4a2 2 0 002 2h2m2 4h6a2 2 0 002-2v-4a2 2 0 00-2-2H9a2 2 0 00-2 2v4a2 2 0 002 2zm8-12V5a2 2 0 00-2-2H9a2 2 0 00-2 2v4h10z"></path></svg>
                            Download BAST
                        </button>

                        <!-- Download GI (Sort by Vehicle) -->
                        <a href="{{ route('reports.exportGI') }}" 
                           class="inline-flex items-center px-4 py-2 bg-blue-700 text-white rounded-md font-bold text-sm hover:bg-blue-800 transition shadow-sm">
                            <svg class="h-4 w-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4"></path></svg>
                            Download GI Data
                        </a>
                    @else
                        <!-- Download GR (Sort by Time & PO) -->
                        <a href="{{ route('reports.exportGR') }}" 
                           class="inline-flex items-center px-4 py-2 bg-green-600 text-white rounded-md font-bold text-sm hover:bg-green-700 transition shadow-sm">
                            <svg class="h-4 w-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4"></path></svg>
                            Download GR Data
                        </a>
                    @endif
                </div>
            </div>

            <!-- FILTER & SEARCH BAR -->
            <div class="bg-white p-6 rounded-lg shadow-sm mb-6 border border-gray-200">
                <form action="{{ route('transactions.index') }}" method="GET">
                    <!-- Pertahankan Tab saat filter -->
                    <input type="hidden" name="tab" value="{{ $tab }}">

                    <div class="flex flex-col md:flex-row gap-4 items-end justify-between">
                        
                        <!-- LEFT SIDE: Search Box -->
                        <div class="w-full md:w-1/3">
                            <label class="block text-xs font-bold text-gray-500 uppercase mb-1">Search</label>
                            <div class="relative">
                                <input type="text" name="search" value="{{ request('search') }}" 
                                    class="w-full rounded-md border-gray-300 shadow-sm focus:border-blue-700 focus:ring-blue-700 pl-10" 
                                    placeholder="{{ $tab === 'OUT' ? 'Part No, Name, or Technician.' : 'Part No, Name, or PO No.' }}">
                                <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                                    <svg class="h-5 w-5 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/></svg>
                                </div>
                            </div>
                        </div>

                        <!-- RIGHT SIDE: Filters & Actions -->
                        <div class="flex flex-col md:flex-row gap-4 w-full md:w-auto items-end">
                            
                            <!-- 2. Vehicle Filter (Hanya muncul di Tab GI/OUT) -->
                            @if($tab === 'OUT')
                                <div class="w-full md:w-48">
                                    <label class="block text-xs font-bold text-gray-500 uppercase mb-1">Filter Facility</label>
                                    <select name="vehicle_id" class="w-full rounded-md border-gray-300 shadow-sm focus:border-blue-700 focus:ring-blue-700 text-sm">
                                        <option value="">All Facilities</option>
                                        @foreach($vehicles as $v)
                                            <option value="{{ $v->id }}" {{ request('vehicle_id') == $v->id ? 'selected' : '' }}>
                                                {{ $v->fleet_number }}
                                            </option>
                                        @endforeach
                                    </select>
                                </div>
                            @endif

                            <!-- 3. Sort Order -->
                            <div class="w-full md:w-40">
                                <label class="block text-xs font-bold text-gray-500 uppercase mb-1">Sort Date</label>
                                <select name="sort" class="w-full rounded-md border-gray-300 shadow-sm focus:border-blue-700 focus:ring-blue-700 text-sm">
                                    <option value="desc" {{ request('sort', 'desc') == 'desc' ? 'selected' : '' }}>Newest First</option>
                                    <option value="asc" {{ request('sort') == 'asc' ? 'selected' : '' }}>Oldest First</option>
                                </select>
                            </div>

                            <!-- 4. Submit Button -->
                            <div>
                                <button type="submit" class="px-6 py-2 bg-blue-700 text-white font-bold rounded-md hover:bg-blue-800 transition shadow-sm text-sm h-[38px] border border-transparent">
                                    Filter
                                </button>
                            </div>

                        </div>
                    </div>
                </form>
            </div>

            <!-- DATA TABLE -->
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg border-t-4 {{ $tab === 'OUT' ? 'border-blue-700' : 'border-green-600' }}">
                <div class="overflow-x-auto">
                    <table class="min-w-full divide-y divide-gray-200">
                        <thead class="bg-gray-50">
                            <tr>
                                <!-- BARU: Checkbox Header -->
                                @if($tab === 'OUT')
                                <th scope="col" class="px-4 py-4 w-10 text-center">
                                    <input type="checkbox" class="rounded border-gray-300 text-blue-600 focus:ring-blue-500 cursor-pointer" onclick="toggleAllCheckboxes(this)">
                                </th>
                                @endif
                                
                                <th class="px-6 py-4 text-left text-xs font-bold text-gray-500 uppercase tracking-wider">Date / Invoice</th>
                                <th class="px-6 py-4 text-left text-xs font-bold text-gray-500 uppercase tracking-wider">Details</th>
                                <th class="px-6 py-4 text-center text-xs font-bold text-gray-500 uppercase tracking-wider">Items</th>
                                
                                <!-- Kolom Dinamis Berdasarkan Tab -->
                                @if($tab === 'OUT')
                                    <th class="px-6 py-4 text-left text-xs font-bold text-gray-500 uppercase tracking-wider">Facility</th>
                                    <th class="px-6 py-4 text-left text-xs font-bold text-gray-500 uppercase tracking-wider">Technician</th>
                                @else
                                    <th class="px-6 py-4 text-left text-xs font-bold text-gray-500 uppercase tracking-wider">Source</th>
                                    <th class="px-6 py-4 text-left text-xs font-bold text-gray-500 uppercase tracking-wider">Type</th>
                                @endif

                                <th class="px-6 py-4 text-right text-xs font-bold text-gray-500 uppercase tracking-wider">Proof / Actions</th>
                            </tr>
                        </thead>
                        <tbody class="bg-white divide-y divide-gray-200">
                            @forelse ($transactions as $trx)
                                @php $details = $transactionDetails->get($trx->id, collect()); @endphp
                                <tr class="transition-colors {{ $trx->status === 'canceled' ? 'bg-red-50 hover:bg-red-100' : 'hover:bg-gray-50' }}">
                                    <!-- BARU: Checkbox Row -->
                                    @if($tab === 'OUT')
                                    <td class="px-4 py-4 text-center whitespace-nowrap">
                                        @if($trx->status === 'completed')
                                            <input type="checkbox" value="{{ $trx->id }}" data-date="{{ \Carbon\Carbon::parse($trx->date)->format('Y-m-d') }}" class="trx-checkbox rounded border-gray-300 text-blue-600 focus:ring-blue-500 cursor-pointer">
                                        @else
                                            <input type="checkbox" disabled class="rounded border-gray-300 bg-gray-200 cursor-not-allowed" title="Canceled transactions cannot be selected">
                                        @endif
                                    </td>
                                    @endif

                                    <!-- Date -->
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                                        {{ \Carbon\Carbon::parse($trx->date)->format('d M Y') }}
                                        <div class="text-xs text-gray-400">{{ \Carbon\Carbon::parse($trx->created_at)->format('H:i') }}</div>
                                        <div class="mt-1 font-mono text-xs font-semibold text-gray-600">{{ $trx->invoice_no }}</div>
                                    </td>
                                    
                                    <!-- Transaction header details -->
                                    <td class="px-6 py-4 text-sm">
                                        <div class="font-bold text-gray-900">{{ $trx->user_name }}</div>
                                        @if($tab === 'IN')
                                            <div class="text-xs text-gray-500">{{ $trx->source_name ?? '-' }} · {{ $trx->source_type ?? '-' }}</div>
                                            @if($trx->purchase_order)<div class="text-xs text-gray-500">PO: {{ $trx->purchase_order }}</div>@endif
                                        @else
                                            <div class="text-xs text-gray-500">{{ $trx->notes ?? '' }}</div>
                                        @endif
                                    </td>

                                    <!-- Qty -->
                                    <td class="px-6 py-4 whitespace-nowrap text-center">
                                        <span class="px-3 py-1 inline-flex text-sm leading-5 font-bold rounded-full {{ $tab === 'OUT' ? 'bg-red-100 text-red-800' : 'bg-green-100 text-green-800' }}">
                                            {{ $details->count() }} item{{ $details->count() === 1 ? '' : 's' }}
                                        </span>
                                    </td>

                                    <!-- Dynamic Columns Content -->
                                    @if($tab === 'OUT')
                                        <td class="px-6 py-4 whitespace-nowrap text-sm font-bold text-blue-700">
                                            {{ $trx->fleet_number }}
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-700">
                                            {{ $trx->requester_name }}
                                        </td>
                                    @else
                                        <td class="px-6 py-4 whitespace-nowrap text-sm font-bold text-gray-900">
                                            {{ $trx->source_name ?? '-' }}
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500 capitalize">
                                            {{ $trx->source_type }}
                                        </td>
                                    @endif

                                    <!-- Proof Image Link & Action Buttons -->
                                    <td class="px-6 py-4 whitespace-nowrap text-right text-sm font-medium">
                                        <div class="flex items-center justify-end gap-3"> 
                                            @if($details->isNotEmpty())
                                                <button type="button" class="text-blue-700 hover:text-blue-900 font-semibold" aria-expanded="false" aria-controls="details-{{ $trx->id }}" onclick="toggleTransactionDetails(this, 'details-{{ $trx->id }}')">View details ({{ $details->count() }})</button>
                                            @endif
                                            @if($trx->status === 'completed')
                                                @if($trx->proof_image_before_url && file_exists(public_path($trx->proof_image_before_url)))
                                                    <a href="{{ asset($trx->proof_image_before_url) }}" target="_blank" class="text-amber-600 hover:text-amber-800 font-semibold">Before</a>
                                                @endif
                                                @if($trx->proof_image_url && file_exists(public_path($trx->proof_image_url)))
                                                    <a href="{{ asset($trx->proof_image_url) }}" target="_blank" class="text-blue-600 hover:text-blue-900 font-semibold">{{ $tab === 'OUT' ? 'After' : 'View' }}</a>
                                                @endif
                                            @else
                                                <span class="text-gray-400 text-xs italic">Canceled</span>
                                            @endif
                                        </div>
                                    </td>
                                </tr>
                                <tr id="details-{{ $trx->id }}" class="hidden bg-gray-50">
                                    <td colspan="{{ $tab === 'OUT' ? 7 : 6 }}" class="px-6 py-4">
                                        <div class="overflow-x-auto rounded border border-gray-200 bg-white">
                                            <table class="min-w-full text-sm">
                                                <thead class="bg-gray-100 text-xs uppercase text-gray-500"><tr><th class="px-4 py-2 text-left">Part</th><th class="px-4 py-2 text-left">Part number</th><th class="px-4 py-2 text-right">Qty</th>@if($tab === 'OUT')<th class="px-4 py-2 text-center">Before</th><th class="px-4 py-2 text-center">After</th>@endif</tr></thead>
                                                <tbody class="divide-y divide-gray-100">
                                                    @foreach($details as $detail)
                                                        @php
                                                            $beforeList = $detail->proof_image_before_url ? (str_starts_with(trim($detail->proof_image_before_url), '[') ? (json_decode($detail->proof_image_before_url, true) ?? [$detail->proof_image_before_url]) : [$detail->proof_image_before_url]) : [];
                                                            $afterList = $detail->proof_image_url ? (str_starts_with(trim($detail->proof_image_url), '[') ? (json_decode($detail->proof_image_url, true) ?? [$detail->proof_image_url]) : [$detail->proof_image_url]) : [];
                                                            $beforeItems = collect($beforeList)->values()->map(fn($p, $i) => ['url' => route('transactions.proof', ['detail' => $detail->id, 'type' => 'before', 'index' => $i]), 'isPdf' => str_ends_with(strtolower($p), '.pdf')])->all();
                                                            $afterItems = collect($afterList)->values()->map(fn($p, $i) => ['url' => route('transactions.proof', ['detail' => $detail->id, 'type' => 'after', 'index' => $i]), 'isPdf' => str_ends_with(strtolower($p), '.pdf')])->all();
                                                        @endphp
                                                        <tr>
                                                            <td class="px-4 py-2 font-medium text-gray-900">{{ $detail->part_name }}</td>
                                                            <td class="px-4 py-2 text-gray-500">{{ $detail->part_number ?? '-' }}</td>
                                                            <td class="px-4 py-2 text-right font-semibold">{{ $detail->qty }} {{ $detail->unit }}</td>
                                                            @if($tab === 'OUT')
                                                                <td class="px-4 py-2 text-center">
                                                                    @if(count($beforeItems) > 0)
                                                                        <button type="button" onclick='openProofModal(@json($detail->part_name), "Before", @json($beforeItems))' class="text-amber-700 hover:text-amber-900 font-semibold underline">
                                                                            Lihat Before{{ count($beforeItems) > 1 ? ' (' . count($beforeItems) . ')' : '' }}
                                                                        </button>
                                                                    @else
                                                                        <span class="text-gray-400">-</span>
                                                                    @endif
                                                                </td>
                                                                <td class="px-4 py-2 text-center">
                                                                    @if(count($afterItems) > 0)
                                                                        <button type="button" onclick='openProofModal(@json($detail->part_name), "After", @json($afterItems))' class="text-blue-700 hover:text-blue-900 font-semibold underline">
                                                                            Lihat After{{ count($afterItems) > 1 ? ' (' . count($afterItems) . ')' : '' }}
                                                                        </button>
                                                                    @else
                                                                        <span class="text-gray-400">-</span>
                                                                    @endif
                                                                </td>
                                                            @endif
                                                        </tr>
                                                    @endforeach
                                                </tbody>
                                            </table>
                                        </div>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="6" class="px-6 py-10 text-center text-gray-500 bg-white">
                                        <div class="flex flex-col items-center justify-center">
                                            <svg class="h-12 w-12 text-gray-300 mb-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 002-2M9 5a2 2 0 012 2m-3 7h3m-3 4h3m-6-4h.01M9 16h.01"></path></svg>
                                            <p class="text-lg font-medium">No transactions found.</p>
                                            <p class="text-sm mt-1">Try adjusting your search or filters.</p>
                                        </div>
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
                
                <!-- Pagination -->
                <div class="px-6 py-4 bg-gray-50 border-t border-gray-200">
                    {{ $transactions->withQueryString()->links() }} 
                </div>
            </div>
        </div>
    </div>

    <!-- MODAL PREVIEW PROOF (BEFORE / AFTER) -->
    <div id="proofGalleryModal" class="fixed inset-0 z-50 hidden bg-black bg-opacity-60 flex items-center justify-center p-4" onclick="if (event.target === this) closeProofModal()">
        <div class="bg-white rounded-lg shadow-xl w-full p-6" style="max-width: 56rem;">
            <div class="flex items-center justify-between border-b border-gray-200 pb-3 mb-4">
                <h3 id="proofGalleryTitle" class="text-lg font-bold text-gray-900">Proof Photos</h3>
                <button type="button" onclick="closeProofModal()" class="text-gray-400 hover:text-gray-700 text-2xl leading-none font-bold">&times;</button>
            </div>
            <div id="proofGalleryGrid" style="display: grid; grid-template-columns: repeat(auto-fill, minmax(180px, 1fr)); gap: 1rem; max-height: 70vh; overflow-y: auto;"></div>
            <div class="mt-5 flex justify-end">
                <button type="button" onclick="closeProofModal()" class="px-4 py-2 bg-gray-200 text-gray-800 rounded hover:bg-gray-300 font-semibold transition">Close</button>
            </div>
        </div>
    </div>

    <!-- MODAL CETAK BAST BULK -->
    <div id="bastModal" class="fixed inset-0 z-50 hidden bg-black bg-opacity-50 flex items-center justify-center">
        <div class="bg-white rounded-lg shadow-xl p-6 max-w-md w-full mx-4">
            <h3 class="text-xl font-bold text-blue-700 mb-4 border-b-2 border-blue-700 pb-2">Generate BAST PDF</h3>
            
            <form id="bulkBastForm" action="{{ route('transactions.gi.bulkPdf') }}" method="POST" target="_blank" onsubmit="setTimeout(handleBastSubmit, 800)">
                @csrf
                <!-- Container untuk menampung ID yang dicentang -->
                <div id="hiddenInputsContainer"></div>

                <div class="mb-4">
                    <label class="block text-sm font-bold text-gray-700 mb-2">BAST Number</label>
                    <!-- Value diisi otomatis dari $nextBastNumber yang dikirim Controller -->
                    <input type="text" name="bast_number" class="w-full rounded-md border-gray-300 shadow-sm focus:border-blue-700 focus:ring-blue-700 font-mono text-sm" placeholder="e.g., 001/PPNLH0102/MTC-KNO/2026-S0" value="{{ $nextBastNumber ?? '' }}" required>
                    <p class="text-xs text-gray-400 mt-1 italic">*Auto-generated. You can edit this manually if needed.</p>
                </div>

                <div class="mb-4">
                    <label class="block text-sm font-bold text-gray-700 mb-2">BAST Date</label>
                    <input type="date" name="bast_date" id="bast_date" class="w-full rounded-md border-gray-300 shadow-sm focus:border-blue-700 focus:ring-blue-700" required>
                </div>

                <div class="mb-6">
                    <label class="block text-sm font-bold text-gray-700 mb-2">Receiving Team</label>
                    <select name="receiving_team" class="w-full rounded-md border-gray-300 shadow-sm focus:border-blue-700 focus:ring-blue-700" required>
                        <option value="" disabled selected>-- Select Team --</option>
                        <option value="Receiving & Storaging">Receiving & Storaging</option>
                        <option value="Refuelling">Refuelling</option>
                        <option value="General Affair">General Affair</option>
                        <option value="HSSE">HSSE</option>
                    </select>
                </div>

                <div class="flex justify-end gap-3">
                    <button type="button" onclick="closeBastModal()" class="px-4 py-2 bg-gray-200 text-gray-800 rounded hover:bg-gray-300 font-semibold transition">Cancel</button>
                    <button type="submit" class="inline-flex items-center px-4 py-2 bg-emerald-600 text-white rounded hover:bg-emerald-700 font-bold transition shadow-md">
                        <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4"></path></svg>
                        Download BAST
                    </button>
                </div>
            </form>
        </div>
    </div>

    <!-- JAVASCRIPT UNTUK MODAL & CHECKBOX -->
    <script>
        function toggleTransactionDetails(button, rowId) {
            const row = document.getElementById(rowId);
            const expanded = button.getAttribute('aria-expanded') === 'true';
            button.setAttribute('aria-expanded', String(!expanded));
            row.classList.toggle('hidden', expanded);
        }

        function toggleAllCheckboxes(source) {
            const checkboxes = document.querySelectorAll('.trx-checkbox');
            checkboxes.forEach(cb => {
                if(!cb.disabled) cb.checked = source.checked;
            });
        }

        function openBastModal() {
            const checkedBoxes = document.querySelectorAll('.trx-checkbox:checked');
            if (checkedBoxes.length === 0) {
                alert("Please select at least one completed transaction to generate BAST.");
                return;
            }

            // Memindahkan nilai ID yang dicentang ke dalam form Modal
            const container = document.getElementById('hiddenInputsContainer');
            container.innerHTML = ''; 
            
            checkedBoxes.forEach(cb => {
                const input = document.createElement('input');
                input.type = 'hidden';
                input.name = 'selected_ids[]';
                input.value = cb.value;
                container.appendChild(input);
            });

            // Ambil date dari transaksi pertama yang dipilih (index 0)
            const firstCheckedDate = checkedBoxes[0].getAttribute('data-date');
            if (firstCheckedDate) {
                document.getElementById('bast_date').value = firstCheckedDate;
            } else {
                // Fallback jika tidak ada data-date, gunakan hari ini
                document.getElementById('bast_date').value = new Date().toISOString().split('T')[0];
            }

            document.getElementById('bastModal').classList.remove('hidden');
        }

        function handleBastSubmit() {
            closeBastModal();
            window.location.reload(); // Refresh halaman agar $nextBastNumber update
        }

        function closeBastModal() {
            document.getElementById('bastModal').classList.add('hidden');
        }

        function openProofModal(partName, label, items) {
            document.getElementById('proofGalleryTitle').textContent = `Foto ${label} — ${partName}`;
            const grid = document.getElementById('proofGalleryGrid');
            grid.innerHTML = '';

            items.forEach((item, idx) => {
                const link = document.createElement('a');
                link.href = item.url;
                link.target = '_blank';
                link.className = 'block border border-gray-200 rounded-lg overflow-hidden bg-gray-50 hover:border-blue-600 transition shadow-sm';

                if (item.isPdf) {
                    const pdfBox = document.createElement('div');
                    pdfBox.className = 'flex flex-col items-center justify-center bg-red-50 text-red-700 font-bold text-sm';
                    pdfBox.style.height = '180px';
                    pdfBox.textContent = `PDF ${idx + 1}`;
                    link.appendChild(pdfBox);
                } else {
                    const img = document.createElement('img');
                    img.src = item.url;
                    img.alt = `${label} ${idx + 1}`;
                    img.style.width = '100%';
                    img.style.height = '180px';
                    img.style.objectFit = 'cover';
                    link.appendChild(img);
                }

                const caption = document.createElement('div');
                caption.className = 'px-2 py-1.5 text-xs text-center text-gray-700 font-semibold bg-white border-t border-gray-100';
                caption.textContent = `${label} ${idx + 1}`;
                link.appendChild(caption);

                grid.appendChild(link);
            });

            document.getElementById('proofGalleryModal').classList.remove('hidden');
        }

        function closeProofModal() {
            document.getElementById('proofGalleryModal').classList.add('hidden');
            document.getElementById('proofGalleryGrid').innerHTML = '';
        }
    </script>
</x-app-layout>
