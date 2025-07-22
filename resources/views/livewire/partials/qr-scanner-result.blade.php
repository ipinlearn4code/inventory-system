<x-filament::section>
    <x-slot name="heading">
        Device Information
    </x-slot>
    <x-slot name="description">
        Scanned at {{ $lastScanTime }} - {{ $scannedDevice['asset_code'] }}
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
                        <span class="ml-2 text-gray-900 dark:text-white">{{ $this->deviceCategory }}</span>
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
                        @if ($this->deviceStatus === 'assigned')
                            <span class="ml-2 text-green-600 dark:text-green-400 font-medium">Assigned</span>
                        @else
                            <span class="ml-2 text-yellow-600 dark:text-yellow-400 font-medium">Available</span>
                        @endif
                    </div>
                </div>
            </div>

            {{-- Assignment Information --}}
            @if ($this->assignedUser)
                <div class="bg-blue-50 dark:bg-blue-900/20 rounded-lg p-4">
                    <h4 class="font-medium text-blue-900 dark:text-blue-100 mb-3">Assignment Details</h4>
                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-3 text-sm">
                        <div>
                            <span class="font-medium text-blue-700 dark:text-blue-300">Assigned to:</span>
                            <span class="ml-2 text-blue-900 dark:text-blue-100">{{ $this->assignedUser->name }}</span>
                        </div>
                        
                        @if ($this->assignedBranch)
                            <div>
                                <span class="font-medium text-blue-700 dark:text-blue-300">Branch:</span>
                                <span class="ml-2 text-blue-900 dark:text-blue-100">{{ $this->assignedBranch->branch_name }}</span>
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

        {{-- Quick Actions --}}
        <div class="space-y-4">
            <h3 class="text-lg font-semibold text-gray-900 dark:text-white">Quick Actions</h3>
            
            <div class="space-y-3">
                <x-filament::button
                    size="sm"
                    color="success"
                    icon="heroicon-o-printer"
                    tag="a"
                    :href="route('qr-code.sticker', $scannedDevice['device_id'])"
                    target="_blank"
                    class="w-full"
                >
                    Print QR Sticker
                </x-filament::button>

                <x-filament::button
                    size="sm"
                    color="gray"
                    icon="heroicon-o-camera"
                    wire:click="resetScanner"
                    class="w-full"
                >
                    Scan Another QR Code
                </x-filament::button>
            </div>
        </div>
    </div>
</x-filament::section>
