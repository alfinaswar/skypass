<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-blue-700 leading-tight">
            {{ __('Profile') }}
        </h2>
    </x-slot>

    <div class="py-12 bg-gray-50">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8 space-y-6">
            
            <!-- Alert Sukses (Mendukung session success maupun status bawaan Breeze) -->
            @if (session('success') || session('status') === 'password-updated' || session('status') === 'profile-updated')
                <div class="bg-white border-l-4 border-green-600 p-4 shadow-sm rounded-r-md flex items-center">
                    <svg class="h-6 w-6 text-green-600 mr-3 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path>
                    </svg>
                    <div>
                        <p class="font-bold text-gray-800">Success!</p>
                        <p class="text-sm text-gray-600">
                            @if (session('status') === 'password-updated')
                                Password has been updated successfully!
                            @elseif (session('status') === 'profile-updated')
                                Profile information has been updated successfully!
                            @else
                                {{ session('success') }}
                            @endif
                        </p>
                    </div>
                </div>
            @endif

            <!-- Alert Gagal / Error Validasi Password -->
            @if ($errors->updatePassword->any())
                <div class="bg-white border-l-4 border-red-600 p-4 shadow-sm rounded-r-md flex items-start">
                    <svg class="h-6 w-6 text-red-600 mr-3 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                    </svg>
                    <div>
                        <p class="font-bold text-gray-800">Failed to Update Password!</p>
                        <ul class="text-sm text-red-600 list-disc list-inside mt-1 space-y-0.5">
                            @foreach ($errors->updatePassword->all() as $error)
                                <li>{{ $error }}</li>
                            @endforeach
                        </ul>
                    </div>
                </div>
            @endif

            <div class="p-4 sm:p-8 bg-white shadow sm:rounded-lg">
                <div class="max-w-xl">
                    @include('profile.partials.update-profile-information-form')
                </div>
            </div>

            <div class="p-4 sm:p-8 bg-white shadow sm:rounded-lg">
                <div class="max-w-xl">
                    @include('profile.partials.update-password-form')
                </div>
            </div>

            <!-- <div class="p-4 sm:p-8 bg-white shadow sm:rounded-lg">
                <div class="max-w-xl">
                    @include('profile.partials.delete-user-form')
                </div>
            </div> -->
        </div>
    </div>
</x-app-layout>