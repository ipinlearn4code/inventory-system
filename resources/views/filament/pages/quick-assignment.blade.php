<x-filament-panels::page>
    <form wire:submit="submit">
        {{ $this->form }}

        <div class="flex items-center justify-end gap-4 mt-6">
            <x-filament::button type="submit">
                Create Assignment and Letter
            </x-filament::button>
        </div>
    </form>
</x-filament-panels::page>
