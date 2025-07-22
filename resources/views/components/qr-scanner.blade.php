<div wire:key="{{ $componentKey }}">
    <livewire:qr-scanner 
        :mode="$mode"
        :auto-start="$autoStart"
        :target-input="$targetInput"
        :emit-events="$emitEvents"
        :key="$componentKey"
    />
</div>
