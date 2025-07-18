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
    protected static ?int $navigationSort = 10;

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
            Action::make('openScanner')
                ->label('Open QR Scanner')
                ->icon('heroicon-o-camera')
                ->color('primary')
                ->url(route('qr-scanner.index'))
                ->openUrlInNewTab(),
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
}
