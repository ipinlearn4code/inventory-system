<?php

namespace App\Filament\Forms\Components;

use Fadlee\FilamentQrCodeField\Forms\Components\QrCodeInput;

class QrCodeScanner extends QrCodeInput
{
    protected string $view = 'filament.forms.components.optimized-qr-code-scanner';

    protected string $displayType = 'button';
    protected ?string $buttonText = null;
    protected ?string $buttonIcon = null;
    protected ?string $buttonColor = null;
    protected ?string $buttonSize = null;
    protected bool $outlined = false;
    protected bool $iconOnly = false;
    protected ?string $clickableText = null;

    protected function setUp(): void
    {
        parent::setUp();
        
        // Ensure we have the required attribute for fadlee library
        $this->extraInputAttributes(['data-qrcode-field' => '1']);
    }

    /**
     * Configure as a button with text
     */
    public function asButton(
        string $text = 'Scan QR Code',
        string $color = 'primary',
        string $size = 'md',
        bool $outlined = false
    ): static {
        $this->displayType = 'button';
        $this->buttonText = $text;
        $this->buttonColor = $color;
        $this->buttonSize = $size;
        $this->outlined = $outlined;
        return $this;
    }

    /**
     * Configure as an icon button
     */
    public function asIconButton(
        string $icon = 'heroicon-o-qr-code',
        string $color = 'primary',
        string $size = 'md',
        bool $outlined = false
    ): static {
        $this->displayType = 'icon-button';
        $this->buttonIcon = $icon;
        $this->buttonColor = $color;
        $this->buttonSize = $size;
        $this->outlined = $outlined;
        $this->iconOnly = true;
        return $this;
    }

    /**
     * Configure as clickable text
     */
    public function asClickableText(string $text = 'Click to scan QR code'): static
    {
        $this->displayType = 'clickable-text';
        $this->clickableText = $text;
        return $this;
    }

    /**
     * Configure button with both icon and text
     */
    public function withIcon(string $icon): static
    {
        $this->buttonIcon = $icon;
        $this->iconOnly = false;
        return $this;
    }

    public function getDisplayType(): string
    {
        return $this->displayType;
    }

    public function getButtonText(): ?string
    {
        return $this->buttonText;
    }

    public function getButtonIcon(): ?string
    {
        return $this->buttonIcon;
    }

    public function getButtonColor(): ?string
    {
        return $this->buttonColor ?? 'primary';
    }

    public function getButtonSize(): ?string
    {
        return $this->buttonSize ?? 'md';
    }

    public function isOutlined(): bool
    {
        return $this->outlined;
    }

    public function isIconOnly(): bool
    {
        return $this->iconOnly;
    }

    public function getClickableText(): ?string
    {
        return $this->clickableText;
    }
}
