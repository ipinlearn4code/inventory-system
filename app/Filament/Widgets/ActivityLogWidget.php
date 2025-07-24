<?php

namespace App\Filament\Widgets;

use App\Models\Device;
use App\Models\DeviceAssignment;
use App\Models\AssignmentLetter;
use App\Models\InventoryLog;
use Filament\Widgets\Widget;
use Illuminate\Support\Carbon;
use App\Models\User;

class ActivityLogWidget extends Widget
{
    protected static string $view = 'filament.widgets.activity-log-widget';
    protected static ?int $sort = 8;

    protected int | string | array $columnSpan = [
        'default' => 'full',
        'md' => 'full',
        'lg' => 'full',
    ];

    public function getRecentActivities(): array
    {
        $activities = collect();

        // Get recent inventory logs for device operations
        $recentDeviceLogs = InventoryLog::where('changed_fields', 'devices')
            ->whereIn('action_type', ['CREATE', 'UPDATE', 'DELETE'])
            ->latest('created_at')
            ->limit(5)
            ->get()
            ->map(function ($log) {
                $newValue = $log->new_value ? json_decode($log->new_value, true) : null;
                $oldValue = $log->old_value ? json_decode($log->old_value, true) : null;
                
                $icon = match($log->action_type) {
                    'CREATE' => 'heroicon-m-plus-circle',
                    'UPDATE' => 'heroicon-m-pencil-square',
                    'DELETE' => 'heroicon-m-trash',
                    default => 'heroicon-m-cog'
                };
                
                $color = match($log->action_type) {
                    'CREATE' => 'success',
                    'UPDATE' => 'warning',
                    'DELETE' => 'danger',
                    default => 'gray'
                };
                
                $title = match($log->action_type) {
                    'CREATE' => 'Perangkat baru ditambahkan',
                    'UPDATE' => 'Perangkat diperbarui',
                    'DELETE' => 'Perangkat dihapus',
                    default => 'Perubahan perangkat'
                };
                
                $description = 'Unknown device';
                if ($newValue) {
                    $description = ($newValue['brand'] ?? '') . ' ' . ($newValue['brand_name'] ?? '') . 
                                 ' (' . ($newValue['asset_code'] ?? 'No code') . ')';
                } elseif ($oldValue) {
                    $description = ($oldValue['brand'] ?? '') . ' ' . ($oldValue['brand_name'] ?? '') . 
                                 ' (' . ($oldValue['asset_code'] ?? 'No code') . ')';
                }

                $username = User::where('pn', $log->created_by)->first()?->name ?? "System";
                
                return [
                    'type' => 'device_' . strtolower($log->action_type),
                    'icon' => $icon,
                    'color' => $color,
                    'title' => $title,
                    'description' => $description,
                    'user' => $username,
                    'time' => $log->created_at,
                ];
            });

        // Get recent inventory logs for assignment operations
        $recentAssignmentLogs = InventoryLog::where('changed_fields', 'device_assignments')
            ->whereIn('action_type', ['CREATE', 'UPDATE', 'DELETE'])
            ->latest('created_at')
            ->limit(3)
            ->get()
            ->map(function ($log) {
                $newValue = $log->new_value ? json_decode($log->new_value, true) : null;
                
                $icon = match($log->action_type) {
                    'CREATE' => 'heroicon-m-arrow-right-circle',
                    'UPDATE' => 'heroicon-m-arrow-path',
                    'DELETE' => 'heroicon-m-x-circle',
                    default => 'heroicon-m-cog'
                };
                
                $color = match($log->action_type) {
                    'CREATE' => 'info',
                    'UPDATE' => 'warning',
                    'DELETE' => 'danger',
                    default => 'gray'
                };
                
                $title = match($log->action_type) {
                    'CREATE' => 'Perangkat ditugaskan',
                    'UPDATE' => 'Assignment diperbarui',
                    'DELETE' => 'Assignment dihapus',
                    default => 'Perubahan assignment'
                };
                
                $description = 'Device assignment';
                if ($newValue && isset($newValue['device_id'])) {
                    $description = "Device ID: {$newValue['device_id']}";
                    if ($log->user_affected) {
                        $description .= " kepada {$log->user_affected}";
                    }
                }
                
                return [
                    'type' => 'assignment_' . strtolower($log->action_type),
                    'icon' => $icon,
                    'color' => $color,
                    'title' => $title,
                    'description' => $description,
                    'user' => $log->created_by ?? 'System',
                    'time' => $log->created_at,
                ];
            });

        // Recent assignment letters (keeping original logic for now)
        $recentLetters = AssignmentLetter::with(['assignment.user', 'assignment.device'])
            ->latest()
            ->limit(2)
            ->get()
            ->map(function ($letter) {
                return [
                    'type' => 'letter_issued',
                    'icon' => 'heroicon-m-document-text',
                    'color' => 'warning',
                    'title' => 'Surat tugas diterbitkan',
                    'description' => "Surat {$letter->letter_number} untuk {$letter->assignment->user->name}",
                    'user' => 'Admin',
                    'time' => $letter->letter_date,
                ];
            });

        // Combine and sort by time
        return $activities
            ->merge($recentDeviceLogs)
            ->merge($recentAssignmentLogs)
            ->merge($recentLetters)
            ->sortByDesc('log_id')
            ->take(5)
            ->values()
            ->toArray();
    }
}
