<div class="space-y-6">
    <!-- Overall Status -->
    <div class="bg-gray-50 dark:bg-gray-800 rounded-lg p-4">
        <div class="flex items-center justify-between mb-3">
            <h3 class="text-lg font-semibold text-gray-900 dark:text-white">Overall Storage Status</h3>
            @php
                $overallColor = \App\Services\StorageHealthService::getStatusColor($storageStatus['overall']['status']);
                $overallIcon = \App\Services\StorageHealthService::getStatusIcon($storageStatus['overall']['status']);
            @endphp
            <div class="flex items-center space-x-2">
                <x-filament::icon 
                    :icon="$overallIcon" 
                    class="w-6 h-6 {{ $overallColor === 'success' ? 'text-green-500' : ($overallColor === 'warning' ? 'text-yellow-500' : 'text-red-500') }}"
                />
                <span class="inline-flex items-center px-3 py-1 rounded-full text-sm font-medium {{ $overallColor === 'success' ? 'bg-green-100 text-green-800 dark:bg-green-900 dark:text-green-200' : ($overallColor === 'warning' ? 'bg-yellow-100 text-yellow-800 dark:bg-yellow-900 dark:text-yellow-200' : 'bg-red-100 text-red-800 dark:bg-red-900 dark:text-red-200') }}">
                    {{ ucfirst($storageStatus['overall']['status']) }}
                </span>
            </div>
        </div>
        <p class="text-sm text-gray-600 dark:text-gray-400">
            {{ $storageStatus['overall']['message'] }}
        </p>
    </div>

    <!-- Storage Systems Grid -->
    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
        <!-- MinIO Storage -->
        <div class="border border-gray-200 dark:border-gray-700 rounded-lg p-4">
            <div class="flex items-center justify-between mb-3">
                <h4 class="text-base font-medium text-gray-900 dark:text-white">MinIO Storage</h4>
                @php
                    $minioColor = \App\Services\StorageHealthService::getStatusColor($storageStatus['minio']['status']);
                    $minioIcon = \App\Services\StorageHealthService::getStatusIcon($storageStatus['minio']['status']);
                @endphp
                <x-filament::icon 
                    :icon="$minioIcon" 
                    class="w-5 h-5 {{ $minioColor === 'success' ? 'text-green-500' : ($minioColor === 'warning' ? 'text-yellow-500' : 'text-red-500') }}"
                />
            </div>
            
            <div class="space-y-2 text-sm">
                <div class="flex justify-between">
                    <span class="text-gray-600 dark:text-gray-400">Status:</span>
                    <span class="font-medium {{ $minioColor === 'success' ? 'text-green-600 dark:text-green-400' : ($minioColor === 'warning' ? 'text-yellow-600 dark:text-yellow-400' : 'text-red-600 dark:text-red-400') }}">
                        {{ ucfirst($storageStatus['minio']['status']) }}
                    </span>
                </div>
                
                @if(isset($storageStatus['minio']['details']) && is_array($storageStatus['minio']['details']))
                    @if(isset($storageStatus['minio']['details']['endpoint']))
                        <div class="flex justify-between">
                            <span class="text-gray-600 dark:text-gray-400">Endpoint:</span>
                            <span class="font-mono text-xs">{{ $storageStatus['minio']['details']['endpoint'] }}</span>
                        </div>
                    @endif
                    
                    @if(isset($storageStatus['minio']['details']['bucket']))
                        <div class="flex justify-between">
                            <span class="text-gray-600 dark:text-gray-400">Bucket:</span>
                            <span class="font-mono text-xs">{{ $storageStatus['minio']['details']['bucket'] }}</span>
                        </div>
                    @endif
                    
                    @if(isset($storageStatus['minio']['details']['response_time_ms']))
                        <div class="flex justify-between">
                            <span class="text-gray-600 dark:text-gray-400">Response Time:</span>
                            <span class="font-medium">{{ $storageStatus['minio']['details']['response_time_ms'] }}ms</span>
                        </div>
                    @endif
                    
                    @if(isset($storageStatus['minio']['details']['file_count']))
                        <div class="flex justify-between">
                            <span class="text-gray-600 dark:text-gray-400">Files in Bucket:</span>
                            <span class="font-medium">{{ $storageStatus['minio']['details']['file_count'] }}</span>
                        </div>
                    @endif
                @endif
            </div>
            
            <div class="mt-3 pt-3 border-t border-gray-200 dark:border-gray-700">
                <p class="text-xs text-gray-600 dark:text-gray-400">
                    {{ $storageStatus['minio']['message'] }}
                </p>
            </div>
        </div>

        <!-- Public Storage -->
        <div class="border border-gray-200 dark:border-gray-700 rounded-lg p-4">
            <div class="flex items-center justify-between mb-3">
                <h4 class="text-base font-medium text-gray-900 dark:text-white">Public Storage</h4>
                @php
                    $publicColor = \App\Services\StorageHealthService::getStatusColor($storageStatus['public']['status']);
                    $publicIcon = \App\Services\StorageHealthService::getStatusIcon($storageStatus['public']['status']);
                @endphp
                <x-filament::icon 
                    :icon="$publicIcon" 
                    class="w-5 h-5 {{ $publicColor === 'success' ? 'text-green-500' : ($publicColor === 'warning' ? 'text-yellow-500' : ($publicColor === 'danger' ? 'text-red-500' : 'text-gray-400')) }}"
                />
            </div>
            
            <div class="space-y-2 text-sm">
                <div class="flex justify-between">
                    <span class="text-gray-600 dark:text-gray-400">Status:</span>
                    <span class="font-medium {{ $publicColor === 'success' ? 'text-green-600 dark:text-green-400' : ($publicColor === 'warning' ? 'text-yellow-600 dark:text-yellow-400' : ($publicColor === 'danger' ? 'text-red-600 dark:text-red-400' : 'text-gray-600 dark:text-gray-400')) }}">
                        {{ ucfirst(str_replace('_', ' ', $storageStatus['public']['status'])) }}
                    </span>
                </div>
                
                <div class="flex justify-between">
                    <span class="text-gray-600 dark:text-gray-400">Type:</span>
                    <span class="font-medium">{{ $storageStatus['public']['status'] === 'not_configured' ? 'Not Used' : 'Local File System' }}</span>
                </div>
                
                <div class="flex justify-between">
                    <span class="text-gray-600 dark:text-gray-400">Purpose:</span>
                    <span class="text-xs">{{ $storageStatus['public']['status'] === 'not_configured' ? 'MinIO is Primary' : 'Backup & Local Storage' }}</span>
                </div>
            </div>
            
            <div class="mt-3 pt-3 border-t border-gray-200 dark:border-gray-700">
                <p class="text-xs text-gray-600 dark:text-gray-400">
                    {{ $storageStatus['public']['message'] }}
                </p>
            </div>
        </div>
    </div>

    <!-- Last Updated -->
    <div class="text-center">
        <p class="text-xs text-gray-500 dark:text-gray-400">
            Last checked: {{ $storageStatus['overall']['checked_at']->format('M d, Y H:i:s') }}
        </p>
    </div>

    <!-- Health Check Actions -->
    <div class="bg-blue-50 dark:bg-blue-900/20 rounded-lg p-4">
        <h5 class="text-sm font-medium text-blue-900 dark:text-blue-100 mb-2">Storage Health Actions</h5>
        <div class="text-xs text-blue-800 dark:text-blue-200 space-y-1">
            <p>• Use the "Storage Status" button to refresh health checks</p>
            <p>• Run <code class="bg-blue-100 dark:bg-blue-800 px-1 rounded">php artisan storage:health-check</code> for detailed diagnostics</p>
            <p>• Check the dashboard widget for real-time monitoring</p>
        </div>
    </div>
</div>
