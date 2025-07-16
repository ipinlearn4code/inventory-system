<?php

namespace App\Filament\Widgets;

use App\Models\Device;
use App\Models\DeviceAssignment;
use App\Models\AssignmentLetter;
use Filament\Widgets\Widget;
use Illuminate\Support\Carbon;

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

        // Recent device additions
        $recentDevices = Device::with('bribox.category')
            ->latest()
            ->limit(3)
            ->get()
            ->map(function ($device) {
                return [
                    'type' => 'device_added',
                    'icon' => 'heroicon-m-plus-circle',
                    'color' => 'success',
                    'title' => 'Perangkat baru ditambahkan',
                    'description' => $device->brand_name . ' (' . ($device->bribox->category->category_name ?? 'Unknown') . ')',
                    'user' => $device->created_by ?? 'System',
                    'time' => $device->created_at,
                ];
            });

        // Recent assignments
        $recentAssignments = DeviceAssignment::with(['device', 'user'])
            ->latest()
            ->limit(3)
            ->get()
            ->map(function ($assignment) {
                return [
                    'type' => 'device_assigned',
                    'icon' => 'heroicon-m-arrow-right-circle',
                    'color' => 'info',
                    'title' => 'Perangkat ditugaskan',
                    'description' => "{$assignment->device->brand_name} kepada {$assignment->user->name}",
                    'user' => $assignment->created_by ?? 'System',
                    'time' => $assignment->assigned_date,
                ];
            });

        // Recent assignment letters
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
            ->merge($recentDevices)
            ->merge($recentAssignments)
            ->merge($recentLetters)
            ->sortByDesc('time')
            ->take(8)
            ->values()
            ->toArray();
    }
}
