<!-- QR Scanner Modal - Inline Version for Filament SPA -->
<div x-show="qrModalOpen" 
     x-transition:enter="ease-out duration-300"
     x-transition:enter-start="opacity-0 scale-95"
     x-transition:enter-end="opacity-100 scale-100"
     x-transition:leave="ease-in duration-200"
     x-transition:leave-start="opacity-100 scale-100"
     x-transition:leave-end="opacity-0 scale-95"
     class="fixed inset-0 z-50 overflow-y-auto"
     x-cloak>
    
    <!-- Background overlay -->
    <div class="fixed inset-0 bg-black bg-opacity-50" 
         x-on:click="qrModalOpen = false"></div>
    
    <!-- Modal content -->
    <div class="flex min-h-screen items-center justify-center p-4">
        <div class="relative w-full max-w-md bg-white rounded-lg shadow-xl"
             x-data="qrScannerInline()"
             x-init="$watch('qrModalOpen', value => { if (value) initScanner(); else cleanup(); })">
            
            <!-- Modal header -->
            <div class="flex items-center justify-between p-6 border-b">
                <h3 class="text-lg font-semibold text-gray-900">Scan QR Code</h3>
                <button type="button" 
                        x-on:click="qrModalOpen = false"
                        class="text-gray-400 hover:text-gray-600 focus:outline-none">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                    </svg>
                </button>
            </div>
            
            <!-- Modal body -->
            <div class="p-6">
                <p class="text-sm text-gray-600 mb-4">
                    Scan a device QR code to automatically select the device
                </p>
                
                <!-- QR Scanner Container -->
                <div class="mb-4">
                    <div id="qr-reader-inline" class="w-full h-64 bg-gray-100 border-2 border-dashed border-gray-300 rounded-lg flex items-center justify-center">
                        <p class="text-gray-500">Camera will appear here</p>
                    </div>
                </div>
                
                <!-- Scanner Controls -->
                <div class="flex gap-2 mb-4">
                    <button type="button" 
                            x-on:click="startScanner()"
                            x-show="!isScanning"
                            class="px-4 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-blue-500">
                        Start Camera
                    </button>
                    <button type="button" 
                            x-on:click="stopScanner()"
                            x-show="isScanning"
                            class="px-4 py-2 bg-red-600 text-white rounded-lg hover:bg-red-700 focus:outline-none focus:ring-2 focus:ring-red-500">
                        Stop Camera
                    </button>
                    <button type="button" 
                            x-on:click="resetScanner()"
                            class="px-4 py-2 bg-gray-600 text-white rounded-lg hover:bg-gray-700 focus:outline-none focus:ring-2 focus:ring-gray-500">
                        Reset
                    </button>
                </div>
                
                <!-- Status Message -->
                <div id="scanner-status-inline" class="text-sm text-gray-600 mb-4">
                    Click "Start Camera" to begin scanning
                </div>
                
                <!-- Manual Selection Button -->
                <div class="text-center">
                    <button type="button" 
                            x-on:click="selectDevice()"
                            x-show="extractedAssetCode"
                            class="px-6 py-2 bg-green-600 text-white rounded-lg hover:bg-green-700 focus:outline-none focus:ring-2 focus:ring-green-500">
                        Select Device: <span x-text="extractedAssetCode"></span>
                    </button>
                </div>
            </div>
            
            <!-- Modal footer -->
            <div class="flex justify-end gap-2 p-6 border-t bg-gray-50">
                <button type="button" 
                        x-on:click="qrModalOpen = false"
                        class="px-4 py-2 text-gray-700 bg-gray-200 rounded-lg hover:bg-gray-300 focus:outline-none focus:ring-2 focus:ring-gray-500">
                    Close
                </button>
            </div>
        </div>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/html5-qrcode@2.3.8/html5-qrcode.min.js"></script>

<script>
function qrScannerInline() {
    return {
        html5QrcodeScanner: null,
        isScanning: false,
        scannedData: null,
        extractedAssetCode: null,

        initScanner() {
            console.log('Initializing QR scanner...');
        },

        startScanner() {
            if (this.isScanning) return;

            const config = {
                fps: 10,
                qrbox: { width: 250, height: 250 },
                aspectRatio: 1.0
            };

            this.html5QrcodeScanner = new Html5Qrcode("qr-reader-inline");
            
            this.html5QrcodeScanner.start(
                { facingMode: "environment" },
                config,
                (decodedText, decodedResult) => {
                    console.log(`QR Code scanned: ${decodedText}`);
                    this.onScanSuccess(decodedText);
                },
                (errorMessage) => {
                    // Handle scan failure silently
                }
            ).then(() => {
                this.isScanning = true;
                document.getElementById('scanner-status-inline').textContent = 'Camera active - Position QR code in the frame';
            }).catch(err => {
                console.error("Unable to start scanning", err);
                document.getElementById('scanner-status-inline').textContent = 'Unable to access camera. Please check permissions.';
            });
        },

        stopScanner() {
            if (!this.isScanning || !this.html5QrcodeScanner) return;

            this.html5QrcodeScanner.stop().then(() => {
                this.isScanning = false;
                document.getElementById('scanner-status-inline').textContent = 'Camera stopped. Click "Start Camera" to scan again.';
            }).catch(err => {
                console.error("Unable to stop scanning", err);
            });
        },

        onScanSuccess(decodedText) {
            // Stop the scanner
            this.stopScanner();
            
            this.scannedData = decodedText;
            
            // Extract asset code from QR data
            if (decodedText.startsWith('briven-')) {
                this.extractedAssetCode = decodedText.substring(7); // Remove 'briven-' prefix
            } else {
                this.extractedAssetCode = decodedText; // Use as-is if no prefix
            }
            
            document.getElementById('scanner-status-inline').textContent = 'QR Code scanned! Looking up device...';
            
            // Automatically try to find and select the device
            this.findAndSelectDevice(this.extractedAssetCode);
        },

        selectDevice() {
            if (!this.extractedAssetCode) return;
            
            // Find the device by asset code
            this.findAndSelectDevice(this.extractedAssetCode);
        },

        async findAndSelectDevice(assetCode) {
            try {
                // Make API call to find device by asset code
                const response = await fetch(`/api/v1/internal/devices/find-by-asset-code/${assetCode}`);
                
                if (response.ok) {
                    const result = await response.json();
                    
                    if (result.device) {
                        // Dispatch window event for device selection
                        window.dispatchEvent(new CustomEvent('device-selected', {
                            detail: {
                                deviceId: result.device.device_id,
                                deviceName: result.device.asset_code,
                                device: result.device
                            }
                        }));
                        
                        // Show success message
                        document.getElementById('scanner-status-inline').textContent = `Device ${result.device.asset_code} selected successfully!`;
                        
                        // Close the modal after a short delay
                        setTimeout(() => {
                            this.$parent.qrModalOpen = false;
                        }, 1000);
                    } else {
                        document.getElementById('scanner-status-inline').textContent = 'Device not found or not available for assignment';
                    }
                } else {
                    const error = await response.json();
                    document.getElementById('scanner-status-inline').textContent = error.error || 'Device not found or not available for assignment';
                }
            } catch (error) {
                console.error('Error finding device:', error);
                document.getElementById('scanner-status-inline').textContent = 'Error finding device. Please try manual selection.';
            }
        },

        resetScanner() {
            this.scannedData = null;
            this.extractedAssetCode = null;
            document.getElementById('scanner-status-inline').textContent = 'Click "Start Camera" to begin scanning';
        },

        cleanup() {
            if (this.html5QrcodeScanner && this.isScanning) {
                this.html5QrcodeScanner.stop().catch(err => {
                    console.error("Error stopping scanner:", err);
                });
            }
        }
    }
}
</script>

<style>
[x-cloak] { display: none !important; }
</style>
