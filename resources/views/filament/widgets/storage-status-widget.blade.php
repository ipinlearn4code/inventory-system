<x-filament-widgets::widget>
    <x-filament::section>
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
                    <button 
                        wire:click="refreshStatus"
                        class="inline-flex items-center px-3 py-1.5 sm:border border-gray-300 dark:border-gray-600 rounded-md text-xs text-gray-700 dark:text-gray-300 bg-white dark:bg-gray-800 hover:bg-gray-50 dark:hover:bg-gray-700 focus:outline-none focus:ring-2 focus:ring-primary-500 transition-colors"
                        title="Refresh Status"
                    >
                        <x-filament::icon icon="heroicon-o-arrow-path" class="w-4 h-4 sm:w-3 sm:h-3 sm:mr-1" />
                        <span class="hidden sm:inline">Refresh</span>
                    </button>
                </div>
            </div>
        </x-slot>

        
        <div class="grid grid-cols-1 md:grid-cols-2 gap-3 sm:gap-4 p-3 sm:p-4 bg-white dark:bg-gray-800 rounded-lg">
            <!-- MinIO Status -->
            <div class="flex items-center justify-between p-2 sm:p-3 bg-gray-50 dark:bg-gray-700 rounded-lg border border-gray-200 dark:border-gray-600">
                <div class="flex items-center space-x-2 sm:space-x-3 min-w-0 flex-1">
                    @php
                        $minioColor = \App\Services\StorageHealthService::getStatusColor($details['minio']['status']);
                        $minioIcon = \App\Services\StorageHealthService::getStatusIcon($details['minio']['status']);
                    @endphp
                    <div class="w-6 h-6 sm:w-8 sm:h-8 rounded-lg flex items-center justify-center flex-shrink-0 {{ $minioColor === 'success' ? 'bg-green-100 dark:bg-green-900' : ($minioColor === 'warning' ? 'bg-yellow-100 dark:bg-yellow-900' : 'bg-red-100 dark:bg-red-900') }}">
                        <x-filament::icon 
                            :icon="$minioIcon" 
                            class="w-3 h-3 sm:w-4 sm:h-4 {{ $minioColor === 'success' ? 'text-green-600 dark:text-green-400' : ($minioColor === 'warning' ? 'text-yellow-600 dark:text-yellow-400' : 'text-red-600 dark:text-red-400') }}"
                        />
                    </div>
                    <div class="min-w-0 flex-1">
                        <span class="text-xs sm:text-sm font-medium text-gray-900 dark:text-white block truncate">MinIO Storage</span>
                        <div class="text-xs text-gray-600 dark:text-gray-400 truncate">
                            {{ $details['minio']['message'] }}
                        </div>
                        @if(isset($details['minio']['details']['response_time_ms']))
                            <div class="text-xs text-gray-500 dark:text-gray-500 hidden sm:block">
                                Response: {{ $details['minio']['details']['response_time_ms'] }}ms
                            </div>
                        @endif
                    </div>
                </div>
                
                <!-- Mobile: Status icon only -->
                <div class="flex-shrink-0">
                    @if($minioColor === 'success')
                        <x-filament::icon icon="heroicon-s-check-circle" class="w-5 h-5 text-green-600 sm:hidden" />
                    @elseif($minioColor === 'warning')
                        <x-filament::icon icon="heroicon-s-exclamation-triangle" class="w-5 h-5 text-yellow-600 sm:hidden" />
                    @else
                        <x-filament::icon icon="heroicon-s-x-circle" class="w-5 h-5 text-red-600 sm:hidden" />
                    @endif
                    
                    <!-- Desktop: Status badge -->
                    <span class="hidden sm:inline-flex items-center px-2 py-1 rounded-full text-xs font-medium {{ $minioColor === 'success' ? 'bg-green-100 text-green-700 dark:bg-green-900 dark:text-green-200' : ($minioColor === 'warning' ? 'bg-yellow-100 text-yellow-700 dark:bg-yellow-900 dark:text-yellow-200' : 'bg-red-100 text-red-700 dark:bg-red-900 dark:text-red-200') }}">
                        {{ ucfirst($details['minio']['status']) }}
                    </span>
                </div>
            </div>

            <!-- Public Storage Status -->
            <div class="flex items-center justify-between p-2 sm:p-3 bg-gray-50 dark:bg-gray-700 rounded-lg border border-gray-200 dark:border-gray-600">
                <div class="flex items-center space-x-2 sm:space-x-3 min-w-0 flex-1">
                    @php
                        $publicColor = \App\Services\StorageHealthService::getStatusColor($details['public']['status']);
                        $publicIcon = \App\Services\StorageHealthService::getStatusIcon($details['public']['status']);
                    @endphp
                    <div class="w-6 h-6 sm:w-8 sm:h-8 rounded-lg flex items-center justify-center flex-shrink-0 {{ $publicColor === 'success' ? 'bg-green-100 dark:bg-green-900' : ($publicColor === 'warning' ? 'bg-yellow-100 dark:bg-yellow-900' : ($publicColor === 'danger' ? 'bg-red-100 dark:bg-red-900' : 'bg-gray-100 dark:bg-gray-800')) }}">
                        <x-filament::icon 
                            :icon="$publicIcon" 
                            class="w-3 h-3 sm:w-4 sm:h-4 {{ $publicColor === 'success' ? 'text-green-600 dark:text-green-400' : ($publicColor === 'warning' ? 'text-yellow-600 dark:text-yellow-400' : ($publicColor === 'danger' ? 'text-red-600 dark:text-red-400' : 'text-gray-400')) }}"
                        />
                    </div>
                    <div class="min-w-0 flex-1">
                        <span class="text-xs sm:text-sm font-medium text-gray-900 dark:text-white block truncate">Public Storage</span>
                        <div class="text-xs text-gray-600 dark:text-gray-400 truncate">
                            {{ $details['public']['status'] === 'not_configured' ? 'Not configured' : $details['public']['message'] }}
                        </div>
                        @if($details['public']['status'] === 'not_configured')
                            <div class="text-xs text-gray-500 dark:text-gray-500 hidden sm:block">
                                MinIO is primary storage
                            </div>
                        @endif
                    </div>
                </div>
                
                <!-- Mobile: Status icon only -->
                <div class="flex-shrink-0">
                    @if($details['public']['status'] === 'not_configured')
                        <x-filament::icon icon="heroicon-s-minus-circle" class="w-5 h-5 text-gray-400 sm:hidden" />
                    @elseif($publicColor === 'success')
                        <x-filament::icon icon="heroicon-s-check-circle" class="w-5 h-5 text-green-600 sm:hidden" />
                    @elseif($publicColor === 'warning')
                        <x-filament::icon icon="heroicon-s-exclamation-triangle" class="w-5 h-5 text-yellow-600 sm:hidden" />
                    @else
                        <x-filament::icon icon="heroicon-s-x-circle" class="w-5 h-5 text-red-600 sm:hidden" />
                    @endif
                    
                    <!-- Desktop: Status badge -->
                    <span class="hidden sm:inline-flex items-center px-2 py-1 rounded-full text-xs font-medium {{ $publicColor === 'success' ? 'bg-green-100 text-green-700 dark:bg-green-900 dark:text-green-200' : ($publicColor === 'warning' ? 'bg-yellow-100 text-yellow-700 dark:bg-yellow-900 dark:text-yellow-200' : ($publicColor === 'danger' ? 'bg-red-100 text-red-700 dark:bg-red-900 dark:text-red-200' : 'bg-gray-100 text-gray-700 dark:bg-gray-800 dark:text-gray-300')) }}">
                        {{ $details['public']['status'] === 'not_configured' ? 'Not Used' : ucfirst($details['public']['status']) }}
                    </span>
                </div>
            </div>
        </div>

        <!-- Last Checked -->
        <div class="mt-3 sm:mt-4 pt-2 sm:pt-3 border-t border-gray-200 dark:border-gray-700 text-center">
            <p class="text-xs text-gray-500 dark:text-gray-400">
                <x-filament::icon icon="heroicon-m-clock" class="w-3 h-3 inline mr-1" />
                <span class="hidden sm:inline">Last checked: </span>{{ $storageStatus['last_checked']->format('H:i:s') }}<span class="hidden sm:inline"> on {{ $storageStatus['last_checked']->format('M d, Y') }}</span>
            </p>
        </div>
    </x-filament::section>
</x-filament-widgets::widget>
