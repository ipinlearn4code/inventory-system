<x-filament-panels::page>
    <div class="space-y-6">
        <!-- Device Selection Form -->
        <x-filament::section>
            <x-slot name="heading">
                Device Selection
            </x-slot>
            <x-slot name="description">
                Select devices to generate QR code stickers. Use the checkboxes below for quick filtering.
            </x-slot>

            <div class="space-y-6">
                {{ $this->form }}
            </div>
        </x-filament::section>

        <!-- Preview Section -->
        @if ($showPreview && $previewStickers->isNotEmpty())
            <x-filament::section>
                <x-slot name="heading">
                    Sticker Preview
                </x-slot>
                <x-slot name="description">
                    Preview of {{ $previewStickers->count() }} QR code stickers. Click on individual stickers to print them separately.
                </x-slot>

                <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4 gap-4">
                    @foreach ($previewStickers as $stickerData)
                        <div class="bg-white dark:bg-gray-800 border border-gray-200 dark:border-gray-700 rounded-lg p-4 space-y-3 hover:shadow-md transition-shadow duration-200">
                            @if ($stickerData['error'])
                                <div class="text-center">
                                    <div class="text-red-600 dark:text-red-400 text-sm">
                                        <x-heroicon-o-exclamation-triangle class="h-12 w-12 mx-auto mb-2" />
                                        Error generating QR code
                                    </div>
                                    <p class="text-xs text-gray-500 dark:text-gray-400">{{ $stickerData['error'] }}</p>
                                </div>
                            @else
                                <div class="text-center">
                                    <img src="{{ $stickerData['qrCodeDataUrl'] }}" 
                                         alt="QR Code for {{ $stickerData['device']->asset_code }}"
                                         class="mx-auto h-24 w-24 sm:h-32 sm:w-32 border border-gray-200 dark:border-gray-600 rounded">
                                </div>
                            @endif

                            <div class="space-y-2 text-sm">
                                <div class="font-semibold text-gray-900 dark:text-white text-center">
                                    {{ $stickerData['device']->asset_code }}
                                </div>
                                
                                <div class="text-xs text-gray-600 dark:text-gray-300 space-y-1">
                                    <div><span class="font-medium">Asset Code:</span> {{ $stickerData['device']->asset_code }}</div>
                                    <div><span class="font-medium">Category:</span> {{ $stickerData['device']->bribox->category->category_name ?? 'N/A' }}</div>
                                    <div>
                                        <span class="font-medium">Device Name:</span> 
                                        <span class="truncate block">{{ $stickerData['device']->brand }} {{ $stickerData['device']->brand_name }} </span>
                                    </div>
                                    <div><span class="font-medium">SN:</span> {{ $stickerData['device']->serial_number }}</div>
                                    
                                </div>
                            </div>
                        </div>
                    @endforeach
                </div>

                <!-- Print Summary -->
                <div class="mt-6 p-4 bg-gray-50 dark:bg-gray-800 rounded-lg">
                    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
                        <div class="text-sm text-gray-600 dark:text-gray-300">
                            <p><span class="font-medium">Total devices selected:</span> {{ $previewStickers->count() }}</p>
                            <p><span class="font-medium">Ready to print:</span> {{ $previewStickers->where('error', null)->count() }}</p>
                            @if ($previewStickers->where('error', '!=', null)->count() > 0)
                                <p class="text-red-600 dark:text-red-400">
                                    <span class="font-medium">Errors:</span> {{ $previewStickers->where('error', '!=', null)->count() }}
                                </p>
                            @endif
                        </div>
                        
                        <div class="flex gap-2">
                            <x-filament::button
                                size="sm"
                                color="success"
                                icon="heroicon-o-printer"
                                tag="a"
                                :href="route('qr-code.stickers.pdf', ['device_ids' => collect($data['devices'] ?? [])->toArray()])"
                                target="_blank"
                                :disabled="empty($data['devices'] ?? [])"
                            >
                                Print All Stickers
                            </x-filament::button>
                        </div>
                    </div>
                </div>
            </x-filament::section>
        @endif

        <!-- Instructions Section -->
        @if (!$showPreview)
            <x-filament::section>
                <x-slot name="heading">
                    Instructions
                </x-slot>

                <div class="prose dark:prose-invert max-w-none">
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                        <div>
                            <h4 class="text-sm font-semibold text-gray-900 dark:text-white mb-2">How to use:</h4>
                            <ol class="text-sm text-gray-600 dark:text-gray-300 space-y-1 list-decimal list-inside">
                                <li>Select devices from the dropdown above</li>
                                <li>Use quick filters for assigned/unassigned devices</li>
                                <li>Click "Generate Preview" to see the stickers</li>
                                <li>Review and print individual or bulk stickers</li>
                            </ol>
                        </div>
                        
                        <div>
                            <h4 class="text-sm font-semibold text-gray-900 dark:text-white mb-2">QR Code Format:</h4>
                            <p class="text-sm text-gray-600 dark:text-gray-300">
                                Each QR code contains: <code class="px-1 py-0.5 bg-gray-100 dark:bg-gray-700 rounded text-xs">briven-{asset_code}</code>
                            </p>
                            <p class="text-xs text-gray-500 dark:text-gray-400 mt-1">
                                Example: briven-ASSET001 for asset code ASSET001
                            </p>
                        </div>
                    </div>
                </div>
            </x-filament::section>
        @endif
    </div>
</x-filament-panels::page>