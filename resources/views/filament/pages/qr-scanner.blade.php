<x-filament-panels::page>
    <div class="space-y-6">
        {{-- QR Scanner Form --}}
        <form wire:submit="save">
            {{ $this->form }}
        </form>

        {{-- Scanned Device Information --}}
        @if ($scannedDevice)
            <x-filament::section>
                <x-slot name="heading">
                    Device Information
                </x-slot>
                <x-slot name="description">
                    Scanned device: {{ $scannedDevice['asset_code'] }}
                </x-slot>

                <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
                    {{-- Device Details --}}
                    <div class="space-y-4">
                        <div class="bg-gray-50 dark:bg-gray-800 rounded-lg p-4 space-y-3">
                            <div class="grid grid-cols-1 sm:grid-cols-2 gap-3 text-sm">
                                <div>
                                    <span class="font-medium text-gray-700 dark:text-gray-300">Asset Code:</span>
                                    <span class="ml-2 text-gray-900 dark:text-white font-mono">{{ $scannedDevice['asset_code'] }}</span>
                                </div>
                                
                                <div>
                                    <span class="font-medium text-gray-700 dark:text-gray-300">Brand:</span>
                                    <span class="ml-2 text-gray-900 dark:text-white">{{ $scannedDevice['brand'] }}</span>
                                </div>
                                
                                <div>
                                    <span class="font-medium text-gray-700 dark:text-gray-300">Model/Series:</span>
                                    <span class="ml-2 text-gray-900 dark:text-white">{{ $scannedDevice['brand_name'] }}</span>
                                </div>
                                
                                <div>
                                    <span class="font-medium text-gray-700 dark:text-gray-300">Category:</span>
                                    <span class="ml-2 text-gray-900 dark:text-white">{{ $scannedDevice['bribox']['category']['category_name'] ?? 'N/A' }}</span>
                                </div>
                                
                                <div>
                                    <span class="font-medium text-gray-700 dark:text-gray-300">Serial Number:</span>
                                    <span class="ml-2 text-gray-900 dark:text-white">{{ $scannedDevice['serial_number'] ?? 'N/A' }}</span>
                                </div>
                                
                                <div>
                                    <span class="font-medium text-gray-700 dark:text-gray-300">Condition:</span>
                                    <span class="ml-2 text-gray-900 dark:text-white">{{ $scannedDevice['condition'] ?? 'N/A' }}</span>
                                </div>
                                
                                <div>
                                    <span class="font-medium text-gray-700 dark:text-gray-300">Status:</span>
                                    @if ($scannedDevice['currentAssignment'] ?? false)
                                        <span class="ml-2 text-green-600 dark:text-green-400 font-medium">Assigned</span>
                                    @else
                                        <span class="ml-2 text-yellow-600 dark:text-yellow-400 font-medium">Available</span>
                                    @endif
                                </div>
                            </div>
                        </div>

                        {{-- Assignment Information --}}
                        @if ($scannedDevice['currentAssignment'] ?? false)
                            <div class="bg-blue-50 dark:bg-blue-900/20 rounded-lg p-4">
                                <h4 class="font-medium text-blue-900 dark:text-blue-100 mb-3">Assignment Details</h4>
                                <div class="grid grid-cols-1 sm:grid-cols-2 gap-3 text-sm">
                                    <div>
                                        <span class="font-medium text-blue-700 dark:text-blue-300">Assigned to:</span>
                                        <span class="ml-2 text-blue-900 dark:text-blue-100">{{ $scannedDevice['currentAssignment']['user']['name'] ?? 'N/A' }}</span>
                                    </div>
                                    
                                    @if ($scannedDevice['currentAssignment']['branch'] ?? false)
                                        <div>
                                            <span class="font-medium text-blue-700 dark:text-blue-300">Branch:</span>
                                            <span class="ml-2 text-blue-900 dark:text-blue-100">{{ $scannedDevice['currentAssignment']['branch']['branch_name'] ?? 'N/A' }}</span>
                                        </div>
                                    @endif
                                    
                                    <div>
                                        <span class="font-medium text-blue-700 dark:text-blue-300">Assigned Date:</span>
                                        <span class="ml-2 text-blue-900 dark:text-blue-100">{{ $scannedDevice['currentAssignment']['assigned_date'] ?? 'N/A' }}</span>
                                    </div>
                                </div>
                            </div>
                        @endif
                    </div>

                    {{-- Instructions --}}
                    <div class="space-y-4">
                        <h3 class="text-lg font-semibold text-gray-900 dark:text-white">Instructions</h3>
                        
                        <div class="prose dark:prose-invert max-w-none">
                            <ol class="text-sm text-gray-600 dark:text-gray-300 space-y-2 list-decimal list-inside">
                                <li>Click the QR scanner field above to activate the camera</li>
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
                    </div>
                </div>
            </x-filament::section>
        @else
            {{-- Instructions when no device scanned --}}
            <x-filament::section>
                <x-slot name="heading">
                    How to Use QR Scanner
                </x-slot>
                
                <div class="space-y-4">
                    <div class="prose dark:prose-invert max-w-none">
                        <ol class="text-sm text-gray-600 dark:text-gray-300 space-y-2 list-decimal list-inside">
                            <li>Click the QR scanner field above to activate the camera</li>
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
                </div>
            </x-filament::section>
        @endif
    </div>
</x-filament-panels::page>
                        