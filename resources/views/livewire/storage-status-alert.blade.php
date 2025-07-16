<div>
    @if($showAlert)
        @php
            $statusColor = \App\Services\StorageHealthService::getStatusColor($storageStatus['status']);
            $statusIcon = \App\Services\StorageHealthService::getStatusIcon($storageStatus['status']);
        @endphp
        
        <div class="mb-6 bg-{{ $statusColor === 'warning' ? 'yellow' : 'red' }}-50 border border-{{ $statusColor === 'warning' ? 'yellow' : 'red' }}-200 rounded-lg p-4 dark:bg-{{ $statusColor === 'warning' ? 'yellow' : 'red' }}-900/10 dark:border-{{ $statusColor === 'warning' ? 'yellow' : 'red' }}-800/20">
            <div class="flex items-start">
                <div class="flex-shrink-0">
                    <x-filament::icon 
                        :icon="$statusIcon" 
                        class="w-5 h-5 text-{{ $statusColor === 'warning' ? 'yellow' : 'red' }}-400 dark:text-{{ $statusColor === 'warning' ? 'yellow' : 'red' }}-300"
                    />
                </div>
                
                <div class="ml-3 flex-1">
                    <h3 class="text-sm font-medium text-{{ $statusColor === 'warning' ? 'yellow' : 'red' }}-800 dark:text-{{ $statusColor === 'warning' ? 'yellow' : 'red' }}-200">
                        Storage Connection {{ $storageStatus['status'] === 'warning' ? 'Warning' : 'Error' }}
                    </h3>
                    
                    <div class="mt-2 text-sm text-{{ $statusColor === 'warning' ? 'yellow' : 'red' }}-700 dark:text-{{ $statusColor === 'warning' ? 'yellow' : 'red' }}-300">
                        <p>{{ $storageStatus['message'] }}</p>
                        
                        @if(isset($storageStatus['details']) && is_string($storageStatus['details']))
                            <p class="mt-1 text-xs">{{ $storageStatus['details'] }}</p>
                        @endif
                        
                        @if($storageStatus['status'] === 'error')
                            <p class="mt-2 text-sm">
                                <strong>Impact:</strong> File uploads and downloads may not work properly. Please contact your system administrator.
                            </p>
                        @endif
                    </div>
                    
                    <div class="mt-4 flex space-x-3">
                        <button 
                            wire:click="refreshStatus"
                            class="inline-flex items-center px-3 py-1.5 border border-transparent text-xs font-medium rounded-md text-{{ $statusColor === 'warning' ? 'yellow' : 'red' }}-800 bg-{{ $statusColor === 'warning' ? 'yellow' : 'red' }}-200 hover:bg-{{ $statusColor === 'warning' ? 'yellow' : 'red' }}-300 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-{{ $statusColor === 'warning' ? 'yellow' : 'red' }}-500 dark:bg-{{ $statusColor === 'warning' ? 'yellow' : 'red' }}-800 dark:text-{{ $statusColor === 'warning' ? 'yellow' : 'red' }}-200 dark:hover:bg-{{ $statusColor === 'warning' ? 'yellow' : 'red' }}-700"
                        >
                            <x-filament::icon icon="heroicon-o-arrow-path" class="w-3 h-3 mr-1" />
                            Refresh Status
                        </button>
                        
                        <button 
                            wire:click="dismissAlert"
                            class="inline-flex items-center px-3 py-1.5 border border-{{ $statusColor === 'warning' ? 'yellow' : 'red' }}-300 text-xs font-medium rounded-md text-{{ $statusColor === 'warning' ? 'yellow' : 'red' }}-700 bg-white hover:bg-{{ $statusColor === 'warning' ? 'yellow' : 'red' }}-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-{{ $statusColor === 'warning' ? 'yellow' : 'red' }}-500 dark:bg-gray-800 dark:text-{{ $statusColor === 'warning' ? 'yellow' : 'red' }}-300 dark:border-{{ $statusColor === 'warning' ? 'yellow' : 'red' }}-600 dark:hover:bg-gray-700"
                        >
                            Dismiss
                        </button>
                    </div>
                </div>
            </div>
        </div>
    @endif
</div>
