<x-filament-widgets::widget>
    <x-filament::section 
        class="storage-widget-clickable storage-status-compact cursor-pointer transition-all duration-200"
        wire:click="openStorageModal"
        title="Click for detailed storage information"
    >
        <x-slot name="heading">
            <div class="flex items-center space-x-3">
                @php
                    $statusColor = \App\Services\StorageHealthService::getStatusColor($storageStatus['status']);
                    $statusIcon = \App\Services\StorageHealthService::getStatusIcon($storageStatus['status']);
                @endphp
                
                <div class="w-10 h-10 bg-primary-100 rounded-lg flex items-center justify-center shadow-sm">
                    <x-filament::icon 
                        :icon="$statusIcon" 
                        class="w-5 h-5 {{ $statusColor === 'success' ? 'text-green-600' : ($statusColor === 'warning' ? 'text-yellow-600' : 'text-red-600') }}"
                    />
                </div>
                <div class="flex-1">
                    <span class="text-lg font-bold text-primary-800 dark:text-primary-200">Storage Status</span>
                    <div class="flex items-center space-x-2 mt-1">
                        <span class="text-sm text-gray-600 dark:text-gray-400 hidden sm:block">{{ $storageStatus['message'] }}</span>
                        <!-- Mobile: Show status icon only -->
                        <div class="flex items-center space-x-1 sm:hidden">
                            @if($statusColor === 'success')
                                <x-filament::icon icon="heroicon-m-check-circle" class="w-4 h-4 text-green-600" />
                                <span class="text-xs text-green-600 font-medium">OK</span>
                            @elseif($statusColor === 'warning')
                                <x-filament::icon icon="heroicon-m-exclamation-triangle" class="w-4 h-4 text-yellow-600" />
                                <span class="text-xs text-yellow-600 font-medium">Warning</span>
                            @else
                                <x-filament::icon icon="heroicon-m-x-circle" class="w-4 h-4 text-red-600" />
                                <span class="text-xs text-red-600 font-medium">Error</span>
                            @endif
                        </div>
                        <!-- Desktop: Show status badge -->
                        <span class="hidden sm:inline-flex items-center px-2 py-0.5 rounded-full text-xs font-medium {{ $statusColor === 'success' ? 'bg-green-100 text-green-800 dark:bg-green-900 dark:text-green-200' : ($statusColor === 'warning' ? 'bg-yellow-100 text-yellow-800 dark:bg-yellow-900 dark:text-yellow-200' : 'bg-red-100 text-red-800 dark:bg-red-900 dark:text-red-200') }}">
                            {{ ucfirst($storageStatus['status']) }}
                        </span>
                    </div>
                </div>
                <div class="flex-shrink-0">
                    <!--  -->
                    <button 
                        wire:click="refreshStatus"
                        class="inline-flex items-center px-3 py-1.5 sm:border border-gray-300 dark:border-gray-600 rounded-md text-xs text-gray-700 dark:text-gray-300 bg-white dark:bg-gray-800 hover:bg-gray-50 dark:hover:bg-gray-700 focus:outline-none focus:ring-2 focus:ring-primary-500 transition-colors"
                        title="Refresh Status"
                    >
                        <x-filament::icon icon="heroicon-o-arrow-path" class="w-4 h-4 sm:w-3 sm:h-3 sm:mr-1" />
                        <span class="hidden sm:inline ml-2">Refresh</span>
                    </button>
                </div>
            </div>
        </x-slot>

        <!-- Simplified Content -->
        <div>
            <!-- Main Status Display -->
            <div class="flex items-center justify-between">
                <div class="flex items-center space-x-3">
                    @php
                        $minioColor = \App\Services\StorageHealthService::getStatusColor($details['minio']['status']);
                        $minioIcon = \App\Services\StorageHealthService::getStatusIcon($details['minio']['status']);
                    @endphp
                    <!-- MinIO Status Icon -->
                    <div class="w-8 h-8 rounded-lg flex items-center justify-center {{ $minioColor === 'success' ? 'bg-green-100 dark:bg-green-900' : ($minioColor === 'warning' ? 'bg-yellow-100 dark:bg-yellow-900' : 'bg-red-100 dark:bg-red-900') }}">
                        <x-filament::icon 
                            :icon="$minioIcon" 
                            class="w-4 h-4 {{ $minioColor === 'success' ? 'text-green-600 dark:text-green-400' : ($minioColor === 'warning' ? 'text-yellow-600 dark:text-yellow-400' : 'text-red-600 dark:text-red-400') }}"
                        />
                    </div>
                    
                    <!-- Storage Info -->
                    <div class="flex-1">
                        <div class="flex items-center space-x-2">
                            <span class="text-sm font-medium text-gray-900 dark:text-white">
                                MinIO Storage
                            </span>
                            @if($details['public']['status'] !== 'not_configured')
                                <span class="text-xs text-gray-500 dark:text-gray-400">
                                    (+1 more)
                                </span>
                            @endif
                        </div>
                        <div class="text-xs text-gray-600 dark:text-gray-400">
                            {{ $details['minio']['status'] === 'healthy' ? 'Running normally' : $details['minio']['message'] }}
                            @if(isset($details['minio']['details']['response_time_ms']))
                                <span class="hidden sm:inline ml-1">• {{ $details['minio']['details']['response_time_ms'] }}ms</span>
                            @endif
                        </div>
                    </div>
                </div>
                
                <!-- Status Summary & Click Indicator -->
                <div class="flex items-center space-x-2">
                    <span class="inline-flex items-center px-2 py-1 rounded-full text-xs font-medium {{ $statusColor === 'success' ? 'bg-green-100 text-green-700 dark:bg-green-900 dark:text-green-200' : ($statusColor === 'warning' ? 'bg-yellow-100 text-yellow-700 dark:bg-yellow-900 dark:text-yellow-200' : 'bg-red-100 text-red-700 dark:bg-red-900 dark:text-red-200') }}">
                        {{ ucfirst($storageStatus['status']) }}
                    </span>
                    <x-filament::icon 
                        icon="heroicon-m-chevron-right" 
                        class="w-4 h-4 text-gray-400 dark:text-gray-500 storage-chevron"
                    />
                </div>
            </div>
        </div>

        <!-- Last Checked -->
        <div class="mt-3 pt-2 border-t border-gray-200 dark:border-gray-700 text-center">
            <p class="text-xs text-gray-500 dark:text-gray-400">
                <x-filament::icon icon="heroicon-m-clock" class="w-3 h-3 inline mr-1" />
                <span class="hidden sm:inline">Last checked: </span>{{ $storageStatus['last_checked']->format('H:i:s') }}<span class="hidden sm:inline"> on {{ $storageStatus['last_checked']->format('M d, Y') }}</span>
            </p>
        </div>
    </x-filament::section>

    <!-- Storage Info Modal -->
    <x-filament::modal id="storage-info-modal" width="4xl">
        <x-slot name="heading">
            <div class="flex items-center space-x-3">
                <div class="w-10 h-10 bg-primary-100 rounded-lg flex items-center justify-center">
                    <x-filament::icon 
                        icon="heroicon-o-server-stack" 
                        class="w-5 h-5 text-primary-600"
                    />
                </div>
                <div>
                    <h2 class="text-xl font-semibold text-gray-900 dark:text-white">Storage System Details</h2>
                    <p class="text-sm text-gray-600 dark:text-gray-400">Complete storage health overview</p>
                </div>
            </div>
        </x-slot>

        @include('filament.modals.storage-info-modal', [
            'storageStatus' => [
                'overall' => [
                    'status' => $storageStatus['status'],
                    'message' => $storageStatus['message'],
                    'checked_at' => $storageStatus['last_checked']
                ],
                'minio' => $details['minio'],
                'public' => $details['public']
            ]
        ])
    </x-filament::modal>
</x-filament-widgets::widget>
