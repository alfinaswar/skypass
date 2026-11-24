<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-blue-700 leading-tight">
            {{ __('Sparepart Details') }}
        </h2>
    </x-slot>

    <div class="py-12 bg-gray-50">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            
            <!-- TOMBOL BACK -->
            <div class="mb-6">
                <a href="{{ route('spareparts.index') }}" class="inline-flex items-center text-gray-600 hover:text-blue-700 transition">
                    <svg class="h-5 w-5 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"></path></svg>
                    Back to List
                </a>
            </div>

            <!-- BAGIAN 1: INFORMASI DETAIL -->
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg border-t-4 border-blue-700 mb-8">
                <div class="p-6 md:flex md:gap-8">
                    <!-- Kolom Kiri: Gambar Besar -->
                    <div class="md:w-1/3 flex-shrink-0 mb-6 md:mb-0">
                        <div class="bg-gray-100 rounded-lg overflow-hidden border border-gray-200 aspect-square flex items-center justify-center relative group">
                            @if($sparepart->image_url && file_exists(public_path($sparepart->image_url)))
                                <!-- Link Pembungkus Gambar -->
                                <a href="{{ asset($sparepart->image_url) }}" target="_blank" class="block w-full h-full relative cursor-zoom-in" title="Click to open full image">
                                    <img src="{{ asset($sparepart->image_url) }}" alt="{{ $sparepart->description }}" class="w-full h-full object-contain transition-opacity group-hover:opacity-90">
                                    
                                    <!-- Overlay Icon Zoom saat Hover -->
                                    <div class="absolute inset-0 flex items-center justify-center opacity-0 group-hover:opacity-100 transition-opacity bg-black bg-opacity-10 pointer-events-none">
                                        <svg class="h-10 w-10 text-white drop-shadow-md" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0zM10 7v3m0 0v3m0-3h3m-3 0H7"></path>
                                        </svg>
                                    </div>
                                </a>
                            @else
                                <svg class="h-24 w-24 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z"/>
                                </svg>
                            @endif
                        </div>
                    </div>

                    <!-- Kolom Kanan: Info -->
                    <div class="md:w-2/3">
                        <div class="flex justify-between items-start">
                            <div>
                                <h1 class="text-3xl font-bold text-gray-900 mb-2">{{ $sparepart->description }}</h1>
                                <p class="text-sm text-gray-500 font-mono mb-4">Part Number: {{ $sparepart->part_number ?? 'N/A' }}</p>
                            </div>
                            <!-- Badge Kategori -->
                            @if($sparepart->category == 'rotating')
                                <span class="px-3 py-1 text-sm font-bold rounded-full bg-red-100 text-red-800">Rotating</span>
                            @else
                                <span class="px-3 py-1 text-sm font-bold rounded-full bg-blue-100 text-blue-800">Consumable</span>
                            @endif
                        </div>

                        <div class="grid grid-cols-2 gap-6 mt-4 border-t border-gray-100 pt-4">
                            <div>
                                <p class="text-sm font-medium text-gray-500">Current Stock</p>
                                @php
                                    $stockColor = 'text-gray-900';
                                    if ($sparepart->current_stock <= $sparepart->min_stock) {
                                        $stockColor = 'text-red-600 animate-pulse';
                                    } elseif ($sparepart->current_stock <= $sparepart->min_stock + 2) {
                                        $stockColor = 'text-yellow-600';
                                    }
                                @endphp
                                <p class="text-4xl font-black {{ $stockColor }}">{{ $sparepart->current_stock }} <span class="text-lg font-normal text-gray-500">{{ $sparepart->unit }}</span></p>
                            </div>
                            <div>
                                <p class="text-sm font-medium text-gray-500">Rack Location</p>
                                <p class="text-xl font-bold text-gray-800">{{ $sparepart->rack_location }}</p>
                            </div>
                            <div>
                                <p class="text-sm font-medium text-gray-500">Min. Stock Alert</p>
                                <p class="text-lg font-semibold text-gray-700">{{ $sparepart->min_stock }} {{ $sparepart->unit }}</p>
                            </div>
                            <div>
                                <p class="text-sm font-medium text-gray-500">Usage Limits (Red/Yellow)</p>
                                <p class="text-lg font-semibold text-gray-700">
                                    > {{ $sparepart->limit_high_usage }} / > {{ $sparepart->limit_medium_usage }}
                                </p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- BAGIAN 2: TABEL TRANSAKSI -->
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg border-t-4 border-gray-400">
                <div class="p-6 border-b border-gray-200">
                    <h3 class="text-lg font-bold text-gray-900 mb-4">Transaction History for this Item</h3>
                    
                    <div class="overflow-x-auto">
                        <table class="min-w-full divide-y divide-gray-200">
                            <thead class="bg-gray-50">
                                <tr>
                                    <th class="px-6 py-3 text-left text-xs font-bold text-gray-500 uppercase">Date</th>
                                    <th class="px-6 py-3 text-left text-xs font-bold text-gray-500 uppercase">Invoice</th>
                                    <th class="px-6 py-3 text-center text-xs font-bold text-gray-500 uppercase">Type</th>
                                    <th class="px-6 py-3 text-center text-xs font-bold text-gray-500 uppercase">Qty</th>
                                    <th class="px-6 py-3 text-left text-xs font-bold text-gray-500 uppercase">Detail (Source/Facility)</th>
                                    <th class="px-6 py-3 text-left text-xs font-bold text-gray-500 uppercase">Admin</th>
                                </tr>
                            </thead>
                            <tbody class="bg-white divide-y divide-gray-200">
                                @forelse($transactions as $trx)
                                    <tr class="hover:bg-gray-50">
                                        <td class="px-6 py-3 text-sm text-gray-900 whitespace-nowrap">
                                            {{ \Carbon\Carbon::parse($trx->date)->format('d M Y') }}
                                        </td>
                                        <td class="px-6 py-3 text-sm font-mono text-gray-500 whitespace-nowrap">
                                            {{ $trx->invoice_no }}
                                        </td>
                                        <td class="px-6 py-3 text-center">
                                            @if($trx->type == 'IN')
                                                <span class="px-2 py-1 text-xs font-bold rounded bg-green-100 text-green-800">IN</span>
                                            @else
                                                <span class="px-2 py-1 text-xs font-bold rounded bg-red-100 text-red-800">OUT</span>
                                            @endif
                                        </td>
                                        <td class="px-6 py-3 text-center font-bold text-gray-800">
                                            {{ $trx->qty }}
                                        </td>
                                        <td class="px-6 py-3 text-sm text-gray-700">
                                            @if($trx->type == 'IN')
                                                @if($trx->source_type == 'procurement')
                                                    <span class="font-semibold">{{ $trx->source_name }}</span> (Vendor)
                                                @elseif($trx->source_type == 'cannibalization')
                                                    From: <span class="font-semibold text-blue-600">{{ $trx->source_name }}</span>
                                                @else
                                                    {{ ucfirst($trx->source_type) }}
                                                @endif
                                            @else
                                                To: <span class="font-semibold text-blue-600">{{ $trx->fleet_number }}</span>
                                                <div class="text-xs text-gray-500">By: {{ $trx->requester_name }}</div>
                                            @endif
                                        </td>
                                        <td class="px-6 py-3 text-sm text-gray-500">
                                            {{ $trx->admin_name }}
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="6" class="px-6 py-8 text-center text-gray-500">
                                            No transactions found for this item yet.
                                        </td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>

                    <!-- Pagination Khusus Tabel Ini -->
                    <div class="mt-4">
                        {{ $transactions->links() }}
                    </div>
                </div>
            </div>

        </div>
    </div>
</x-app-layout>