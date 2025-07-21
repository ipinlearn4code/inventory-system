<div x-data="qrScannerModal()" x-init="initScanner()" class="space-y-4">
    <div class="text-center">
        <div id="qr-reader-modal" class="w-full max-w-md mx-auto h-64 border-2 border-dashed border-gray-300 dark:border-gray-600 rounded-lg overflow-hidden">
        </div>
        
        <div class="mt-4 space-y-2">
            <div class="flex justify-center gap-2">
                <button 
                    type="button"
                    x-show="!isScanning"
                    @click="startScanner()"
                    class="inline-flex items-center px-3 py-2 text-sm font-medium text-white bg-primary-600 border border-transparent rounded-md hover:bg-primary-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-primary-500"
                >
                    <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M14.828 14.828a4 4 0 01-5.656 0M9 10h1m4 0h1m-6 4h1m4 0h1m-6-8h8a2 2 0 012 2v8a2 2 0 01-2 2H8a2 2 0 01-2-2V8a2 2 0 012-2z"></path>
                    </svg>
                    Start Camera
                </button>
                
                <button 
                    type="button"
                    x-show="isScanning"
                    @click="stopScanner()"
                    class="inline-flex items-center px-3 py-2 text-sm font-medium text-gray-700 bg-gray-200 border border-gray-300 rounded-md hover:bg-gray-300 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-gray-500"
                >
                    <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 10l2 2 4-4"></path>
                    </svg>
                    Stop Camera
                </button>
            </div>
            
            <div id="scanner-status-modal" class="text-sm text-gray-600 dark:text-gray-400">
                Click "Start Camera" to begin scanning
            </div>
        </div>
    </div>

    <div x-show="scannedData" class="mt-4 p-4 bg-green-50 dark:bg-green-900/20 rounded-lg">
        <h4 class="text-sm font-semibold text-green-900 dark:text-green-100 mb-2">
            QR Code Scanned Successfully!
        </h4>
        <div class="text-sm text-green-800 dark:text-green-200">
            <p><strong>Scanned Data:</strong> <span x-text="scannedData"></span></p>
            <p><strong>Asset Code:</strong> <span x-text="extractedAssetCode"></span></p>
        </div>
        
        <div class="mt-3 flex gap-2">
            <button 
                type="button"
                @click="selectDevice()"
                class="inline-flex items-center px-3 py-2 text-sm font-medium text-white bg-green-600 border border-transparent rounded-md hover:bg-green-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-green-500"
            >
                Select This Device
            </button>
            
            <button 
                type="button"
                @click="resetScanner()"
                class="inline-flex items-center px-3 py-2 text-sm font-medium text-gray-700 bg-gray-200 border border-gray-300 rounded-md hover:bg-gray-300 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-gray-500"
            >
                Scan Again
            </button>
        </div>
    </div>

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

<script src="https://cdn.jsdelivr.net/npm/html5-qrcode@2.3.8/html5-qrcode.min.js"></script>

<script>
function qrScannerModal() {
    return {
        html5QrcodeScanner: null,
        isScanning: false,
        scannedData: null,
        extractedAssetCode: null,

        initScanner() {
            // Initialize scanner when modal opens
        },

        startScanner() {
            if (this.isScanning) return;

            const config = {
                fps: 10,
                qrbox: { width: 250, height: 250 },
                aspectRatio: 1.0
            };

            this.html5QrcodeScanner = new Html5Qrcode("qr-reader-modal");
            
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
                document.getElementById('scanner-status-modal').textContent = 'Camera active - Position QR code in the frame';
            }).catch(err => {
                console.error("Unable to start scanning", err);
                document.getElementById('scanner-status-modal').textContent = 'Unable to access camera. Please check permissions.';
            });
        },

        stopScanner() {
            if (!this.isScanning || !this.html5QrcodeScanner) return;

            this.html5QrcodeScanner.stop().then(() => {
                this.isScanning = false;
                document.getElementById('scanner-status-modal').textContent = 'Camera stopped. Click "Start Camera" to scan again.';
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
            
            document.getElementById('scanner-status-modal').textContent = 'QR Code scanned! Looking up device...';
            
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
                                deviceId: result.device.id,
                                deviceName: result.device.asset_code,
                                device: result.device
                            }
                        }));
                        
                        // Show success message
                        document.getElementById('scanner-status-modal').textContent = `Device ${result.device.asset_code} selected successfully!`;
                        
                        // Close the modal after a short delay
                        setTimeout(() => {
                            this.closeModal();
                        }, 1000);
                    } else {
                        document.getElementById('scanner-status-modal').textContent = 'Device not found or not available for assignment';
                    }
                } else {
                    const error = await response.json();
                    document.getElementById('scanner-status-modal').textContent = error.error || 'Device not found or not available for assignment';
                }
            } catch (error) {
                console.error('Error finding device:', error);
                document.getElementById('scanner-status-modal').textContent = 'Error finding device. Please try manual selection.';
            }
        },

        resetScanner() {
            this.scannedData = null;
            this.extractedAssetCode = null;
            document.getElementById('scanner-status-modal').textContent = 'Click "Start Camera" to begin scanning';
        },

        closeModal() {
            if (this.html5QrcodeScanner && this.isScanning) {
                this.html5QrcodeScanner.stop();
            }
            // Close the Filament modal
            this.$dispatch('close-modal');
        }
    }
}
</script>
