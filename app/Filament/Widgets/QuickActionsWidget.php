<?php

namespace App\Filament\Widgets;

use Filament\Widgets\Widget;

class QuickActionsWidget extends Widget
{
    protected static string $view = 'filament.widgets.quick-actions-widget';
    protected static ?int $sort = 4;
    protected static ?string $heading = '⚡ Aksi Cepat';
    
    protected int | string | array $columnSpan = 2;

    public function getQuickActions(): array
    {
        $auth = session('authenticated_user');
        $authModel = \App\Models\Auth::where('pn', $auth['pn'] ?? '')->first();
        
        $actions = [];
        
        // Check permissions for each action
        if ($authModel && ($authModel->hasRole('superadmin') || $authModel->hasRole('admin'))) {
            $actions = [
                [
                    'label' => '➕ Tambah Perangkat Baru',
                    'icon' => 'heroicon-m-plus-circle',
                    'color' => 'primary',
                    'url' => \App\Filament\Resources\DeviceResource::getUrl('create'),
                    'description' => 'Daftarkan perangkat baru ke sistem',
                ],
                [
                    'label' => '📋 Buat Penugasan Perangkat',
                    'icon' => 'heroicon-m-arrow-right-circle',
                    'color' => 'info',
                    'url' => \App\Filament\Resources\DeviceAssignmentResource::getUrl('create'),
                    'description' => 'Tugaskan perangkat ke pengguna',
                ],
                [
                    'label' => '👤 Tambah Pengguna Baru',
                    'icon' => 'heroicon-m-user-plus',
                    'color' => 'warning',
                    'url' => \App\Filament\Resources\UserResource::getUrl('create'),
                    'description' => 'Daftarkan pengguna baru',
                ],
                [
                    'label' => '📊 Cetak Laporan Bulanan',
                    'icon' => 'heroicon-m-document-chart-bar',
                    'color' => 'success',
                    'url' => '#',
                    'description' => 'Generate laporan inventaris',
                    'onclick' => 'alert("Fitur laporan akan segera tersedia!")',
                ],
            ];
        } else {
            // Actions for regular users
            $actions = [
                [
                    'label' => 'Lihat Perangkat Saya',
                    'icon' => 'heroicon-m-computer-desktop',
                    'color' => 'info',
                    'url' => \App\Filament\Resources\DeviceAssignmentResource::getUrl('index'),
                    'description' => 'Lihat perangkat yang ditugaskan',
                ],
                [
                    'label' => 'Lapor Kerusakan',
                    'icon' => 'heroicon-m-exclamation-triangle',
                    'color' => 'danger',
                    'url' => '#',
                    'description' => 'Laporkan perangkat rusak',
                    'onclick' => 'alert("Silahkan hubungi admin untuk melaporkan kerusakan!")',
                ],
            ];
        }
        
        return $actions;
    }
}
