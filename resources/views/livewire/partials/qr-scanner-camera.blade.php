<div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
    {{-- Camera Scanner --}}
    <div class="flex flex-col items-center space-y-4">
        <div id="qr-reader" 
             class="w-full max-w-md h-64 sm:h-80 border-2 border-dashed border-gray-300 dark:border-gray-600 rounded-lg overflow-hidden">
        </div>
        
        <div class="flex flex-col sm:flex-row gap-2 w-full max-w-md">
            <x-filament::button
                x-show="!isScanning"
                @click="startScanner()"
                size="sm"
                color="primary"
                icon="heroicon-o-play"
                class="flex-1"
            >
                Start Camera
            </x-filament::button>
            
            <x-filament::button
                x-show="isScanning"
                @click="stopScanner()"
                size="sm"
                color="gray"
                icon="heroicon-o-stop"
                class="flex-1"
            >
                Stop Camera
            </x-filament::button>
        </div>

        <div id="scanner-status" class="text-sm text-gray-600 dark:text-gray-400 text-center">
            Click "Start Camera" to begin scanning
        </div>
    </div>

    {{-- Instructions --}}
    <div class="space-y-4">
        <h3 class="text-lg font-semibold text-gray-900 dark:text-white">Instructions</h3>
        
        <div class="prose dark:prose-invert max-w-none">
            <ol class="text-sm text-gray-600 dark:text-gray-300 space-y-2 list-decimal list-inside">
                <li>Click "Start Camera" to activate the scanner</li>
                <li>Allow camera access when prompted by your browser</li>
                <li>Position the QR code within the camera view</li>
                <li>The device information will appear automatically when scanned</li>
            </ol>
        </div>

        <div class="mt-4 p-4 bg-blue-50 dark:bg-blue-900/20 rounded-lg">
            <h4 class="text-sm font-semibold text-blue-900 dark:text-blue-100 mb-2">
                <x-heroicon-o-information-circle class="h-4 w-4 inline mr-1" />
                QR Code Format
            </h4>
            <p class="text-sm text-blue-800 dark:text-blue-200">
                This scanner reads QR codes in the format: <code class="px-1 py-0.5 bg-blue-100 dark:bg-blue-800 rounded text-xs">briven-{asset_code}</code>
            </p>
        </div>

        @if ($errorMessage)
            <div class="mt-4 p-4 bg-red-50 dark:bg-red-900/20 rounded-lg">
                <h4 class="text-sm font-semibold text-red-900 dark:text-red-100 mb-1">
                    <x-heroicon-o-exclamation-triangle class="h-4 w-4 inline mr-1" />
                    Scanner Error
                </h4>
                <p class="text-sm text-red-800 dark:text-red-200">{{ $errorMessage }}</p>
            </div>
        @endif
    </div>
</div>
