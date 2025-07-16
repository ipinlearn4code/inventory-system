<?php

namespace App\Filament\Widgets;

use App\Models\MainBranch;
use App\Models\Branch;
use Filament\Forms\Components\Select;
use Filament\Forms\Form;
use Filament\Tables\Filters\Filter;
use Filament\Widgets\Widget;
use Illuminate\Database\Eloquent\Builder;

class GlobalFilterWidget extends Widget
{
    protected static string $view = 'filament.widgets.global-filter-widget';    protected static ?int $sort = 2;

    protected int | string | array $columnSpan = [
        'default' => 'full',
        'md' => 'full',
        'lg' => 'full',
    ];
    
    public ?int $mainBranchId = null;
    public ?int $branchId = null;
    
    public function mount(): void
    {
        // Get default values from session if available
        $this->mainBranchId = session('dashboard_main_branch_filter');
        $this->branchId = session('dashboard_branch_filter');
    }

    public function updatedMainBranchId($value): void
    {
        session(['dashboard_main_branch_filter' => $value]);
        $this->branchId = null; // Reset branch filter when main branch changes
        session()->forget('dashboard_branch_filter');
        $this->dispatch('filtersUpdated');
    }

    public function updatedBranchId($value): void
    {
        session(['dashboard_branch_filter' => $value]);
        $this->dispatch('filtersUpdated');
    }

    public function getMainBranchOptions(): array
    {
        return [null => 'Semua Kantor Utama'] + MainBranch::pluck('main_branch_name', 'main_branch_id')->toArray();
    }

    public function getBranchOptions(): array
    {
        $options = [null => 'Semua Kantor Cabang'];
        
        if ($this->mainBranchId) {
            $branches = Branch::where('main_branch_id', $this->mainBranchId)
                ->pluck('unit_name', 'branch_id')
                
                ->toArray();
            return $options + $branches;
        }
        
        $branches = Branch::pluck('unit_name', 'branch_id')->toArray();
        return $options + $branches;
    }
}
