<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-blue-700 leading-tight">
            {{ __('Sparepart List') }}
        </h2>
    </x-slot>

    <div class="py-12 bg-gray-50">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            
            <!-- ALERT SUCCESS -->
            @if(session('success'))
                <div class="mb-6 bg-white border-l-4 border-green-600 p-4 shadow-sm relative rounded-r-md">
                    <div class="flex">
                        <div class="flex-shrink-0">
                            <svg class="h-5 w-5 text-green-600" viewBox="0 0 20 20" fill="currentColor">
                                <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"/>
                            </svg>
                        </div>
                        <div class="ml-3">
                            <p class="text-sm text-gray-800 font-bold">Success</p>
                            <p class="text-sm text-gray-600">{{ session('success') }}</p>
                        </div>
                    </div>
                </div>
            @endif

            @if(session('error'))
                <div class="mb-6 bg-white border-l-4 border-red-600 p-4 shadow-sm rounded-r-md flex items-center">
                    <svg class="h-6 w-6 text-red-600 mr-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
                    <div><p class="font-bold text-gray-800">Error</p><p class="text-sm text-gray-600">{{ session('error') }}</p></div>
                </div>
            @endif

            <!-- TOOLBAR: SEARCH & ADD BUTTON -->
            <div class="flex flex-col md:flex-row justify-between items-center mb-6 gap-4">
                
                <!-- Search Form & Layout Button -->
                <div class="w-full md:w-1/2 flex gap-3">
                    <!-- TAMBAHKAN ID searchForm -->
                    <form action="{{ route('spareparts.index') }}" method="GET" class="flex-grow" id="searchForm">
                        <div class="flex rounded-md shadow-sm">
                            <div class="relative flex-grow focus-within:z-10">
                                <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                                    <svg class="h-5 w-5 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/>
                                    </svg>
                                </div>
                                <!-- TAMBAHKAN ID searchInput, autocomplete="off", dan perbarui placeholder -->
                                <input type="text" name="search" id="searchInput" value="{{ request('search') }}" 
                                    class="w-full rounded-none rounded-l-md border-gray-300 focus:border-blue-700 focus:ring-blue-700 pl-10 py-2 sm:text-sm transition-colors" 
                                    placeholder="Enter name or part number" autocomplete="off">
                            </div>
                            <button type="submit" class="-ml-px relative inline-flex items-center px-4 py-2 border border-blue-700 text-sm font-medium rounded-r-md text-white bg-blue-700 hover:bg-blue-800 focus:outline-none focus:ring-1 focus:ring-blue-700 focus:border-blue-700">
                                <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/>
                                </svg>
                            </button>
                        </div>
                    </form>

                    <!-- Tombol Layout Gudang -->
                    <a href="{{ route('warehouse.layout') }}" 
                       class="inline-flex items-center px-4 py-2 bg-white border border-gray-300 rounded-md font-bold text-sm text-gray-700 hover:bg-gray-50 shadow-sm transition-all flex-shrink-0">
                        <svg class="h-5 w-5 mr-2 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"></path>
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"></path>
                        </svg>
                        Rack Layout
                    </a>
                </div>

                <!-- Action Buttons Group (Kanan - Rapat) -->
                <div class="flex gap-3 w-full md:w-auto justify-end">
                    <!-- DOWNLOAD BUTTON -->
                    <a href="{{ route('reports.exportStock') }}" 
                       class="inline-flex items-center px-4 py-2 bg-white border border-gray-300 rounded-md font-bold text-sm text-gray-700 tracking-widest hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500 transition-all shadow-sm">
                        <svg class="h-5 w-5 mr-2 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4"/>
                        </svg>
                        Download Stock Data
                    </a>

                    <!-- ADD BUTTON -->
                    <a href="{{ route('spareparts.create') }}" 
                       class="inline-flex items-center px-6 py-2 bg-blue-700 border border-transparent rounded-md font-bold text-sm text-white tracking-widest hover:bg-blue-800 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-700 transition-all shadow-md">
                        <svg class="h-5 w-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6"/>
                        </svg>
                        Add Sparepart
                    </a>
                </div>
            </div>

            <!-- CONTAINER TABEL (TAMBAHKAN ID tableContainer) -->
            <div id="tableContainer" class="bg-white overflow-hidden shadow-sm sm:rounded-lg border-t-4 border-blue-700 transition-opacity duration-300">
                <div class="overflow-x-auto">
                    <table class="min-w-full divide-y divide-gray-200">
                        <thead class="bg-gray-50">
                            <tr>
                                <th scope="col" class="px-6 py-4 text-left text-xs font-bold text-gray-500 uppercase tracking-wider">Image</th>
                                <th scope="col" class="px-6 py-4 text-left text-xs font-bold text-gray-500 uppercase tracking-wider">Item Info</th>
                                <th scope="col" class="px-6 py-4 text-left text-xs font-bold text-gray-500 uppercase tracking-wider">Category</th>
                                <th scope="col" class="px-6 py-4 text-center text-xs font-bold text-gray-500 uppercase tracking-wider">Stock</th>
                                <th scope="col" class="px-6 py-4 text-left text-xs font-bold text-gray-500 uppercase tracking-wider">Location</th>
                                <th scope="col" class="px-6 py-4 text-right text-xs font-bold text-gray-500 uppercase tracking-wider">Actions</th>
                            </tr>
                        </thead>
                        <tbody class="bg-white divide-y divide-gray-200">
                            @forelse ($spareparts as $item)
                                <!-- -- LOGIKA WARNA BARIS: Cek usage_30_days vs limit -- -->
                                @php
                                    // 1. Definisikan Default (Hijau/Aman)
                                    // Kita pisahkan BG dan BORDER
                                    $bgClass = 'bg-gray-50 hover:bg-gray-100'; 
                                    $borderClass = 'border-l-4 border-green-400';
                                    $badge = null;
                                    
                                    // 2. Cek Logika High/Medium Usage
                                    if (isset($item->usage_30_days)) {
                                        if ($item->usage_30_days >= $item->limit_high_usage) {
                                            // Merah (High)
                                            $borderClass = 'border-l-4 border-red-500';
                                            $badge = 'high';
                                        } elseif ($item->usage_30_days >= $item->limit_medium_usage) {
                                            // Kuning (Medium)
                                            $borderClass = 'border-l-4 border-yellow-400';
                                            $badge = 'medium';
                                        }
                                    }
                                @endphp

                                <!-- Terapkan kelas warna ke TR -->
                                <tr class="transition-colors {{ $bgClass }}">
                                    <td class="px-6 py-4 whitespace-nowrap {{ $borderClass }}">
                                        <div class="flex-shrink-0 h-16 w-16 bg-gray-100 rounded-md overflow-hidden border border-gray-200">
                                            @if($item->image_url && file_exists(public_path($item->image_url)))
                                                <img class="h-16 w-16 object-cover" src="{{ asset($item->image_url) }}" alt="Photo">
                                            @else
                                                <div class="flex items-center justify-center h-full text-gray-400">
                                                    <svg class="h-8 w-8" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z"/></svg>
                                                </div>
                                            @endif
                                        </div>
                                    </td>

                                    <td class="px-6 py-4">
                                        <div class="text-sm font-bold text-gray-900">
                                            <a href="{{ route('spareparts.show', $item->id) }}" class="hover:text-blue-600 hover:underline">
                                                {{ $item->description }}
                                            </a>
                                        </div>
                                        <div class="text-sm text-gray-500">{{ $item->part_number ?? '-' }}</div>
                                        
                                        @if($badge === 'high')
                                            <span class="inline-flex items-center px-2 py-0.5 rounded text-xs font-bold bg-red-200 text-red-800 mt-1">
                                                Fast Moving (High)
                                            </span>
                                        @elseif($badge === 'medium')
                                            <span class="inline-flex items-center px-2 py-0.5 rounded text-xs font-bold bg-yellow-200 text-yellow-800 mt-1">
                                                Fast Moving (Medium)
                                            </span>
                                        @endif
                                    </td>

                                    <!-- Category Column -->
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        @if($item->category == 'rotating')
                                            <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full bg-red-100 text-red-800">
                                                Rotating
                                            </span>
                                        @else
                                            <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full bg-blue-100 text-blue-800">
                                                Consumable
                                            </span>
                                        @endif
                                    </td>

                                    <!-- Stock Column (With Color Indicator) -->
                                    <td class="px-6 py-4 whitespace-nowrap text-center">
                                        @php
                                            $stockColor = 'text-gray-900';
                                            if ($item->current_stock <= $item->min_stock) {
                                                $stockColor = 'text-red-600 font-bold animate-pulse'; // Critical
                                            } elseif ($item->current_stock <= $item->min_stock + 2) {
                                                $stockColor = 'text-yellow-600 font-bold'; // Warning
                                            }
                                        @endphp
                                        <div class="text-lg {{ $stockColor }}">
                                            {{ $item->current_stock }}
                                        </div>
                                        <div class="text-xs text-gray-500">{{ $item->unit }}</div>
                                    </td>

                                    <!-- Location Column -->
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-700">
                                        {{ $item->rack_location }}
                                    </td>

                                    <!-- Actions Column (Role Based) -->
                                    <td class="px-6 py-4 whitespace-nowrap text-right text-sm font-medium">
                                        @if(auth()->user()->role === 'supervisor')
                                            <!-- Supervisor: Edit & Delete -->
                                            <a href="{{ route('spareparts.edit', $item->id) }}" class="text-blue-600 hover:text-blue-900 mr-3 font-bold">Edit</a>
                                            
                                            <form action="{{ route('spareparts.destroy', $item->id) }}" method="POST" class="inline-block" onsubmit="return confirm('Are you sure you want to delete this item?');">
                                                @csrf
                                                @method('DELETE')
                                                <button type="submit" class="text-red-600 hover:text-red-900 font-bold">Delete</button>
                                            </form>
                                        @else
                                            <!-- Admin: Issue Button (Text Only Style) -->
                                            <a href="{{ route('transactions.gi.create', ['part_id' => $item->id]) }}" 
                                               class="text-blue-600 hover:text-blue-900 font-bold">
                                                Submit GI
                                            </a>
                                        @endif
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="6" class="px-6 py-10 text-center text-gray-500">
                                        <p class="text-lg">No spareparts found.</p>
                                        <p class="text-sm">Click "Add Sparepart" to create one.</p>
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
                
                <!-- Pagination -->
                <div class="px-6 py-4 bg-gray-50 border-t border-gray-200">
                    {{ $spareparts->withQueryString()->links() }} 
                </div>
            </div>
        </div>
    </div>

    <!-- JAVASCRIPT UNTUK LIVE SEARCH AJAX -->
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const searchInput = document.getElementById('searchInput');
            const tableContainer = document.getElementById('tableContainer');
            const searchForm = document.getElementById('searchForm');
            let searchTimeout;

            // Cegah form reload saat menekan Enter
            searchForm.addEventListener('submit', function(e) {
                e.preventDefault();
            });

            searchInput.addEventListener('input', function() {
                clearTimeout(searchTimeout);
                const query = this.value;

                // Eksekusi jika kosong (untuk reset) atau jika >= 2 karakter
                if (query.length === 1) return;

                // Tambahkan efek visual memudar sebagai indikator loading
                tableContainer.style.opacity = '0.4';

                // Delay 400ms (debounce) agar tidak spamming server saat user mengetik cepat
                searchTimeout = setTimeout(() => {
                    // Buat URL dengan query string
                    const url = new URL(window.location.href);
                    if (query) {
                        url.searchParams.set('search', query);
                    } else {
                        url.searchParams.delete('search');
                    }
                    
                    // Reset pagination ke page 1 saat mencari
                    url.searchParams.delete('page');

                    // Lakukan AJAX Fetch
                    fetch(url, {
                        headers: {
                            'X-Requested-With': 'XMLHttpRequest' // Tanda AJAX request
                        }
                    })
                    .then(response => response.text())
                    .then(html => {
                        // Membaca response HTML dan mengambil bagian tabelnya saja
                        const parser = new DOMParser();
                        const doc = parser.parseFromString(html, 'text/html');
                        const newTableContainer = doc.getElementById('tableContainer');
                        
                        if (newTableContainer) {
                            // Timpa isi tabel lama dengan tabel baru
                            tableContainer.innerHTML = newTableContainer.innerHTML;
                        }
                        
                        // Kembalikan opacity menjadi normal
                        tableContainer.style.opacity = '1';

                        // Update URL di address bar tanpa reload halaman
                        window.history.pushState({}, '', url);
                    })
                    .catch(error => {
                        console.error('Search failed:', error);
                        tableContainer.style.opacity = '1';
                    });
                }, 400); 
            });
        });
    </script>
</x-app-layout>