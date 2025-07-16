<div class="filament-widget bg-white dark:bg-gray-900 border border-gray-200 dark:border-gray-700 rounded-lg shadow-sm">
    <div class="p-3">
        <!-- Header -->
        <div class="flex items-center justify-between mb-2">
            <div class="flex items-center space-x-2">
                @php
                    $statusColor = \App\Services\StorageHealthService::getStatusColor($storageStatus['status']);
                    $statusIcon = \App\Services\StorageHealthService::getStatusIcon($storageStatus['status']);
                @endphp
                
                <div class="flex-shrink-0">
                    <x-filament::icon 
                        :icon="$statusIcon" 
                        class="w-5 h-5 {{ $statusColor === 'success' ? 'text-green-500' : ($statusColor === 'warning' ? 'text-yellow-500' : 'text-red-500') }}"
                    />
                </div>
                
                <div>
                    <h3 class="text-sm font-semibold text-gray-900 dark:text-white">
                        Storage Status
                    </h3>
                    <p class="text-xs text-gray-600 dark:text-gray-400">
                        {{ $storageStatus['message'] }}
                    </p>
                </div>
            </div>
            
            <div class="flex items-center space-x-2">
                <!-- Refresh Button -->
                <button 
                    wire:click="refreshStatus"
                    class="inline-flex items-center px-2 py-1 border border-gray-300 dark:border-gray-600 rounded text-xs text-gray-700 dark:text-gray-300 bg-white dark:bg-gray-800 hover:bg-gray-50 dark:hover:bg-gray-700 focus:outline-none focus:ring-2 focus:ring-blue-500"
                    title="Refresh Status"
                >
                    <x-filament::icon icon="heroicon-o-arrow-path" class="w-3 h-3" />
                </button>
                
                <!-- Status Badge -->
                <span class="inline-flex items-center px-2 py-0.5 rounded text-xs font-medium {{ $statusColor === 'success' ? 'bg-green-100 text-green-800 dark:bg-green-900 dark:text-green-200' : ($statusColor === 'warning' ? 'bg-yellow-100 text-yellow-800 dark:bg-yellow-900 dark:text-yellow-200' : 'bg-red-100 text-red-800 dark:bg-red-900 dark:text-red-200') }}">
                    {{ ucfirst($storageStatus['status']) }}
                </span>
            </div>
        </div>

        <!-- Storage Details - Compact Layout -->
        <div class="space-y-1.5">
            <!-- MinIO Status -->
            <div class="flex items-center justify-between p-2 bg-gray-50 dark:bg-gray-800 rounded">
                <div class="flex items-center space-x-2">
                    @php
                        $minioColor = \App\Services\StorageHealthService::getStatusColor($details['minio']['status']);
                        $minioIcon = \App\Services\StorageHealthService::getStatusIcon($details['minio']['status']);
                    @endphp
                    <x-filament::icon 
                        :icon="$minioIcon" 
                        class="w-4 h-4 {{ $minioColor === 'success' ? 'text-green-500' : ($minioColor === 'warning' ? 'text-yellow-500' : 'text-red-500') }}"
                    />
                    <span class="text-sm font-medium text-gray-900 dark:text-white">MinIO</span>
                </div>
                
                <div class="text-right">
                    <div class="text-xs text-gray-600 dark:text-gray-400">
                        {{ $details['minio']['message'] }}
                    </div>
                    @if(isset($details['minio']['details']['response_time_ms']))
                        <div class="text-xs text-gray-500 dark:text-gray-500">
                            {{ $details['minio']['details']['response_time_ms'] }}ms
                        </div>
                    @endif
                </div>
            </div>

            <!-- Public Storage Status -->
            <div class="flex items-center justify-between p-2 bg-gray-50 dark:bg-gray-800 rounded">
                <div class="flex items-center space-x-2">
                    @php
                        $publicColor = \App\Services\StorageHealthService::getStatusColor($details['public']['status']);
                        $publicIcon = \App\Services\StorageHealthService::getStatusIcon($details['public']['status']);
                    @endphp
                    <x-filament::icon 
                        :icon="$publicIcon" 
                        class="w-4 h-4 {{ $publicColor === 'success' ? 'text-green-500' : ($publicColor === 'warning' ? 'text-yellow-500' : ($publicColor === 'danger' ? 'text-red-500' : 'text-gray-400')) }}"
                    />
                    <span class="text-sm font-medium text-gray-900 dark:text-white">Public</span>
                </div>
                
                <div class="text-right">
                    <div class="text-xs text-gray-600 dark:text-gray-400">
                        {{ $details['public']['status'] === 'not_configured' ? 'Not used' : $details['public']['message'] }}
                    </div>
                    @if($details['public']['status'] === 'not_configured')
                        <div class="text-xs text-gray-500 dark:text-gray-500">
                            <em>MinIO is primary</em>
                        </div>
                    @endif
                </div>
            </div>
        </div>

        <!-- Last Checked -->
        <div class="mt-2 pt-2 border-t border-gray-200 dark:border-gray-700">
            <p class="text-xs text-gray-500 dark:text-gray-400 text-center">
                Last checked: {{ $storageStatus['last_checked']->format('H:i:s') }}
            </p>
        </div>
    </div>
</div>
