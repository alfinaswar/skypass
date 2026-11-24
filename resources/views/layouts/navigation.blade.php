<nav x-data="{ open: false }" class="bg-blue-600 border-b border-blue-700" style="background-color: #006BB8; border-bottom-color: #005a9e;">
    <!-- Primary Navigation Menu -->
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="flex justify-between h-16">
            <div class="flex">
                <!-- Logo Aplikasi -->
                <div class="shrink-0 flex items-center">
                    <a href="{{ route('dashboard') }}" class="flex flex-row items-center gap-2.5">
                        <img src="{{ asset('favicon.ico') }}" alt="Favicon" class="h-8 w-8 object-contain flex-shrink-0">
                        <!-- Ganti Logo Laravel jadi Teks Putih -->
                        <span class="text-white font-black text-2xl tracking-wider" style="margin: 0; line-height: 1;">
                            stockx<span style="color: #A6C210;">.in</span>
                        </span>
                    </a>
                </div>

                <!-- Navigation Links (Desktop) -->
                <!-- Menambahkan margin-left: 50px agar ada jarak tegas dari logo -->
                <div class="hidden space-x-8 sm:-my-px sm:ml-10 sm:flex" style="margin-left: 50px;">
                    <!-- Dashboard Link -->
                    <x-nav-link :href="route('dashboard')" :active="request()->routeIs('dashboard')" 
                        class="text-white hover:text-gray-200 border-transparent hover:border-white focus:border-white"
                        style="color: white;">
                        {{ __('Dashboard') }}
                    </x-nav-link>

                    <!-- Link Master Sparepart -->
                    <x-nav-link :href="route('spareparts.index')" :active="request()->routeIs('spareparts.*')" 
                        class="text-white hover:text-gray-200 border-transparent hover:border-white focus:border-white"
                        style="color: white;">
                        {{ __('Spareparts') }}
                    </x-nav-link>

                    <!-- Good Receipt Dropdown (BARU: Diubah jadi Dropdown) -->
                    <div class="relative flex items-center h-full" x-data="{ openGR: false }" @mouseleave="openGR = false">
                        <div @mouseover="openGR = true" class="h-full flex items-center">
                            <x-nav-link :href="route('transactions.gr.create')" :active="request()->routeIs('transactions.gr.*') || request()->routeIs('transactions.pendingGR')" 
                                class="text-white hover:text-gray-200 border-transparent hover:border-white focus:border-white cursor-pointer inline-flex items-center h-full"
                                style="color: white;">
                                {{ __('Good Receipt') }}
                                <svg class="ml-1 h-4 w-4 fill-current" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20">
                                    <path fill-rule="evenodd" d="M5.293 7.293a1 1 0 011.414 0L10 10.586l3.293-3.293a1 1 0 111.414 1.414l-4 4a1 1 0 01-1.414 0l-4-4a1 1 0 010-1.414z" clip-rule="evenodd" />
                                </svg>
                            </x-nav-link>
                        </div>

                        <!-- Submenu Dropdown GR -->
                        <div x-show="openGR" 
                             x-transition:enter="transition ease-out duration-200"
                             x-transition:enter-start="transform opacity-0 scale-95"
                             x-transition:enter-end="transform opacity-100 scale-100"
                             x-transition:leave="transition ease-in duration-75"
                             x-transition:leave-start="transform opacity-100 scale-100"
                             x-transition:leave-end="transform opacity-0 scale-95"
                             class="absolute z-50 w-48 rounded-md shadow-lg origin-top-left left-0"
                             style="top: 60px; display: none;">
                            <div class="rounded-md ring-1 ring-black ring-opacity-5 py-1 bg-white">
                                <!-- Submit New GR -->
                                <a href="{{ route('transactions.gr.create') }}" class="block px-4 py-2 text-sm leading-5 text-gray-700 hover:bg-gray-100 focus:outline-none focus:bg-gray-100 transition duration-150 ease-in-out">
                                    Submit New GR
                                </a>
                                <!-- Pending GR -->
                                <a href="{{ route('transactions.pendingGR') }}" class="block px-4 py-2 text-sm leading-5 text-gray-700 hover:bg-gray-100 focus:outline-none focus:bg-gray-100 transition duration-150 ease-in-out">
                                    Pending GR
                                </a>
                                <!-- Purchase Orders (BARU) -->
                                <a href="{{ route('transactions.po.index') }}" class="block px-4 py-2 text-sm leading-5 text-gray-700 hover:bg-gray-100 focus:outline-none focus:bg-gray-100 transition duration-150 ease-in-out border-t border-gray-100">
                                    Purchase Orders
                                </a>
                            </div>
                        </div>
                    </div>

                    <!-- Good Issue Dropdown (Barang Keluar) -->
                    <div class="relative flex items-center h-full" x-data="{ openGI: false }" @mouseleave="openGI = false">
                        <div @mouseover="openGI = true" class="h-full flex items-center">
                            <x-nav-link :href="route('transactions.gi.create')" :active="request()->routeIs('transactions.gi.*') || request()->routeIs('transactions.pending')" 
                                class="text-white hover:text-gray-200 border-transparent hover:border-white focus:border-white cursor-pointer inline-flex items-center h-full"
                                style="color: white;">
                                {{ __('Good Issue') }}
                                <svg class="ml-1 h-4 w-4 fill-current" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20">
                                    <path fill-rule="evenodd" d="M5.293 7.293a1 1 0 011.414 0L10 10.586l3.293-3.293a1 1 0 111.414 1.414l-4 4a1 1 0 01-1.414 0l-4-4a1 1 0 010-1.414z" clip-rule="evenodd" />
                                </svg>
                            </x-nav-link>
                        </div>

                        <!-- Submenu Dropdown GI -->
                        <div x-show="openGI" 
                             x-transition:enter="transition ease-out duration-200"
                             x-transition:enter-start="transform opacity-0 scale-95"
                             x-transition:enter-end="transform opacity-100 scale-100"
                             x-transition:leave="transition ease-in duration-75"
                             x-transition:leave-start="transform opacity-100 scale-100"
                             x-transition:leave-end="transform opacity-0 scale-95"
                             class="absolute z-50 w-48 rounded-md shadow-lg origin-top-left left-0"
                             style="top: 60px; display: none;">
                            <div class="rounded-md ring-1 ring-black ring-opacity-5 py-1 bg-white">
                                <!-- Submit New Issue -->
                                <a href="{{ route('transactions.gi.create') }}" class="block px-4 py-2 text-sm leading-5 text-gray-700 hover:bg-gray-100 focus:outline-none focus:bg-gray-100 transition duration-150 ease-in-out">
                                    Submit New GI
                                </a>
                                <!-- Pending GI -->
                                <a href="{{ route('transactions.pending') }}" class="block px-4 py-2 text-sm leading-5 text-gray-700 hover:bg-gray-100 focus:outline-none focus:bg-gray-100 transition duration-150 ease-in-out">
                                    Pending GI
                                </a>
                            </div>
                        </div>
                    </div>

                    <!-- Transaction History (Riwayat) -->
                    <x-nav-link :href="route('transactions.index')" :active="request()->routeIs('transactions.index')" 
                        class="text-white hover:text-gray-200 border-transparent hover:border-white focus:border-white"
                        style="color: white;">
                        {{ __('Transactions') }}
                    </x-nav-link>

                </div>
            </div>

            <!-- Settings & Notification Dropdown (Pojok Kanan) -->
            <div class="hidden sm:flex sm:items-center sm:ml-6">
                
                <!-- NOTIFICATION BELL (SUPERVISOR ONLY) -->
                @if(Auth::user()->role === 'supervisor')
                    <div class="relative mr-4" x-data="{ openNotify: false }" @click.outside="openNotify = false">
                        <button @click="openNotify = !openNotify" class="relative p-1 rounded-full text-blue-200 hover:text-white focus:outline-none focus:ring-2 focus:ring-white">
                            <span class="sr-only">View notifications</span>
                            <!-- Bell Icon -->
                            <svg class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" aria-hidden="true">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M14.857 17.082a23.848 23.848 0 005.454-1.31A8.967 8.967 0 0118 9.75v-.7V9A6 6 0 006 9v.75a8.967 8.967 0 01-2.312 6.022c1.733.64 3.56 1.085 5.455 1.31m5.714 0a24.255 24.255 0 01-5.714 0m5.714 0a3 3 0 11-5.714 0" />
                            </svg>
                            
                            <!-- Badge Hitungan (Hanya muncul jika ada unread) -->
                            @if(Auth::user()->unreadNotifications->count() > 0)
                                <span class="absolute top-0 right-0 block h-4 w-4 rounded-full bg-red-600 ring-2 ring-white text-[10px] font-bold text-center text-white leading-tight">
                                    {{ Auth::user()->unreadNotifications->count() }}
                                </span>
                            @endif
                        </button>

                        <!-- Notification Dropdown -->
                        <div x-show="openNotify" 
                            x-transition:enter="transition ease-out duration-200"
                            x-transition:enter-start="transform opacity-0 scale-95"
                            x-transition:enter-end="transform opacity-100 scale-100"
                            x-transition:leave="transition ease-in duration-75"
                            x-transition:leave-start="transform opacity-100 scale-100"
                            x-transition:leave-end="transform opacity-0 scale-95"
                            class="absolute right-0 z-50 mt-2 w-80 origin-top-right rounded-md bg-white py-1 shadow-lg ring-1 ring-black ring-opacity-5 focus:outline-none" 
                            style="display: none;">
                            
                            <!-- Header Dropdown (UPDATED) -->
                            <div class="px-4 py-2 border-b border-gray-100 flex justify-between items-center">
                                <div class="flex items-center gap-2">
                                    <h3 class="text-sm font-bold text-gray-700">Notifications</h3>
                                    
                                    <!-- TOMBOL CLEAR ALL (X) -->
                                    @if(Auth::user()->notifications->count() > 0)
                                        <form action="{{ route('notifications.clear') }}" method="POST" onsubmit="return confirm('Clear all notifications?');">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit" class="text-gray-400 hover:text-red-500 transition-colors focus:outline-none" title="Clear All">
                                                <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path></svg>
                                            </button>
                                        </form>
                                    @endif
                                </div>

                                <!-- Tombol Read All (Hanya jika ada unread) -->
                                @if(Auth::user()->unreadNotifications->count() > 0)
                                    <form action="{{ route('notifications.readAll') }}" method="POST">
                                        @csrf
                                        <button type="submit" class="text-xs text-blue-600 hover:text-blue-800 font-semibold hover:underline">
                                            Read All
                                        </button>
                                    </form>
                                @endif
                            </div>

                            <!-- List Notifikasi (Scrollable) -->
                            <div class="max-h-64 overflow-y-auto">
                                @forelse(Auth::user()->notifications as $notification)
                                    <div class="px-4 py-3 border-b border-gray-50 last:border-0 {{ $notification->read_at ? 'bg-white' : 'bg-blue-50' }}">
                                        <p class="text-xs font-bold text-gray-800">
                                            {{ $notification->data['title'] ?? 'Notification' }}
                                        </p>
                                        <p class="text-xs text-gray-600 mt-1">
                                            {{ $notification->data['message'] ?? '' }}
                                        </p>
                                        <p class="text-[10px] text-gray-400 mt-1 text-right">
                                            {{ $notification->created_at->diffForHumans() }}
                                        </p>
                                    </div>
                                @empty
                                    <div class="px-4 py-4 text-center text-sm text-gray-500">
                                        No notifications.
                                    </div>
                                @endforelse
                            </div>
                        </div>
                    </div>
                @endif
                <!-- END NOTIFICATION -->

                <x-dropdown align="right" width="48">
                    <x-slot name="trigger">
                        <button class="inline-flex items-center px-3 py-2 border border-transparent text-sm leading-4 font-bold rounded-md text-[#006BB8] bg-white hover:bg-gray-100 focus:outline-none transition ease-in-out duration-150 shadow-sm" style="color: #006BB8;">
                            <!-- Nama User -->
                            <div>{{ Auth::user()->name }}</div>

                            <div class="ml-1">
                                <svg class="fill-current h-4 w-4" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20">
                                    <path fill-rule="evenodd" d="M5.293 7.293a1 1 0 011.414 0L10 10.586l3.293-3.293a1 1 0 111.414 1.414l-4 4a1 1 0 01-1.414 0l-4-4a1 1 0 010-1.414z" clip-rule="evenodd" />
                                </svg>
                            </div>
                        </button>
                    </x-slot>

                    <x-slot name="content">

                        <!-- TAMBAHAN: MENU ADMIN -->
                        @if(Auth::user()->role === 'supervisor')
                            <x-dropdown-link :href="route('admin.index')">
                                {{ __('Admin') }}
                            </x-dropdown-link>
                        @endif
                        
                        <x-dropdown-link :href="route('profile.edit')">
                            {{ __('Profile') }}
                        </x-dropdown-link>

                        <!-- Authentication -->
                        <form method="POST" action="{{ route('logout') }}">
                            @csrf

                            <x-dropdown-link :href="route('logout')"
                                    onclick="event.preventDefault();
                                                this.closest('form').submit();">
                                {{ __('Log Out') }}
                            </x-dropdown-link>
                        </form>
                    </x-slot>
                </x-dropdown>
            </div>

            <!-- Hamburger (Mobile Menu Button) -->
            <div class="-mr-2 flex items-center sm:hidden">
                <button @click="open = ! open" class="inline-flex items-center justify-center p-2 rounded-md text-white hover:text-gray-200 hover:bg-blue-700 focus:outline-none transition duration-150 ease-in-out">
                    <svg class="h-6 w-6" stroke="currentColor" fill="none" viewBox="0 0 24 24">
                        <path :class="{'hidden': open, 'inline-flex': ! open }" class="inline-flex" stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16" />
                        <path :class="{'hidden': ! open, 'inline-flex': open }" class="hidden" stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                    </svg>
                </button>
            </div>
        </div>
    </div>

    <!-- Responsive Navigation Menu (Mobile) -->
    <div :class="{'block': open, 'hidden': ! open}" class="hidden sm:hidden bg-blue-700" style="background-color: #005a9e;">
        <div class="pt-2 pb-3 space-y-1">
            <x-responsive-nav-link :href="route('dashboard')" :active="request()->routeIs('dashboard')" class="text-white hover:bg-blue-800 border-l-4 border-transparent hover:border-white" style="color: white;">
                {{ __('Dashboard') }}
            </x-responsive-nav-link>

            <x-responsive-nav-link :href="route('spareparts.index')" :active="request()->routeIs('spareparts.*')" class="text-white hover:bg-blue-800 border-l-4 border-transparent hover:border-white" style="color: white;">
                {{ __('Spareparts') }}
            </x-responsive-nav-link>

            <!-- BARU: Good Receipt Group (Mobile) -->
             <div class="border-t border-[#004a8e] mt-1 pt-1 pb-1">
                <div class="px-4 py-2 text-xs font-bold text-blue-200 uppercase tracking-wider">Good Receipt</div>
                <x-responsive-nav-link :href="route('transactions.gr.create')" :active="request()->routeIs('transactions.gr.*')" class="text-white hover:bg-[#004a8e] border-l-4 border-transparent hover:border-white pl-8">
                    {{ __('Submit New GR') }}
                </x-responsive-nav-link>
                <x-responsive-nav-link :href="route('transactions.pendingGR')" :active="request()->routeIs('transactions.pendingGR*')" class="text-white hover:bg-[#004a8e] border-l-4 border-transparent hover:border-white pl-8">
                    {{ __('Pending GR') }}
                </x-responsive-nav-link>
                <!-- New PO Link -->
                <x-responsive-nav-link :href="route('transactions.po.index')" :active="request()->routeIs('transactions.po.*')" class="text-white hover:bg-[#004a8e] border-l-4 border-transparent hover:border-white pl-8">
                    {{ __('Purchase Orders') }}
                </x-responsive-nav-link>
            </div>

            <!-- Good Issue Group (Mobile) -->
            <div class="border-t border-[#004a8e] mt-1 pt-1 pb-1">
                <div class="px-4 py-2 text-xs font-bold text-blue-200 uppercase tracking-wider">Good Issue</div>
                <x-responsive-nav-link :href="route('transactions.gi.create')" :active="request()->routeIs('transactions.gi.*')" class="text-white hover:bg-[#004a8e] border-l-4 border-transparent hover:border-white pl-8">
                    {{ __('Submit New GI') }}
                </x-responsive-nav-link>
                <x-responsive-nav-link :href="route('transactions.pending')" :active="request()->routeIs('transactions.pending*')" class="text-white hover:bg-[#004a8e] border-l-4 border-transparent hover:border-white pl-8">
                    {{ __('Pending GI') }}
                </x-responsive-nav-link>
            </div>

            <x-responsive-nav-link :href="route('transactions.index')" :active="request()->routeIs('transactions.index')" class="text-white hover:bg-blue-800 border-l-4 border-transparent hover:border-white" style="color: white;">
                {{ __('Transactions') }}
            </x-responsive-nav-link>

        </div>

        <!-- Responsive Settings Options -->
        <div class="pt-4 pb-1 border-t border-blue-800" style="border-top-color: #004a8e;">
            <div class="px-4">
                <div class="font-medium text-base text-white">{{ Auth::user()->name }}</div>
                <div class="font-medium text-sm text-blue-200">{{ Auth::user()->username }}</div>
            </div>
            
            <!-- Mobile Notifications Link (Simple version) - SUPERVISOR ONLY -->
            @if(Auth::user()->role === 'supervisor')
             <div class="mt-3 px-4">
                <div class="flex items-center justify-between text-blue-100 text-sm">
                    <span>Notifications</span>
                    @if(Auth::user()->unreadNotifications->count() > 0)
                        <span class="bg-red-600 text-white rounded-full px-2 py-0.5 text-xs">{{ Auth::user()->unreadNotifications->count() }} new</span>
                    @else
                        <span class="text-xs">No new</span>
                    @endif
                </div>
            </div>
            @endif

            <div class="mt-3 space-y-1">
                <x-responsive-nav-link :href="route('profile.edit')" class="text-blue-100 hover:text-white">
                    {{ __('Profile') }}
                </x-responsive-nav-link>

                <!-- Authentication -->
                <form method="POST" action="{{ route('logout') }}">
                    @csrf

                    <x-responsive-nav-link :href="route('logout')" class="text-blue-100 hover:text-white"
                            onclick="event.preventDefault();
                                        this.closest('form').submit();">
                        {{ __('Log Out') }}
                    </x-responsive-nav-link>
                </form>
            </div>
        </div>
    </div>
</nav>