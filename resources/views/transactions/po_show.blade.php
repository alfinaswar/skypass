<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-blue-700 leading-tight">
            {{ __('Purchase Order Details') }}
        </h2>
    </x-slot>

    <div class="py-12 bg-gray-50">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            
            <!-- BACK BUTTON -->
            <div class="mb-6">
                <a href="{{ route('transactions.po.index') }}" class="inline-flex items-center text-gray-600 hover:text-blue-700 transition font-medium">
                    <svg class="h-5 w-5 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"></path></svg>
                    Back to PO List
                </a>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
                
                <!-- LEFT COLUMN: PO INFO & BAST -->
                <div class="md:col-span-1 space-y-6">
                    <!-- PO Info Card -->
                    <div class="bg-white p-6 rounded-lg shadow-sm border-t-4 border-blue-700">
                        <h3 class="text-gray-500 text-xs font-bold uppercase mb-1 tracking-wide">PO Number</h3>
                        <p class="text-2xl font-black text-gray-900 mb-6 truncate" title="{{ $header->purchase_order }}">
                            {{ $header->purchase_order }}
                        </p>

                        <div class="border-t border-gray-100 pt-4 space-y-4">
                            <div>
                                <p class="text-xs text-gray-400 uppercase font-bold mb-1">Vendor</p>
                                <p class="text-gray-800 font-medium break-words">{{ $header->source_name }}</p>
                            </div>
                            <div>
                                <p class="text-xs text-gray-400 uppercase font-bold mb-1">Reciept Date</p>
                                <p class="text-gray-800 font-medium">{{ \Carbon\Carbon::parse($header->date)->format('d F Y') }}</p>
                            </div>
                            <div>
                                <p class="text-xs text-gray-400 uppercase font-bold mb-1">Total Items</p>
                                <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-bold bg-blue-100 text-blue-800">
                                    {{ count($transactions) }} Items
                                </span>
                            </div>
                        </div>
                    </div>

                    <!-- BAST PREVIEW Card -->
                    <div class="bg-white p-6 rounded-lg shadow-sm border-t-4 border-green-600">
                        <h3 class="text-gray-900 font-bold mb-4 flex items-center">
                            <svg class="w-5 h-5 mr-2 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path></svg>
                            BAST Document
                        </h3>
                        
                        @if($header->proof_image_url && file_exists(public_path($header->proof_image_url)))
                            @php
                                $ext = strtolower(pathinfo($header->proof_image_url, PATHINFO_EXTENSION));
                            @endphp

                            @if($ext === 'pdf')
                                <!-- PREVIEW PDF (Dengan menggunakan tag embed) -->
                                <div class="w-full h-[350px] rounded-lg border border-gray-200 overflow-hidden bg-gray-100 mb-3 relative">
                                    <!-- Fallback Ikon Dokumen jika browser tidak mendukung iframe -->
                                    <div class="absolute inset-0 flex flex-col items-center justify-center text-gray-400 z-0">
                                        <svg class="h-12 w-12 mb-2" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path></svg>
                                        <p class="text-sm font-medium">PDF Document</p>
                                    </div>
                                    <!-- Reader PDF -->
                                    <embed src="{{ asset($header->proof_image_url) }}" type="application/pdf" width="100%" height="100%" class="relative z-10">
                                </div>

                                <a href="{{ asset($header->proof_image_url) }}" target="_blank" class="flex items-center justify-center w-full px-4 py-2 bg-blue-50 border border-blue-200 rounded-md font-semibold text-xs text-blue-700 uppercase tracking-widest hover:bg-blue-100 transition ease-in-out duration-150">
                                    <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 21h7a2 2 0 002-2V9.414a1 1 0 00-.293-.707l-5.414-5.414A1 1 0 0012.586 3H7a2 2 0 00-2 2v11m0 5l4.879-4.879m0 0a3 3 0 104.243-4.242 3 3 0 00-4.243 4.242z"></path></svg>
                                    Open Full PDF
                                </a>
                            @else
                                <!-- PREVIEW GAMBAR LAMA -->
                                <a href="{{ asset($header->proof_image_url) }}" target="_blank" class="block group relative overflow-hidden rounded-lg border border-gray-200">
                                    <img src="{{ asset($header->proof_image_url) }}" class="w-full h-auto object-cover group-hover:opacity-90 transition">
                                    <div class="absolute inset-0 flex items-center justify-center bg-black bg-opacity-0 group-hover:bg-opacity-30 transition">
                                        <span class="bg-white px-3 py-1 rounded-full text-xs font-bold shadow opacity-0 group-hover:opacity-100 transition transform scale-90 group-hover:scale-100 text-gray-800">
                                            Click to Zoom
                                        </span>
                                    </div>
                                </a>
                            @endif

                            <!-- DOWNLOAD BUTTON (Shared Button) -->
                            <div class="mt-4">
                                <a href="{{ asset($header->proof_image_url) }}" download class="flex items-center justify-center w-full px-4 py-2 bg-gray-100 border border-gray-300 rounded-md font-semibold text-xs text-gray-700 uppercase tracking-widest shadow-sm hover:bg-gray-200 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2 transition ease-in-out duration-150">
                                    <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4"></path></svg>
                                    Download BAST
                                </a>
                            </div>

                        @else
                            <div class="flex flex-col items-center justify-center h-32 bg-gray-50 rounded-lg border-2 border-dashed border-gray-300">
                                <svg class="h-8 w-8 text-gray-400 mb-2" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z"></path></svg>
                                <p class="text-xs text-gray-500 font-medium">No BAST Uploaded</p>
                            </div>
                        @endif
                    </div>
                </div>

                <!-- RIGHT COLUMN: ITEMS TABLE -->
                <div class="md:col-span-2">
                    <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg border border-gray-200">
                        <div class="px-6 py-4 border-b border-gray-200 bg-gray-50 flex justify-between items-center">
                            <h3 class="font-bold text-gray-700">Items in this PO</h3>
                        </div>
                        <div class="overflow-x-auto">
                            <table class="min-w-full divide-y divide-gray-200">
                                <thead class="bg-white">
                                    <tr>
                                        <th class="px-6 py-3 text-left text-xs font-bold text-gray-500 uppercase tracking-wider">Part Info</th>
                                        <th class="px-6 py-3 text-left text-xs font-bold text-gray-500 uppercase tracking-wider">Invoice No & Date</th>
                                        <th class="px-6 py-3 text-center text-xs font-bold text-gray-500 uppercase tracking-wider">Qty</th>
                                        <th class="px-6 py-3 text-left text-xs font-bold text-gray-500 uppercase tracking-wider">Received By</th>
                                    </tr>
                                </thead>
                                <tbody class="bg-white divide-y divide-gray-200">
                                    @foreach($transactions as $item)
                                        <tr class="hover:bg-gray-50 transition-colors">
                                            <td class="px-6 py-4">
                                                <div class="text-sm font-bold text-gray-900">{{ $item->part_name }}</div>
                                                <div class="text-xs text-gray-500 font-mono">{{ $item->part_number ?? '-' }}</div>
                                            </td>
                                            <td class="px-6 py-4 text-sm font-mono text-gray-500">
                                                {{ $item->invoice_no }}
                                                <div class="text-[12px] text-gray-500 mt-0.5">{{ \Carbon\Carbon::parse($item->created_at)->format('d M H:i') }}</div>
                                            </td>
                                            <td class="px-6 py-4 text-center">
                                                <span class="inline-flex items-center px-3 py-1 rounded-full text-xs font-bold bg-green-100 text-green-800">
                                                    {{ $item->qty }} {{ $item->unit }}
                                                </span>
                                            </td>
                                            <td class="px-6 py-4 text-sm text-gray-500">
                                                <div class="flex items-center">
                                                    <svg class="w-4 h-4 mr-1.5 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"></path></svg>
                                                    {{ $item->user_name }}
                                                </div>
                                            </td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>

            </div>
        </div>
    </div>
</x-app-layout>