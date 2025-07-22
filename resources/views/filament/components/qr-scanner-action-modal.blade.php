<div x-data="qrScannerActionModal()" x-init="initScanner()">
    <x-qr-scanner mode="modal" />
    
    <script>
        function qrScannerActionModal() {
            return {
                initScanner() {
                    // Listen for device selection
                    this.$el.addEventListener('device-selected', (event) => {
                        const { deviceId, assetCode, device } = event.detail;
                        
                        // Find the target input and set its value
                        const targetInput = document.querySelector('[name="device_id"]') || 
                                          document.querySelector('[name="asset_code"]');
                        
                        if (targetInput) {
                            // Trigger change event for Livewire/Alpine
                            targetInput.value = deviceId || assetCode;
                            targetInput.dispatchEvent(new Event('input', { bubbles: true }));
                            targetInput.dispatchEvent(new Event('change', { bubbles: true }));
                        }
                        
                        // Dispatch to parent component
                        this.$dispatch('device-selected-from-qr', {
                            deviceId,
                            assetCode,
                            device
                        });
                        
                        // Close modal
                        setTimeout(() => {
                            this.$dispatch('close-modal');
                        }, 1000);
                    });
                }
            }
        }
    </script>
</div>
