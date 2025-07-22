<?php

namespace App\View\Components;

use Illuminate\View\Component;

class QrScanner extends Component
{
    public string $mode;
    public bool $autoStart;
    public ?string $targetInput;
    public array $emitEvents;
    public string $componentKey;

    public function __construct(
        string $mode = 'full',
        bool $autoStart = false,
        ?string $targetInput = null,
        array $emitEvents = []
    ) {
        $this->mode = $mode;
        $this->autoStart = $autoStart;
        $this->targetInput = $targetInput;
        $this->emitEvents = $emitEvents;
        $this->componentKey = 'qr-scanner-' . uniqid();
    }

    public function render()
    {
        return view('components.qr-scanner');
    }
}
