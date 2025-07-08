@php
    $user = session('authenticated_user');
    // Enhanced initials logic to handle various name formats
    $initials = 'UN'; // Default
    if ($user && !empty($user['name'])) {
        $nameParts = explode(' ', trim($user['name']));
        if (count($nameParts) >= 2) {
            // First name + Last name
            $initials = strtoupper(substr($nameParts[0], 0, 1) . substr($nameParts[count($nameParts)-1], 0, 1));
        } else {
            // Single name - use first two characters
            $initials = strtoupper(substr($user['name'], 0, 2));
        }
    }
    
    // Dynamic color based on user role with better fallback
    $avatarColors = [
        'superadmin' => 'from-red-600 to-red-700 ring-red-100 dark:ring-red-900',
        'admin' => 'from-blue-600 to-blue-700 ring-blue-100 dark:ring-blue-900',
        'manager' => 'from-purple-600 to-purple-700 ring-purple-100 dark:ring-purple-900',
        'supervisor' => 'from-orange-600 to-orange-700 ring-orange-100 dark:ring-orange-900',
        'user' => 'from-green-600 to-green-700 ring-green-100 dark:ring-green-900',
        'guest' => 'from-gray-600 to-gray-700 ring-gray-100 dark:ring-gray-900',
    ];
    
    $userRole = strtolower($user['role'] ?? 'user');
    $colorClass = $avatarColors[$userRole] ?? $avatarColors['user'];
@endphp

<div class="flex items-center">
    <div x-data="{ open: false }" class="relative">
        <!-- Avatar Button -->
        <button 
            x-ref="button"
            @click="open = !open" 
            @click.away="open = false"
            class="flex items-center space-x-8 p-2 rounded-lg hover:bg-gray-100 dark:hover:bg-gray-700 transition-colors"
        >
            <!-- Avatar Circle with Enhanced Styling -->
            <div class="w-9 h-9 bg-gradient-to-br {{ $colorClass }} rounded-full flex items-center justify-center text-white text-sm font-bold shadow-lg ring-2 ring-white dark:ring-gray-700 hover:shadow-xl transition-all duration-200">
                {{ $initials }}
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
            class="absolute mt-2 w-56 bg-white dark:bg-gray-800 rounded-lg shadow-lg ring-1 ring-black ring-opacity-5 z-50"
            style="display: none;"
            x-init="
                $watch('open', value => {
                    if (value) {
                        $nextTick(() => {
                            const rect = $refs.button.getBoundingClientRect();
                            const dropdown = $el;
                            const viewportWidth = window.innerWidth;
                            const dropdownWidth = 224; // w-56 = 14rem = 224px
                            
                            // Check if dropdown would overflow on the right
                            if (rect.right + dropdownWidth > viewportWidth) {
                                // Position to the left of the button
                                dropdown.style.right = '0px';
                                dropdown.style.left = 'auto';
                            } else {
                                // Position to the right of the button
                                dropdown.style.left = '0px';
                                dropdown.style.right = 'auto';
                            }
                        });
                    }
                })
            "
        >
            <div class="p-4 border-b border-gray-200 dark:border-gray-700">
                <div class="flex items-center space-x-4">
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
