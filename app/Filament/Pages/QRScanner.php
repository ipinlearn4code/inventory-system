<?php

namespace App\Filament\Pages;

use Filament\Pages\Page;
use Filament\Actions\Action;

class QRScanner extends Page
{
    protected static ?string $navigationIcon = 'heroicon-o-qr-code';
    protected static string $view = 'filament.pages.qr-scanner';
    protected static ?string $navigationGroup = 'Device Management';
    protected static ?string $navigationLabel = 'QR Scanner';
    protected static ?int $navigationSort = 9;

    public static function canAccess(): bool
    {
        $auth = session('authenticated_user');
        if (!$auth) return false;
        
        $authModel = \App\Models\Auth::where('pn', $auth['pn'])->first();
        return $authModel && ($authModel->hasRole('superadmin') || $authModel->hasRole('admin') || $authModel->hasRole('user'));
    }

    protected function getHeaderActions(): array
    {
        return [
            Action::make('refreshPage')
                ->label('Refresh Scanner')
                ->icon('heroicon-o-arrow-path')
                ->color('gray')
                ->action(fn () => $this->redirect(static::getUrl())),
        ];
    }

    public function getTitle(): string
    {
        return 'QR Code Scanner';
    }

    public function getHeading(): string
    {
        return 'QR Code Scanner';
    }

    public function getSubheading(): ?string
    {
        return 'Scan QR codes to view device information instantly';
    }
}
