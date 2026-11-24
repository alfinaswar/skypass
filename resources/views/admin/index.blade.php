    <x-app-layout>
        <x-slot name="header">
            <h2 class="font-semibold text-xl text-blue-700 leading-tight">
                {{ __('Administration Tools') }}
            </h2>
        </x-slot>

        <div class="py-12 bg-gray-50">
            <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
                
                <!-- ALERT MESSAGES -->
                @if(session('success'))
                    <div class="mb-6 bg-green-100 border-l-4 border-green-500 text-green-700 p-4 rounded shadow-sm flex items-center">
                        <svg class="h-6 w-6 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path></svg>
                        <div>
                            <p class="font-bold">Success</p>
                            <p>{{ session('success') }}</p>
                        </div>
                    </div>
                @endif
                @if(session('error') || $errors->any())
                    <div class="mb-6 bg-red-100 border-l-4 border-red-500 text-red-700 p-4 rounded shadow-sm">
                        <div class="flex items-center mb-2">
                            <svg class="h-6 w-6 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
                            <p class="font-bold">Error</p>
                        </div>
                        <p>{{ session('error') }}</p>
                        <ul class="list-disc list-inside ml-2">
                            @foreach ($errors->all() as $error) <li>{{ $error }}</li> @endforeach
                        </ul>
                    </div>
                @endif

                <!-- TABS NAVIGATION -->
                <div class="flex border-b border-gray-300 mb-6 overflow-x-auto overflow-y-hidden">
                    <!-- Tab 1: User Manager -->
                    <a href="{{ route('admin.index', ['tab' => 'users']) }}" 
                    class="px-6 py-3 font-bold text-sm rounded-t-lg transition-colors border-t border-l border-r whitespace-nowrap
                    {{ $tab === 'users' ? 'bg-white text-blue-700 border-gray-300 -mb-px' : 'bg-gray-200 text-gray-500 hover:bg-gray-100 border-transparent' }}">
                    User Manager
                    </a>
                    
                    <!-- Tab 4: Manage Vehicles (BARU) -->
                    <a href="{{ route('admin.index', ['tab' => 'vehicles']) }}" 
                    class="px-6 py-3 font-bold text-sm rounded-t-lg transition-colors border-t border-l border-r ml-2 whitespace-nowrap
                    {{ $tab === 'vehicles' ? 'bg-white text-blue-700 border-gray-300 -mb-px' : 'bg-gray-200 text-gray-500 hover:bg-gray-100 border-transparent' }}">
                    Manage Facility
                    </a>

                    <!-- Tab 2: Transactions Removal -->
                    <a href="{{ route('admin.index', ['tab' => 'transactions']) }}" 
                    class="px-6 py-3 font-bold text-sm rounded-t-lg transition-colors border-t border-l border-r ml-2 whitespace-nowrap
                    {{ $tab === 'transactions' ? 'bg-white text-red-600 border-gray-300 -mb-px' : 'bg-gray-200 text-gray-500 hover:bg-gray-100 border-transparent' }}">
                    Transactions Removal
                    </a>

                    <!-- Tab 3: Backup/Restore -->
                    <a href="{{ route('admin.index', ['tab' => 'backup']) }}" 
                    class="px-6 py-3 font-bold text-sm rounded-t-lg transition-colors border-t border-l border-r ml-2 whitespace-nowrap
                    {{ $tab === 'backup' ? 'bg-white text-green-700 border-gray-300 -mb-px' : 'bg-gray-200 text-gray-500 hover:bg-gray-100 border-transparent' }}">
                    Backup / Restore
                    </a>
                </div>

                <!-- CONTENT AREA -->
                <div class="bg-white p-6 rounded-lg shadow-sm border border-gray-200 min-h-[400px]">
                    
                    <!-- ================= TAB 1: USER MANAGER ================= -->
                    @if($tab === 'users')
                        <div class="grid grid-cols-1 md:grid-cols-3 gap-8">
                            <!-- Form Tambah User -->
                            <div class="md:col-span-1 bg-gray-50 p-6 rounded-lg border border-gray-200 h-fit">
                                <h3 class="text-lg font-bold text-gray-800 mb-4 border-b border-gray-300 pb-2">Add New Warehouse Admin</h3>
                                <form action="{{ route('admin.users.store') }}" method="POST">
                                    @csrf
                                    <div class="mb-4">
                                        <label class="block text-xs font-bold text-gray-700 uppercase mb-1">Full Name</label>
                                        <input type="text" name="name" class="w-full rounded-md border-gray-300 shadow-sm text-sm focus:border-blue-500 focus:ring-blue-500" placeholder="e.g. John Doe" required>
                                    </div>
                                    <div class="mb-4">
                                        <label class="block text-xs font-bold text-gray-700 uppercase mb-1">Username</label>
                                        <input type="text" name="username" class="w-full rounded-md border-gray-300 shadow-sm text-sm focus:border-blue-500 focus:ring-blue-500" placeholder="e.g. admin2" required>
                                    </div>
                                    <div class="mb-4">
                                        <label class="block text-xs font-bold text-gray-700 uppercase mb-1">Password</label>
                                        <input type="password" name="password" class="w-full rounded-md border-gray-300 shadow-sm text-sm focus:border-blue-500 focus:ring-blue-500" placeholder="Min 6 characters" required>
                                    </div>
                                    <div class="mb-6">
                                        <label class="block text-xs font-bold text-gray-700 uppercase mb-1">Confirm Password</label>
                                        <input type="password" name="password_confirmation" class="w-full rounded-md border-gray-300 shadow-sm text-sm focus:border-blue-500 focus:ring-blue-500" placeholder="Retype password" required>
                                    </div>
                                    <button type="submit" class="w-full bg-blue-700 text-white font-bold py-2 px-4 rounded-md hover:bg-blue-800 text-sm transition shadow-sm">
                                        Create User
                                    </button>
                                </form>
                            </div>

                            <!-- List User -->
                            <div class="md:col-span-2">
                                <h3 class="text-lg font-bold text-gray-800 mb-4">Existing Admin Users</h3>
                                <div class="overflow-x-auto rounded-lg border border-gray-200">
                                    <table class="min-w-full divide-y divide-gray-200">
                                        <thead class="bg-gray-50">
                                            <tr>
                                                <th class="px-6 py-3 text-left text-xs font-bold text-gray-500 uppercase tracking-wider">Name</th>
                                                <th class="px-6 py-3 text-left text-xs font-bold text-gray-500 uppercase tracking-wider">Username</th>
                                                <th class="px-6 py-3 text-center text-xs font-bold text-gray-500 uppercase tracking-wider">Role</th>
                                                <th class="px-6 py-3 text-right text-xs font-bold text-gray-500 uppercase tracking-wider">Action</th>
                                            </tr>
                                        </thead>
                                        <tbody class="bg-white divide-y divide-gray-200">
                                            @forelse($users as $u)
                                                <tr class="hover:bg-gray-50">
                                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900 font-medium">{{ $u->name }}</td>
                                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-600">{{ $u->username }}</td>
                                                    <td class="px-6 py-4 whitespace-nowrap text-center">
                                                        <span class="px-2 py-1 rounded-full text-xs font-bold bg-blue-100 text-blue-800">Admin</span>
                                                    </td>
                                                    <td class="px-6 py-4 whitespace-nowrap text-right text-xs">
                                                        <button type="button" 
                                                            onclick="openResetPasswordModal('{{ $u->id }}', '{{ $u->name }}', '{{ $u->username }}')" 
                                                            class="text-blue-600 hover:text-blue-900 font-bold uppercase tracking-wide mr-3 transition">
                                                            Reset Password
                                                        </button>
                                                        <form action="{{ route('admin.users.destroy', $u->id) }}" method="POST" class="inline-block" onsubmit="return confirm('Are you sure you want to delete user {{ $u->name }}?');">
                                                            @csrf @method('DELETE')
                                                            <button type="submit" class="text-red-600 hover:text-red-900 font-bold uppercase tracking-wide">Remove</button>
                                                        </form>
                                                    </td>
                                                </tr>
                                            @empty
                                                <tr><td colspan="4" class="px-6 py-10 text-center text-gray-500 text-sm">No additional admin users found.</td></tr>
                                            @endforelse
                                        </tbody>
                                    </table>
                                </div>
                            </div>
                        </div>
                    @endif
                    
                    <!-- ================= TAB 4: MANAGE VEHICLES (BARU) ================= -->
                    @if($tab === 'vehicles')
                        <div class="grid grid-cols-1 md:grid-cols-3 gap-8">
                            <!-- Form Tambah Vehicle -->
                            <div class="md:col-span-1 bg-gray-50 p-6 rounded-lg border border-gray-200 h-fit">
                                <h3 class="text-lg font-bold text-gray-800 mb-4 border-b border-gray-300 pb-2">Add New Facility</h3>
                                <form action="{{ route('admin.vehicles.store') }}" method="POST">
                                    @csrf
                                    <div class="mb-4">
                                        <label class="block text-xs font-bold text-gray-700 uppercase mb-1">Facility Number</label>
                                        <input type="text" name="fleet_number" class="w-full rounded-md border-gray-300 shadow-sm text-sm focus:border-blue-500 focus:ring-blue-500" placeholder="e.g. KNA 123" required>
                                    </div>
                                    <button type="submit" class="w-full bg-blue-700 text-white font-bold py-2 px-4 rounded-md hover:bg-blue-800 text-sm transition shadow-sm">
                                        Add Facility
                                    </button>
                                </form>
                            </div>

                            <!-- List Vehicles -->
                            <div class="md:col-span-2">
                                <h3 class="text-lg font-bold text-gray-800 mb-4">Registered Facility</h3>
                                <div class="overflow-x-auto rounded-lg border border-gray-200">
                                    <table class="min-w-full divide-y divide-gray-200">
                                        <thead class="bg-gray-50">
                                            <tr>
                                                <th class="px-6 py-3 text-left text-xs font-bold text-gray-500 uppercase tracking-wider">Facility Number</th>
                                                <th class="px-6 py-3 text-left text-xs font-bold text-gray-500 uppercase tracking-wider">Created At</th>
                                                <th class="px-6 py-3 text-right text-xs font-bold text-gray-500 uppercase tracking-wider">Action</th>
                                            </tr>
                                        </thead>
                                        <tbody class="bg-white divide-y divide-gray-200">
                                            @forelse($vehicles as $v)
                                                <tr class="hover:bg-gray-50">
                                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900 font-bold">{{ $v->fleet_number }}</td>
                                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-600">{{ \Carbon\Carbon::parse($v->created_at)->format('d M Y') }}</td>
                                                    <td class="px-6 py-4 whitespace-nowrap text-right">
                                                        <form action="{{ route('admin.vehicles.destroy', $v->id) }}" method="POST" onsubmit="return confirm('Are you sure you want to delete facility {{ $v->fleet_number }}?');">
                                                            @csrf @method('DELETE')
                                                            <button type="submit" class="text-red-600 hover:text-red-900 font-bold text-xs uppercase tracking-wide">Delete</button>
                                                        </form>
                                                    </td>
                                                </tr>
                                            @empty
                                                <tr><td colspan="3" class="px-6 py-10 text-center text-gray-500 text-sm">No facility found.</td></tr>
                                            @endforelse
                                        </tbody>
                                    </table>
                                </div>
                            </div>
                        </div>
                    @endif

                    <!-- ================= TAB 2: TRANSACTIONS REMOVAL ================= -->
                    @if($tab === 'transactions')
                        <div>
                            <div class="flex flex-col md:flex-row justify-between items-center mb-6 gap-4">
                                <div class="flex items-center">
                                    <div class="p-2 bg-red-100 rounded-full mr-3 text-red-600">
                                        <svg class="h-6 w-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"></path></svg>
                                    </div>
                                    <h3 class="text-lg font-bold text-red-700">Delete Transactions</h3>
                                </div>
                                
                                <!-- Search Transaksi -->
                                <form action="{{ route('admin.index') }}" method="GET" class="w-full md:w-1/3 flex gap-2">
                                    <input type="hidden" name="tab" value="transactions">
                                    <div class="relative flex-grow">
                                        <input type="text" name="search" value="{{ request('search') }}" placeholder="Search Invoice / PO..." 
                                            class="w-full rounded-md border-gray-300 shadow-sm focus:border-red-500 focus:ring-red-500 text-sm pl-10">
                                        <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                                            <svg class="h-4 w-4 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/></svg>
                                        </div>
                                    </div>
                                    <button type="submit" class="px-4 py-2 bg-gray-800 text-white rounded-md hover:bg-gray-700 text-sm font-bold shadow-sm">Search</button>
                                </form>
                            </div>

                            <div class="bg-red-50 border-l-4 border-red-500 p-4 mb-6 rounded-r-md">
                                <div class="flex">
                                    <div class="flex-shrink-0">
                                        <svg class="h-5 w-5 text-red-500" viewBox="0 0 20 20" fill="currentColor"><path fill-rule="evenodd" d="M8.257 3.099c.765-1.36 2.722-1.36 3.486 0l5.58 9.92c.75 1.334-.213 2.98-1.742 2.98H4.42c-1.53 0-2.493-1.646-1.743-2.98l5.58-9.92zM11 13a1 1 0 11-2 0 1 1 0 012 0zm-1-8a1 1 0 00-1 1v3a1 1 0 002 0V6a1 1 0 00-1-1z" clip-rule="evenodd"/></svg>
                                    </div>
                                    <div class="ml-3">
                                        <p class="text-sm text-red-800 font-bold">Warning!</p>
                                        <p class="text-sm text-red-700">Deleting a transaction here will <strong>permanently remove</strong> the record and any associated BAST/Proof images from the server without reverting the stock.</p>
                                        <p class="text-sm text-red-700">This action cannot be undone.</p>
                                    </div>
                                </div>
                            </div>

                            <div class="overflow-x-auto rounded-lg border border-gray-200">
                                <table class="min-w-full divide-y divide-gray-200">
                                    <thead class="bg-gray-50">
                                        <tr>
                                            <th class="px-6 py-3 text-left text-xs font-bold text-gray-500 uppercase tracking-wider">Date</th>
                                            <th class="px-6 py-3 text-left text-xs font-bold text-gray-500 uppercase tracking-wider">Invoice / PO</th>
                                            <th class="px-6 py-3 text-center text-xs font-bold text-gray-500 uppercase tracking-wider">Type</th>
                                            <th class="px-6 py-3 text-center text-xs font-bold text-gray-500 uppercase tracking-wider">Status</th>
                                            <th class="px-6 py-3 text-right text-xs font-bold text-gray-500 uppercase tracking-wider">Action</th>
                                        </tr>
                                    </thead>
                                    <tbody class="bg-white divide-y divide-gray-200">
                                        @forelse($transactions as $t)
                                            <tr class="hover:bg-red-50 transition-colors">
                                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                                                    {{ \Carbon\Carbon::parse($t->date)->format('d M Y') }}
                                                    <div class="text-xs text-gray-400">{{ \Carbon\Carbon::parse($t->created_at)->format('H:i') }}</div>
                                                </td>
                                                <td class="px-6 py-4 text-sm font-mono text-gray-600">
                                                    <div class="font-bold text-gray-800">{{ $t->invoice_no }}</div>
                                                    @if($t->purchase_order) 
                                                        <div class="text-xs text-blue-600 mt-1 flex items-center">
                                                            <svg class="w-3 h-3 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path></svg>
                                                            {{ $t->purchase_order }}
                                                        </div>
                                                    @endif
                                                </td>
                                                <td class="px-6 py-4 whitespace-nowrap text-center text-sm font-bold {{ $t->type == 'IN' ? 'text-green-600' : 'text-red-600' }}">
                                                    {{ $t->type }}
                                                </td>
                                                <td class="px-6 py-4 whitespace-nowrap text-center text-xs">
                                                    <span class="px-2.5 py-0.5 rounded-full {{ $t->status == 'completed' ? 'bg-green-100 text-green-800' : ($t->status == 'pending' ? 'bg-yellow-100 text-yellow-800' : 'bg-gray-100 text-gray-800') }}">
                                                        {{ ucfirst($t->status) }}
                                                    </span>
                                                </td>
                                                <td class="px-6 py-4 whitespace-nowrap text-right">
                                                    <form action="{{ route('admin.transactions.destroy', $t->id) }}" method="POST" onsubmit="return confirm('PERMANENTLY DELETE transaction {{ $t->invoice_no }}? This cannot be undone.');">
                                                        @csrf @method('DELETE')
                                                        <button type="submit" class="bg-red-600 text-white px-3 py-1.5 rounded-md text-xs font-bold hover:bg-red-700 shadow-sm transition">DELETE</button>
                                                    </form>
                                                </td>
                                            </tr>
                                        @empty
                                            <tr><td colspan="5" class="px-6 py-10 text-center text-gray-500 text-sm">No transactions found matching your search.</td></tr>
                                        @endforelse
                                    </tbody>
                                </table>
                            </div>
                            <div class="mt-4">
                                {{ $transactions->appends(['tab' => 'transactions'])->links() }}
                            </div>
                        </div>
                    @endif

                    <!-- ================= TAB 3: BACKUP / RESTORE ================= -->
                    @if($tab === 'backup')
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-8">
                            
                            <!-- Download Section -->
                            <div class="bg-green-50 p-6 rounded-lg border border-green-200 shadow-sm flex flex-col h-full">
                                <div class="flex items-center mb-4">
                                    <div class="p-2 bg-green-200 rounded-full text-green-700 mr-3">
                                        <svg class="h-6 w-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4"></path></svg>
                                    </div>
                                    <h3 class="text-lg font-bold text-green-800">1. Backup Data</h3>
                                </div>
                                <p class="text-sm text-green-700 mb-6 flex-grow">
                                    Download a complete copy of the database (.sqlite) and all uploaded images (BAST, Proofs). Save this ZIP file safely in a secure location (e.g., Google Drive or External HDD).
                                </p>
                                <a href="{{ route('backup.download') }}" class="inline-flex justify-center items-center w-full px-6 py-3 bg-green-700 text-white font-bold rounded-md hover:bg-green-800 shadow-md transition">
                                    <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4"></path></svg>
                                    Download Backup (.ZIP)
                                </a>
                            </div>

                            <!-- Restore Section -->
                            <div class="bg-yellow-50 p-6 rounded-lg border border-yellow-200 shadow-sm flex flex-col h-full">
                                <div class="flex items-center mb-4">
                                    <div class="p-2 bg-yellow-200 rounded-full text-yellow-700 mr-3">
                                        <svg class="h-6 w-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"></path></svg>
                                    </div>
                                    <h3 class="text-lg font-bold text-yellow-800">2. Restore Data</h3>
                                </div>
                                <p class="text-sm text-yellow-800 mb-4 flex-grow">
                                    Upload a previously downloaded backup (.zip) file to restore the system. 
                                    <br><span class="font-bold text-red-600 block mt-2 p-2 bg-red-100 rounded border border-red-200">⚠️ WARNING: This will OVERWRITE all current data and images! This action cannot be undone.</span>
                                </p>
                                
                                <form action="{{ route('admin.restore') }}" method="POST" enctype="multipart/form-data" onsubmit="return confirm('CRITICAL WARNING:\n\nYou are about to overwrite the entire database and images with this backup.\n\nAll current data will be LOST.\n\nAre you absolutely sure you want to proceed?');">
                                    @csrf
                                    <div class="mb-4">
                                        <input type="file" name="backup_file" accept=".zip" class="block w-full text-sm text-gray-500
                                        file:mr-4 file:py-2 file:px-4
                                        file:rounded-md file:border-0
                                        file:text-sm file:font-semibold
                                        file:bg-yellow-200 file:text-yellow-800
                                        hover:file:bg-yellow-300 cursor-pointer" required>
                                    </div>
                                    <button type="submit" class="w-full bg-yellow-600 text-white font-bold py-3 rounded-md hover:bg-yellow-700 shadow-md transition flex justify-center items-center">
                                        <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"></path></svg>
                                        Restore Backup
                                    </button>
                                </form>
                            </div>

                        </div>
                    @endif

                </div>
            </div>
        </div>
        <!-- MODAL RESET PASSWORD (SUPERVISOR ONLY) -->
        <div id="resetPasswordModal" style="display: none;" class="fixed inset-0 z-50 bg-black bg-opacity-50 items-center justify-center p-4">
            <div class="bg-white rounded-lg shadow-xl p-6 max-w-md w-full mx-4 relative">
                <div class="flex items-center mb-4 border-b border-gray-100 pb-3">
                    <div class="p-2 bg-blue-100 rounded-full text-blue-700 mr-3">
                        <svg class="h-6 w-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 7a2 2 0 012 2m4 0a6 6 0 01-7.743 5.743L11 17H9v2H7v2H4a1 1 0 01-1-1v-2.586a1 1 0 01.293-.707l5.964-5.964A6 6 0 1121 9z" />
                        </svg>
                    </div>
                    <div>
                        <h3 class="text-lg font-bold text-gray-800">Set New Password</h3>
                        <p class="text-xs text-gray-500">For user: <span id="resetUserName" class="font-bold text-gray-800"></span> (<span id="resetUserUsername" class="font-mono text-blue-600 font-semibold"></span>)</p>
                    </div>
                </div>

                <form id="resetPasswordForm" method="POST" action="">
                    @csrf
                    @method('PUT')

                    <div class="mb-4">
                        <label class="block text-xs font-bold text-gray-700 uppercase mb-1">New Password</label>
                        <input type="password" name="password" class="w-full rounded-md border-gray-300 shadow-sm text-sm focus:border-blue-500 focus:ring-blue-500" placeholder="Min. 6 characters" required>
                    </div>

                    <div class="mb-6">
                        <label class="block text-xs font-bold text-gray-700 uppercase mb-1">Confirm New Password</label>
                        <input type="password" name="password_confirmation" class="w-full rounded-md border-gray-300 shadow-sm text-sm focus:border-blue-500 focus:ring-blue-500" placeholder="Retype new password" required>
                    </div>

                    <div class="flex justify-end gap-3">
                        <button type="button" onclick="closeResetPasswordModal()" class="px-4 py-2 bg-gray-200 text-gray-800 rounded hover:bg-gray-300 font-semibold text-sm transition">Cancel</button>
                        <button type="submit" class="px-4 py-2 bg-blue-700 text-white rounded hover:bg-blue-800 font-bold text-sm transition shadow-sm">Update Password</button>
                    </div>
                </form>
            </div>
        </div>

    <!-- SCRIPT JAVASCRIPT MODAL -->
    <script>
        function openResetPasswordModal(id, name, username) {
            document.getElementById('resetUserName').textContent = name;
            document.getElementById('resetUserUsername').textContent = username;
            document.getElementById('resetPasswordForm').action = "{{ url('/admin/users') }}/" + id + "/password";
            document.getElementById('resetPasswordModal').style.display = 'flex';
        }

        function closeResetPasswordModal() {
            document.getElementById('resetPasswordModal').style.display = 'none';
        }

        document.getElementById('resetPasswordModal')?.addEventListener('click', function(e) {
            if (e.target === this) {
                closeResetPasswordModal();
            }
        });
    </script>
    </x-app-layout>