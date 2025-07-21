<x-filament::card class="flex items-center justify-center h-full">
    <div class="space-y-4 text-center">
        @if ($qrCodeDataUrl)
            <div>
                <img src="{{ $qrCodeDataUrl }}" 
                     alt="QR Code for {{ $assetCode }}"
                     class="mx-auto h-48 w-48 border border-gray-200 dark:border-gray-600 rounded-lg shadow-sm">
            </div>
            
            <div class="flex justify-center space-x-2">
                <x-filament::button
                    size="sm"
                    color="success"
                    icon="heroicon-o-printer"
                    tag="a"
                    href="{{ route('qr-code.sticker', $deviceId) }}"
                    target="_blank"
                >
                    Print QR Sticker
                </x-filament::button>
                
            </div>
        @else
            <div class="text-center text-gray-500 dark:text-gray-400">
                <x-heroicon-o-exclamation-triangle class="h-12 w-12 mx-auto mb-2" />
                <p>Error generating QR code</p>
            </div>
        @endif
    </div>
</x-filament::card>
