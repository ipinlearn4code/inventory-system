<div class="space-y-4">
    <div class="text-center">
        <div id="qr-reader" class="w-full max-w-md mx-auto h-64 border-2 border-dashed border-gray-300 dark:border-gray-600 rounded-lg overflow-hidden">
        </div>
        
        <div class="mt-4 space-y-2">
            <div class="flex justify-center gap-2">
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
        </div>
    </div>

    @if ($scannedDevice)
        <div class="mt-4 p-4 bg-green-50 dark:bg-green-900/20 rounded-lg">
            <h4 class="text-sm font-semibold text-green-900 dark:text-green-100 mb-2">
                QR Code Scanned Successfully!
            </h4>
            <div class="text-sm text-green-800 dark:text-green-200">
                <p><strong>Asset Code:</strong> {{ $scannedDevice['asset_code'] }}</p>
                <p><strong>Device:</strong> {{ $scannedDevice['brand'] }} {{ $scannedDevice['brand_name'] }}</p>
                @if ($this->deviceStatus === 'assigned')
                    <p><strong>Status:</strong> <span class="text-red-600">Assigned to {{ $this->assignedUser->name ?? 'Unknown' }}</span></p>
                @else
                    <p><strong>Status:</strong> <span class="text-green-600">Available</span></p>
                @endif
            </div>
            
            <div class="mt-3 flex gap-2">
                <x-filament::button
                    size="sm"
                    color="success"
                    @click="$dispatch('device-selected', { deviceId: {{ $scannedDevice['device_id'] }}, assetCode: '{{ $scannedDevice['asset_code'] }}', device: @js($scannedDevice) })"
                >
                    Select This Device
                </x-filament::button>
                
                <x-filament::button
                    size="sm"
                    color="gray"
                    wire:click="resetScanner"
                >
                    Scan Again
                </x-filament::button>
            </div>
        </div>
    @endif

    @if ($errorMessage)
        <div class="mt-4 p-4 bg-red-50 dark:bg-red-900/20 rounded-lg">
            <h4 class="text-sm font-semibold text-red-900 dark:text-red-100 mb-1">
                <x-heroicon-o-exclamation-triangle class="h-4 w-4 inline mr-1" />
                Scanner Error
            </h4>
            <p class="text-sm text-red-800 dark:text-red-200">{{ $errorMessage }}</p>
        </div>
    @endif

    <div class="mt-4 p-4 bg-blue-50 dark:bg-blue-900/20 rounded-lg">
        <h4 class="text-sm font-semibold text-blue-900 dark:text-blue-100 mb-2">
            Instructions
        </h4>
        <ul class="text-sm text-blue-800 dark:text-blue-200 space-y-1">
            <li>• Click "Start Camera" to activate the scanner</li>
            <li>• Allow camera access when prompted</li>
            <li>• Position the QR code within the camera view</li>
            <li>• The device will be automatically selected when scanned</li>
        </ul>
    </div>
</div>
