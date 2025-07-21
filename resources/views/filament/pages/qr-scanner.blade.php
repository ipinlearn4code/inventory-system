<x-filament-panels::page>
    <div class="space-y-6">
        {{-- QR Scanner Section --}}
        @if ($showScanner)
            <x-filament::section>
                <x-slot name="heading">
                    QR Code Scanner
                </x-slot>
                <x-slot name="description">
                    Point your camera at a QR code to scan device information
                </x-slot>

                <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
                    {{-- Camera Scanner --}}
                    <div class="flex flex-col items-center space-y-4">
                        <div id="qr-reader" 
                             class="w-full max-w-md h-64 sm:h-80 border-2 border-dashed border-gray-300 dark:border-gray-600 rounded-lg overflow-hidden">
                        </div>
                        
                        <div class="flex flex-col sm:flex-row gap-2 w-full max-w-md">
                            <x-filament::button
                                id="start-scanner"
                                size="sm"
                                color="primary"
                                icon="heroicon-o-play"
                                class="flex-1"
                            >
                                Start Camera
                            </x-filament::button>
                            
                            <x-filament::button
                                id="stop-scanner"
                                size="sm"
                                color="gray"
                                icon="heroicon-o-stop"
                                class="flex-1"
                                style="display: none;"
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
            </x-filament::section>
        @endif

        {{-- Scanned Device Information --}}
        @if ($scannedDevice)
            <x-filament::section>
                <x-slot name="heading">
                    Device Information
                </x-slot>
                <x-slot name="description">
                    Scanned at {{ $lastScanTime }} - {{ $scannedDevice->asset_code }}
                </x-slot>

                <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
                    {{-- Device Details --}}
                    <div class="space-y-4">
                        <div class="bg-gray-50 dark:bg-gray-800 rounded-lg p-4 space-y-3">
                            <div class="grid grid-cols-1 sm:grid-cols-2 gap-3 text-sm">
                                <div>
                                    <span class="font-medium text-gray-700 dark:text-gray-300">Asset Code:</span>
                                    <span class="ml-2 text-gray-900 dark:text-white font-mono">{{ $scannedDevice->asset_code }}</span>
                                </div>
                                
                                <div>
                                    <span class="font-medium text-gray-700 dark:text-gray-300">Brand:</span>
                                    <span class="ml-2 text-gray-900 dark:text-white">{{ $scannedDevice->brand_name }}</span>
                                </div>
                                
                                <div>
                                    <span class="font-medium text-gray-700 dark:text-gray-300">Category:</span>
                                    <span class="ml-2 text-gray-900 dark:text-white">{{ $scannedDevice->bribox->category->category_name ?? 'N/A' }}</span>
                                </div>
                                
                                <div>
                                    <span class="font-medium text-gray-700 dark:text-gray-300">Serial Number:</span>
                                    <span class="ml-2 text-gray-900 dark:text-white">{{ $scannedDevice->serial_number ?? 'N/A' }}</span>
                                </div>
                                
                                <div>
                                    <span class="font-medium text-gray-700 dark:text-gray-300">Condition:</span>
                                    <span class="ml-2 text-gray-900 dark:text-white">{{ $scannedDevice->condition ?? 'N/A' }}</span>
                                </div>
                                
                                <div>
                                    <span class="font-medium text-gray-700 dark:text-gray-300">Status:</span>
                                    @if ($scannedDevice->currentAssignment)
                                        <span class="ml-2 text-green-600 dark:text-green-400 font-medium">Assigned</span>
                                    @else
                                        <span class="ml-2 text-yellow-600 dark:text-yellow-400 font-medium">Available</span>
                                    @endif
                                </div>
                            </div>
                        </div>

                        {{-- Assignment Information --}}
                        @if ($scannedDevice->currentAssignment)
                            <div class="bg-blue-50 dark:bg-blue-900/20 rounded-lg p-4">
                                <h4 class="font-medium text-blue-900 dark:text-blue-100 mb-3">Assignment Details</h4>
                                <div class="grid grid-cols-1 sm:grid-cols-2 gap-3 text-sm">
                                    <div>
                                        <span class="font-medium text-blue-700 dark:text-blue-300">Assigned to:</span>
                                        <span class="ml-2 text-blue-900 dark:text-blue-100">{{ $scannedDevice->currentAssignment->user->name }}</span>
                                    </div>
                                    
                                    <div>
                                        <span class="font-medium text-blue-700 dark:text-blue-300">Branch:</span>
                                        <span class="ml-2 text-blue-900 dark:text-blue-100">{{ $scannedDevice->currentAssignment->branch->branch_name ?? 'N/A' }}</span>
                                    </div>
                                    
                                    <div>
                                        <span class="font-medium text-blue-700 dark:text-blue-300">Assigned Date:</span>
                                        <span class="ml-2 text-blue-900 dark:text-blue-100">{{ $scannedDevice->currentAssignment->assigned_date ?? 'N/A' }}</span>
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
                                :href="route('qr-code.sticker', $scannedDevice->device_id)"
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
        @endif
    </div>

    {{-- QR Scanner JavaScript --}}
    @push('scripts')
    <script src="https://cdn.jsdelivr.net/npm/html5-qrcode@2.3.8/html5-qrcode.min.js"></script>
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            let html5QrcodeScanner = null;
            let isScanning = false;

            const startButton = document.getElementById('start-scanner');
            const stopButton = document.getElementById('stop-scanner');
            const statusDiv = document.getElementById('scanner-status');

            function onScanSuccess(decodedText, decodedResult) {
                console.log(`Code matched = ${decodedText}`, decodedResult);
                
                // Stop the scanner first before clearing
                if (html5QrcodeScanner && isScanning) {
                    html5QrcodeScanner.stop().then(() => {
                        html5QrcodeScanner.clear().then(() => {
                            isScanning = false;
                            startButton.style.display = 'inline-flex';
                            stopButton.style.display = 'none';
                            statusDiv.textContent = 'QR Code scanned successfully!';

                            // Send the scanned data to Livewire
                            @this.dispatch('qr-code-scanned', { qrData: decodedText });
                        }).catch(err => {
                            console.error("Unable to clear scanner", err);
                            // Still send the data even if clear fails
                            @this.dispatch('qr-code-scanned', { qrData: decodedText });
                        });
                    }).catch(err => {
                        console.error("Unable to stop scanner", err);
                        // Try to clear anyway and send data
                        try {
                            html5QrcodeScanner.clear();
                        } catch (clearErr) {
                            console.error("Unable to clear scanner", clearErr);
                        }
                        @this.dispatch('qr-code-scanned', { qrData: decodedText });
                    });
                } else {
                    // Fallback if scanner is not properly initialized
                    @this.dispatch('qr-code-scanned', { qrData: decodedText });
                }
            }

            function onScanFailure(error) {
                // Handle scan failure, usually better to ignore here
                console.log(`Code scan error = ${error}`);
            }

            startButton.addEventListener('click', function() {
                if (isScanning) return;

                const config = {
                    fps: 10,
                    qrbox: { width: 250, height: 250 },
                    aspectRatio: 1.0
                };

                html5QrcodeScanner = new Html5Qrcode("qr-reader");
                
                html5QrcodeScanner.start(
                    { facingMode: "environment" }, // Use back camera
                    config,
                    onScanSuccess,
                    onScanFailure
                ).then(() => {
                    isScanning = true;
                    startButton.style.display = 'none';
                    stopButton.style.display = 'inline-flex';
                    statusDiv.textContent = 'Camera active - Position QR code in the frame';
                }).catch(err => {
                    console.error("Unable to start scanning", err);
                    statusDiv.textContent = 'Unable to access camera. Please check permissions.';
                });
            });

            stopButton.addEventListener('click', function() {
                if (!isScanning || !html5QrcodeScanner) return;

                html5QrcodeScanner.stop().then(() => {
                    isScanning = false;
                    startButton.style.display = 'inline-flex';
                    stopButton.style.display = 'none';
                    statusDiv.textContent = 'Camera stopped. Click "Start Camera" to scan again.';
                }).catch(err => {
                    console.error("Unable to stop scanning", err);
                });
            });

            // Auto cleanup when navigating away
            window.addEventListener('beforeunload', function() {
                if (html5QrcodeScanner && isScanning) {
                    html5QrcodeScanner.stop();
                }
            });
        });
    </script>
    @endpush
</x-filament-panels::page>
