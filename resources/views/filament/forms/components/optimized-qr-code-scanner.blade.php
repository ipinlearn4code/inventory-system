@php
    $id = $getId();
    $statePath = $getStatePath();
    $displayType = $getDisplayType();
    $buttonText = $getButtonText();
    $buttonIcon = $getButtonIcon();
    $buttonColor = $getButtonColor();
    $buttonSize = $getButtonSize();
    $isOutlined = $isOutlined();
    $isIconOnly = $isIconOnly();
    $clickableText = $getClickableText();
    $isDisabled = $isDisabled();
@endphp

<x-dynamic-component
    :component="$getFieldWrapperView()"
    :field="$field"
>
    <div class="relative">
        <!-- Input tersembunyi untuk fadlee library -->
        <input
            type="text"
            id="{{ $id }}"
            wire:model.live="{{ $statePath }}"
            data-qrcode-field="1"
            style="position: absolute; opacity: 0; pointer-events: none; z-index: -1;"
            tabindex="-1"
        />

        <!-- Custom button display -->
        @if($displayType === 'button' || $displayType === 'icon-button')
            <x-filament::button
                type="button"
                :color="$buttonColor"
                :size="$buttonSize"
                :outlined="$isOutlined"
                :icon="$buttonIcon"
                :disabled="$isDisabled"
                onclick="openScannerModal('{{ $id }}')"
            >
                @if(!$isIconOnly && $buttonText)
                    {{ $buttonText }}
                @endif
            </x-filament::button>
        @elseif($displayType === 'clickable-text')
            <button
                type="button"
                class="text-primary-600 hover:text-primary-700 hover:underline font-medium cursor-pointer focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-primary-500 transition-colors"
                onclick="openScannerModal('{{ $id }}')"
                @if($isDisabled) disabled @endif
            >
                {{ $clickableText ?: 'Click to scan QR code' }}
            </button>
        @else
            <!-- Fallback ke text input asli fadlee -->
            <input
                type="text"
                id="{{ $id }}"
                wire:model="{{ $statePath }}"
                data-qrcode-field="1"
                placeholder="{{ $getPlaceholder() }}"
                @if($isDisabled) disabled @endif
                @if($isReadOnly()) readonly @endif
                class="block w-full border-gray-300 rounded-md shadow-sm focus:border-primary-500 focus:ring-primary-500"
            />
        @endif
    </div>
</x-dynamic-component>

@once
{{-- QR Scanner Scripts - Load Once --}}
<script>
// Global QR scanner initialization - only loaded once
window.QRScannerGlobal = {
    initialized: false,
    scanners: new Map(),
    
    init() {
        if (this.initialized) return;
        
        // Load QR scanner scripts dynamically
        this.loadScripts().then(() => {
            this.initialized = true;
            this.initializeModal();
        });
    },
    
    async loadScripts() {
        // Only load if not already loaded
        if (!window.Html5Qrcode) {
            const script = document.createElement('script');
            script.src = 'https://cdn.jsdelivr.net/npm/html5-qrcode@2.3.8/html5-qrcode.min.js';
            document.head.appendChild(script);
            
            return new Promise((resolve) => {
                script.onload = resolve;
            });
        }
        return Promise.resolve();
    },
    
    initializeModal() {
        // Initialize modal HTML if not exists
        if (!document.getElementById('qrcode-scanner-modal')) {
            const modalHTML = `
                <div id="qrcode-scanner-modal" class="fi-modal fixed inset-0 z-50 min-h-full overflow-y-auto overflow-x-hidden transition duration-300 pointer-events-none opacity-0" style="display: none;">
                    <div class="relative flex min-h-full items-center justify-center p-4">
                        <div class="fi-modal-window pointer-events-auto relative flex w-full max-w-lg transform flex-col bg-white shadow-xl ring-1 ring-gray-950/5 transition-all dark:bg-gray-900 dark:ring-white/10 rounded-xl">
                            <div class="fi-modal-header flex items-center gap-x-4 px-6 py-4">
                                <h2 class="fi-modal-heading text-base font-semibold leading-6 text-gray-950 dark:text-white">
                                    QR Code Scanner
                                </h2>
                                <button type="button" onclick="QRScannerGlobal.closeModal()" class="fi-modal-close-btn ml-auto">
                                    <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                                    </svg>
                                </button>
                            </div>
                            <div class="fi-modal-content px-6 py-4">
                                <div id="qr-reader" style="width: 100%; height: 300px;"></div>
                                <div class="mt-4">
                                    <button type="button" onclick="QRScannerGlobal.closeModal()" class="fi-btn fi-btn-color-gray">
                                        Close
                                    </button>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            `;
            document.body.insertAdjacentHTML('beforeend', modalHTML);
        }
    },
    
    openScanner(inputId) {
        const modal = document.getElementById('qrcode-scanner-modal');
        const reader = document.getElementById('qr-reader');
        
        if (!modal || !reader) return;
        
        modal.style.display = 'flex';
        modal.classList.remove('opacity-0', 'pointer-events-none');
        modal.classList.add('opacity-100');
        
        this.startScanning(inputId);
    },
    
    startScanning(inputId) {
        const html5QrCode = new Html5Qrcode("qr-reader");
        this.scanners.set(inputId, html5QrCode);
        
        Html5Qrcode.getCameras().then(devices => {
            if (devices && devices.length) {
                const cameraId = devices[0].id;
                html5QrCode.start(
                    cameraId,
                    {
                        fps: 10,
                        qrbox: { width: 250, height: 250 }
                    },
                    (decodedText) => {
                        this.onScanSuccess(inputId, decodedText);
                    },
                    (errorMessage) => {
                        // Handle scan failure silently
                    }
                ).catch(err => {
                    console.error('Unable to start scanning:', err);
                });
            }
        }).catch(err => {
            console.error('Unable to get cameras:', err);
        });
    },
    
    onScanSuccess(inputId, decodedText) {
        const input = document.getElementById(inputId);
        if (input) {
            input.value = decodedText;
            input.dispatchEvent(new Event('input', { bubbles: true }));
            input.dispatchEvent(new Event('change', { bubbles: true }));
        }
        this.closeModal();
    },
    
    closeModal() {
        const modal = document.getElementById('qrcode-scanner-modal');
        if (!modal) return;
        
        // Stop all active scanners
        this.scanners.forEach((scanner, inputId) => {
            scanner.stop().catch(err => console.error('Error stopping scanner:', err));
        });
        this.scanners.clear();
        
        modal.classList.add('opacity-0', 'pointer-events-none');
        modal.classList.remove('opacity-100');
        
        setTimeout(() => {
            modal.style.display = 'none';
        }, 300);
    }
};

// Global function for opening scanner
window.openScannerModal = function(inputId) {
    if (!window.QRScannerGlobal.initialized) {
        window.QRScannerGlobal.init();
        setTimeout(() => window.QRScannerGlobal.openScanner(inputId), 1000);
    } else {
        window.QRScannerGlobal.openScanner(inputId);
    }
};

// Initialize on DOM ready
document.addEventListener('DOMContentLoaded', () => {
    window.QRScannerGlobal.init();
});
</script>
@endonce
