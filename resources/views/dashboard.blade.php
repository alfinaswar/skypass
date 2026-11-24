<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-blue-700 leading-tight">
            {{ __('Dashboard Overview') }}
        </h2>
    </x-slot>

    <div class="py-12 bg-gray-50">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            
            <!-- 1. SUMMARY CARDS -->
            <div class="grid grid-cols-1 md:grid-cols-3 gap-6 mb-8">
                <!-- Card 1: Total Items -->
                <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg border-l-4 border-blue-700 p-6">
                    <div class="flex items-center">
                        <div class="p-3 rounded-full bg-blue-100 text-blue-700 mr-4">
                            <svg class="h-8 w-8" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4"></path></svg>
                        </div>
                        <div>
                            <p class="text-sm font-medium text-gray-500">Total Spareparts</p>
                            <p class="text-3xl font-bold text-gray-900">{{ $totalItems }}</p>
                        </div>
                    </div>
                </div>

                <!-- Card 2: Critical Stock -->
                <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg border-l-4 border-red-600 p-6">
                    <div class="flex items-center">
                        <div class="p-3 rounded-full bg-red-100 text-red-600 mr-4">
                            <svg class="h-8 w-8" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"></path></svg>
                        </div>
                        <div>
                            <p class="text-sm font-medium text-gray-500">Critical Stock</p>
                            <p class="text-3xl font-bold text-red-600">{{ $criticalStockCount }}</p>
                        </div>
                    </div>
                </div>

                <!-- Card 3: Pending Issues -->
                <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg border-l-4 border-yellow-500 p-6">
                    <div class="flex items-center">
                        <div class="p-3 rounded-full bg-yellow-100 text-yellow-600 mr-4">
                            <svg class="h-8 w-8" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
                        </div>
                        <div>
                            <div class="flex items-center divide-x divide-gray-300">
                                <div class="pr-4 text-center">
                                    <p class="text-xs font-medium text-gray-500 uppercase">Pending GI</p>
                                    <p class="text-2xl font-bold text-gray-900">{{ $pendingIssuesCount }}</p>
                                </div>
                                <div class="pl-4 text-center">
                                    <p class="text-xs font-medium text-gray-500 uppercase">Pending GR</p>
                                    <p class="text-2xl font-bold text-gray-900">{{ $pendingGRCount }}</p>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- 2. MONTHLY TRANSACTION CHART (UPDATED) -->
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg mb-8 border-t-4 border-gray-400">
                <div class="p-6 border-b border-gray-200">
                    
                    <!-- Header Flex: Judul Kiri, Dropdown Kanan -->
                    <div class="flex flex-col md:flex-row justify-between items-center mb-6">
                        <h3 class="text-lg font-bold text-gray-900 mb-2 md:mb-0">
                            Transaction Activity: {{ \Carbon\Carbon::createFromFormat('Y-m', $selectedMonth)->format('F Y') }}
                        </h3>
                        
                        <!-- Filter Form -->
                        <form action="{{ route('dashboard') }}" method="GET" class="flex items-center">
                            <label for="month" class="mr-2 text-sm text-gray-600 font-medium">Select Period:</label>
                            <select name="month" id="month" onchange="this.form.submit()" 
                                class="rounded-md border-gray-300 shadow-sm focus:border-blue-700 focus:ring-blue-700 text-sm">
                                @foreach($availableMonths as $value => $label)
                                    <option value="{{ $value }}" {{ $selectedMonth == $value ? 'selected' : '' }}>
                                        {{ $label }}
                                    </option>
                                @endforeach
                            </select>
                        </form>
                    </div>

                    <div class="w-full h-80">
                        <canvas id="transactionChart"></canvas>
                    </div>
                </div>
            </div>

            <div class="grid grid-cols-1 lg:grid-cols-3 gap-8 mb-8">
                
                <!-- 3. CRITICAL STOCK TABLE (Left - 2/3 Width) -->
                <div class="lg:col-span-2 bg-white overflow-hidden shadow-sm sm:rounded-lg border-t-4 border-red-600">
                    <div class="p-6 border-b border-gray-200">
                        <h3 class="text-lg font-bold text-gray-900 mb-4 flex items-center">
                            <span class="bg-red-100 text-red-600 p-1 rounded mr-2">
                                <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z"></path></svg>
                            </span>
                            Critical Stock Items (Low Inventory)
                        </h3>
                        <div class="overflow-x-auto">
                            <table class="min-w-full divide-y divide-gray-200">
                                <thead class="bg-gray-50">
                                    <tr>
                                        <th class="px-4 py-3 text-left text-xs font-bold text-gray-500 uppercase">Part Info</th>
                                        <th class="px-4 py-3 text-left text-xs font-bold text-gray-500 uppercase">Location</th>
                                        <th class="px-4 py-3 text-center text-xs font-bold text-gray-500 uppercase">Status</th>
                                        <th class="px-4 py-3 text-right text-xs font-bold text-gray-500 uppercase">Action</th>
                                    </tr>
                                </thead>
                                <tbody class="bg-white divide-y divide-gray-200">
                                    @forelse($criticalItems as $item)
                                        <tr>
                                            <td class="px-4 py-3">
                                                <div class="text-sm font-bold text-gray-900">{{ $item->description }}</div>
                                                <div class="text-xs text-gray-500">{{ $item->part_number ?? '-' }}</div>
                                            </td>
                                            <td class="px-4 py-3 text-sm text-gray-500">{{ $item->rack_location }}</td>
                                            <td class="px-4 py-3 text-center">
                                                <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full bg-red-100 text-red-800">
                                                    {{ $item->current_stock }} / {{ $item->min_stock }} {{ $item->unit }}
                                                </span>
                                            </td>
                                            <td class="px-4 py-3 text-right">
                                                <a href="{{ route('transactions.gr.create') }}" class="text-blue-600 hover:text-blue-900 text-xs font-bold">Restock</a>
                                            </td>
                                        </tr>
                                    @empty
                                        <tr>
                                            <td colspan="4" class="px-4 py-4 text-center text-gray-500 text-sm">All stock levels are healthy!</td>
                                        </tr>
                                    @endforelse
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>

                <!-- 4. PENDING ISSUES TABLE (Right - 1/3 Width) -->
                <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg border-t-4 border-yellow-500">
                    <div class="p-6 border-b border-gray-200">
                        <div class="flex justify-between items-center mb-4">
                            <h3 class="text-lg font-bold text-gray-900">Pending Good Issues</h3>
                            <a href="{{ route('transactions.pending') }}" class="text-xs text-blue-600 hover:underline">View All</a>
                        </div>
                        
                        <div class="overflow-y-auto max-h-64">
                            @forelse($pendingIssues as $pending)
                                <div class="flex items-center justify-between border-b border-gray-100 pb-3 mb-3 last:border-0 last:mb-0 last:pb-0">
                                    <div>
                                        <div class="text-sm font-bold text-gray-800">{{ $pending->invoice_no }}</div>
                                        <div class="text-xs text-gray-500">{{ \Carbon\Carbon::parse($pending->date)->format('d M') }} | {{ $pending->fleet_number }}</div>
                                    </div>
                                    <a href="{{ route('transactions.pending') }}" class="px-3 py-1 bg-yellow-100 text-yellow-800 text-xs font-bold rounded-full hover:bg-yellow-200">
                                        Verify
                                    </a>
                                </div>
                            @empty
                                <div class="text-center text-gray-500 text-sm py-4">No pending transactions.</div>
                            @endforelse
                        </div>
                    </div>
                </div>
            </div>

            <!-- 5. RECENT TRANSACTIONS (Full Width) -->
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg border-t-4 border-gray-400">
                <div class="p-6 border-b border-gray-200">
                    <div class="flex justify-between items-center mb-4">
                        <h3 class="text-lg font-bold text-gray-900">Recent Activity</h3>
                        <a href="{{ route('transactions.index') }}" class="text-sm text-blue-700 hover:underline font-semibold">View Full History &rarr;</a>
                    </div>
                    
                    <div class="overflow-x-auto">
                        <table class="min-w-full divide-y divide-gray-200">
                            <thead class="bg-gray-50">
                                <tr>
                                    <th class="px-6 py-3 text-left text-xs font-bold text-gray-500 uppercase">Date</th>
                                    <th class="px-6 py-3 text-left text-xs font-bold text-gray-500 uppercase">Type</th>
                                    <th class="px-6 py-3 text-left text-xs font-bold text-gray-500 uppercase">Item</th>
                                    <th class="px-6 py-3 text-center text-xs font-bold text-gray-500 uppercase">Qty</th>
                                    <th class="px-6 py-3 text-right text-xs font-bold text-gray-500 uppercase">Status</th>
                                </tr>
                            </thead>
                            <tbody class="bg-white divide-y divide-gray-200">
                                @foreach($recentTransactions as $trx)
                                    <tr class="hover:bg-gray-50">
                                        <td class="px-6 py-3 text-sm text-gray-500">
                                            {{ \Carbon\Carbon::parse($trx->created_at)->format('d M H:i') }}
                                        </td>
                                        <td class="px-6 py-3 text-sm">
                                            @if($trx->type == 'IN')
                                                <span class="text-green-600 font-bold bg-green-50 px-2 py-0.5 rounded">IN</span>
                                            @else
                                                <span class="text-red-600 font-bold bg-red-50 px-2 py-0.5 rounded">OUT</span>
                                            @endif
                                        </td>
                                        <td class="px-6 py-3 text-sm text-gray-900 font-medium">
                                            {{ $trx->item_name }}
                                            <span class="text-xs text-gray-400 block">{{ $trx->invoice_no }}</span>
                                        </td>
                                        <td class="px-6 py-3 text-center text-sm font-bold">
                                            {{ $trx->qty }} {{ $trx->unit }}
                                        </td>
                                        <td class="px-6 py-3 text-right text-xs">
                                            @if($trx->status == 'completed')
                                                <span class="text-green-600 font-bold">Completed</span>
                                            @elseif($trx->status == 'pending')
                                                <span class="text-yellow-600 font-bold">Pending</span>
                                            @else
                                                <span class="text-red-500 font-bold">Canceled</span>
                                            @endif
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

    <!-- SCRIPT UNTUK CHART (LINE) -->
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const ctx = document.getElementById('transactionChart').getContext('2d');
            
            // Data dari Controller
            const labels = @json($chartDates);
            const dataIn = @json($chartDataIn); // Data Barang Masuk
            const dataOut = @json($chartDataOut); // Data Barang Keluar

            new Chart(ctx, {
                type: 'line', // UBAH KE LINE CHART
                data: {
                    labels: labels,
                    datasets: [
                        {
                            label: 'Good Reciept (GR)',
                            data: dataIn,
                            borderColor: '#16a34a', // Warna Garis Hijau
                            backgroundColor: 'rgba(22, 163, 74, 0.1)', // Warna Fill Tipis
                            pointBackgroundColor: '#16a34a',
                            tension: 0.3, // Garis melengkung halus
                            fill: true
                        },
                        {
                            label: 'Good Issued (GI)',
                            data: dataOut,
                            borderColor: '#dc2626', // Warna Garis Merah
                            backgroundColor: 'rgba(220, 38, 38, 0.1)', // Warna Fill Tipis
                            pointBackgroundColor: '#dc2626',
                            tension: 0.3, // Garis melengkung halus
                            fill: true
                        }
                    ]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    scales: {
                        y: {
                            beginAtZero: true,
                            ticks: {
                                stepSize: 1 // Pastikan sumbu Y angkanya bulat
                            },
                            title: {
                                display: true,
                                text: 'Transaction Count', // Label Sumbu Y
                                font: {
                                    weight: 'bold'
                                }
                            }
                        },
                        x: {
                            title: {
                                display: true,
                                text: 'Date', // Label Sumbu X
                                font: {
                                    weight: 'bold'
                                }
                            }
                        }
                    },
                    plugins: {
                        legend: {
                            position: 'top',
                        }
                    }
                }
            });
        });
    </script>
</x-app-layout>