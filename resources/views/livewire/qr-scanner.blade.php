<div class="qr-scanner-component" x-data="qrScannerComponent($wire)" wire:key="qr-scanner-{{ uniqid() }}">
    @if ($mode === 'full')
        {{-- Full Page Scanner --}}
        <div class="space-y-6">
            @if (!$scannedDevice)
                <x-filament::section>
                    <x-slot name="heading">
                        QR Code Scanner
                    </x-slot>
                    <x-slot name="description">
                        Point your camera at a QR code to scan device information
                    </x-slot>

                    @include('livewire.partials.qr-scanner-camera')
                </x-filament::section>
            @endif

            @if ($scannedDevice)
                @include('livewire.partials.qr-scanner-result')
            @endif
        </div>
    @elseif ($mode === 'modal')
        {{-- Modal Scanner --}}
        @include('livewire.partials.qr-scanner-modal-content')
    @else
        {{-- Inline Scanner --}}
        @include('livewire.partials.qr-scanner-inline')
    @endif

    {{-- QR Scanner JavaScript --}}
    @push('scripts')
    <script src="https://cdn.jsdelivr.net/npm/html5-qrcode@2.3.8/html5-qrcode.min.js"></script>
    <script>
        document.addEventListener('alpine:init', () => {
            Alpine.data('qrScannerComponent', (wire) => ({
                html5QrcodeScanner: null,
                isScanning: false,
                scannedData: null,
                extractedAssetCode: null,

                init() {
                    // Listen for Livewire events
                    this.$wire.on('scanner-start', () => {
                        this.startScanner();
                    });

                    this.$wire.on('scanner-stop', () => {
                        this.stopScanner();
                    });

                    this.$wire.on('scanner-reset', () => {
                        this.resetScanner();
                    });

                    // Auto-start if configured
                    if (wire.autoStart) {
                        setTimeout(() => this.startScanner(), 500);
                    }

                    // Cleanup on navigate away
                    window.addEventListener('beforeunload', () => {
                        this.cleanup();
                    });

                    // Handle Livewire navigation
                    document.addEventListener('livewire:navigating', () => {
                        this.cleanup();
                    });
                },

                startScanner() {
                    if (this.isScanning) return;

                    const config = {
                        fps: 10,
                        qrbox: { width: 250, height: 250 },
                        aspectRatio: 1.0,
                        showTorchButtonIfSupported: true
                    };

                    this.html5QrcodeScanner = new Html5Qrcode("qr-reader");
                    
                    this.html5QrcodeScanner.start(
                        { facingMode: "environment" },
                        config,
                        (decodedText, decodedResult) => {
                            this.onScanSuccess(decodedText);
                        },
                        (errorMessage) => {
                            // Handle scan failure silently
                        }
                    ).then(() => {
                        this.isScanning = true;
                        this.updateStatus('Camera active - Position QR code in the frame');
                    }).catch(err => {
                        console.error("Unable to start scanning", err);
                        this.updateStatus('Unable to access camera. Please check permissions.');
                    });
                },

                stopScanner() {
                    if (!this.isScanning || !this.html5QrcodeScanner) return;

                    this.html5QrcodeScanner.stop().then(() => {
                        this.isScanning = false;
                        this.updateStatus('Camera stopped. Click "Start Camera" to scan again.');
                    }).catch(err => {
                        console.error("Unable to stop scanning", err);
                        this.isScanning = false;
                    });
                },

                onScanSuccess(decodedText) {
                    console.log(`QR Code scanned: ${decodedText}`);
                    
                    // Stop the scanner
                    this.stopScanner();
                    
                    this.scannedData = decodedText;
                    
                    // Extract asset code from QR data
                    if (decodedText.startsWith('briven-')) {
                        this.extractedAssetCode = decodedText.substring(7);
                    } else {
                        this.extractedAssetCode = decodedText;
                    }
                    
                    this.updateStatus('QR Code scanned! Processing...');
                    
                    // Send to Livewire component
                    wire.call('handleQRCodeScanned', { qrData: decodedText });
                },

                resetScanner() {
                    this.cleanup();
                    this.scannedData = null;
                    this.extractedAssetCode = null;
                    this.updateStatus('Click "Start Camera" to begin scanning');
                },

                cleanup() {
                    if (this.html5QrcodeScanner && this.isScanning) {
                        try {
                            this.html5QrcodeScanner.stop();
                        } catch (e) {
                            console.warn('Error stopping scanner:', e);
                        }
                    }
                    this.isScanning = false;
                },

                updateStatus(message) {
                    const statusElement = document.getElementById('scanner-status');
                    if (statusElement) {
                        statusElement.textContent = message;
                    }
                }
            }))
        });
    </script>
    @endpush
</div>
