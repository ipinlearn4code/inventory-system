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
