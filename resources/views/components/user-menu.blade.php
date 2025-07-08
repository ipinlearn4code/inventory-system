@php
    $user = session('authenticated_user');
    $initials = $user ? strtoupper(substr($user['name'], 0, 1) . substr($user['name'], strpos($user['name'], ' ') + 1, 1)) : 'UN';
@endphp

<div class="flex items-center">
    <div x-data="{ open: false }" class="relative">
        <!-- Avatar Button -->
        <button 
            @click="open = !open" 
            @click.away="open = false"
            class="flex items-center space-x-3 p-2 rounded-lg hover:bg-gray-100 dark:hover:bg-gray-700 transition-colors"
        >
            <!-- Avatar -->
            <div class="w-8 h-8 bg-blue-600 rounded-full flex items-center justify-center text-white text-sm font-semibold">
                {{ $initials }}
            </div>
            
            <!-- User Info -->
            <div class="hidden md:block text-left">
                <div class="text-sm font-medium text-gray-900 dark:text-white">
                    {{ $user['name'] ?? 'Unknown User' }}
                </div>
                <div class="text-xs text-gray-500 dark:text-gray-400">
                    {{ $user['pn'] ?? 'N/A' }} • {{ ucfirst($user['role'] ?? 'user') }}
                </div>
            </div>
            
            <!-- Dropdown Arrow -->
            <svg 
                :class="{ 'rotate-180': open }" 
                class="w-4 h-4 text-gray-500 transition-transform" 
                fill="none" 
                stroke="currentColor" 
                viewBox="0 0 24 24"
            >
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"></path>
            </svg>
        </button>

        <!-- Dropdown Menu -->
        <div 
            x-show="open" 
            x-transition:enter="transition ease-out duration-100"
            x-transition:enter-start="transform opacity-0 scale-95"
            x-transition:enter-end="transform opacity-100 scale-100"
            x-transition:leave="transition ease-in duration-75"
            x-transition:leave-start="transform opacity-100 scale-100"
            x-transition:leave-end="transform opacity-0 scale-95"
            class="absolute right-0 mt-2 w-56 bg-white dark:bg-gray-800 rounded-lg shadow-lg ring-1 ring-black ring-opacity-5 z-50"
            style="display: none;"
        >
            <div class="p-4 border-b border-gray-200 dark:border-gray-700">
                <div class="flex items-center space-x-3">
                    <div class="w-10 h-10 bg-blue-600 rounded-full flex items-center justify-center text-white font-semibold">
                        {{ $initials }}
                    </div>
                    <div>
                        <div class="font-medium text-gray-900 dark:text-white">
                            {{ $user['name'] ?? 'Unknown User' }}
                        </div>
                        <div class="text-sm text-gray-500 dark:text-gray-400">
                            PN: {{ $user['pn'] ?? 'N/A' }}
                        </div>
                        <div class="text-xs text-blue-600 dark:text-blue-400">
                            {{ ucfirst($user['role'] ?? 'user') }} • {{ $user['department_id'] ?? 'N/A' }}
                        </div>
                    </div>
                </div>
            </div>
            
            <div class="py-2">
                <!-- Profile/Settings (placeholder for future) -->
                <a 
                    href="#" 
                    class="flex items-center px-4 py-2 text-sm text-gray-700 dark:text-gray-300 hover:bg-gray-100 dark:hover:bg-gray-700"
                >
                    <svg class="w-4 h-4 mr-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"></path>
                    </svg>
                    Profile Settings
                </a>
                
                <!-- Divider -->
                <div class="border-t border-gray-200 dark:border-gray-700 my-1"></div>
                
                <!-- Logout -->
                <a 
                    href="/logout" 
                    class="flex items-center px-4 py-2 text-sm text-red-600 hover:bg-red-50 dark:hover:bg-red-900/20"
                    onclick="event.preventDefault(); document.getElementById('logout-form').submit();"
                >
                    <svg class="w-4 h-4 mr-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1"></path>
                    </svg>
                    Sign Out
                </a>
            </div>
        </div>
    </div>
</div>

<!-- Hidden logout form -->
<form id="logout-form" action="/logout" method="POST" style="display: none;">
    @csrf
</form>
