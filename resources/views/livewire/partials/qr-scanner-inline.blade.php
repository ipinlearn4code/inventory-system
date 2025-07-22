<div class="space-y-4">
    <div class="flex items-center gap-4">
        <div id="qr-reader" class="w-48 h-48 border border-gray-300 dark:border-gray-600 rounded-lg overflow-hidden flex-shrink-0">
        </div>
        
        <div class="flex-1 space-y-3">
            <div class="flex gap-2">
                <x-filament::button
                    x-show="!isScanning"
                    @click="startScanner()"
                    size="sm"
                    color="primary"
                    icon="heroicon-o-play"
                >
                    Start Camera
                </x-filament::button>
                
                <x-filament::button
                    x-show="isScanning"
                    @click="stopScanner()"
                    size="sm"
                    color="gray"
                    icon="heroicon-o-stop"
                >
                    Stop Camera
                </x-filament::button>
            </div>
            
            <div id="scanner-status" class="text-sm text-gray-600 dark:text-gray-400">
                Click "Start Camera" to begin scanning
            </div>

            @if ($scannedDevice)
                <div class="p-3 bg-green-50 dark:bg-green-900/20 rounded-lg">
                    <p class="text-sm font-medium text-green-900 dark:text-green-100">
                        Device Found: {{ $scannedDevice['asset_code'] }}
                    </p>
                    <p class="text-xs text-green-800 dark:text-green-200">
                        {{ $scannedDevice['brand'] }} {{ $scannedDevice['brand_name'] }}
                    </p>
                </div>
            @endif

            @if ($errorMessage)
                <div class="p-3 bg-red-50 dark:bg-red-900/20 rounded-lg">
                    <p class="text-sm font-medium text-red-900 dark:text-red-100">Error:</p>
                    <p class="text-xs text-red-800 dark:text-red-200">{{ $errorMessage }}</p>
                </div>
            @endif
        </div>
    </div>
</div>
