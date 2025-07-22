<?php

namespace App\Filament\Components;

use Filament\Forms\Components\Actions\Action;
use Filament\Forms\Components\Component;
use Filament\Support\Concerns\HasColor;
use Filament\Support\Concerns\HasIcon;

class QrScannerAction extends Action
{
    use HasColor;
    use HasIcon;

    protected string $targetInput;
    protected array $emitEvents = [];

    public static function make(?string $name = null): static
    {
        $static = parent::make($name ?? 'qrScanner');
        
        $static->label('Scan QR Code')
            ->icon('heroicon-o-qr-code')
            ->color('primary')
            ->modalHeading('QR Code Scanner')
            ->modalWidth('lg')
            ->modalContent(view('filament.components.qr-scanner-action-modal'))
            ->action(function () {
                // Action is handled by JavaScript/Livewire events
            });

        return $static;
    }

    public function targetInput(string $input): static
    {
        $this->targetInput = $input;
        return $this;
    }

    public function emitEvents(array $events): static
    {
        $this->emitEvents = $events;
        return $this;
    }

    public function getTargetInput(): string
    {
        return $this->targetInput ?? '';
    }

    public function getEmitEvents(): array
    {
        return $this->emitEvents;
    }
}
