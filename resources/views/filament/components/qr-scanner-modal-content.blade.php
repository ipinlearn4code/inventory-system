<div x-data="modalQrScanner()" x-init="init()">
    <x-qr-scanner mode="modal" />
</div>

<script>
function modalQrScanner() {
    return {
        init() {
            // Listen for device selection events
            this.$el.addEventListener('device-selected', (event) => {
                // Dispatch to parent modal
                window.dispatchEvent(new CustomEvent('device-selected', {
                    detail: event.detail
                }));
            });
        }
    }
}
</script>
