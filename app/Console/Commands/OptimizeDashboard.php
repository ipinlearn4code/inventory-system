<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Services\DashboardOptimizationService;

class OptimizeDashboard extends Command
{
    /**
     * The name and signature of the console command.
     */
    protected $signature = 'dashboard:optimize {--report : Show optimization report}';

    /**
     * The console command description.
     */
    protected $description = 'Optimize dashboard performance and show statistics';

    /**
     * Execute the console command.
     */
    public function handle(): int
    {
        if ($this->option('report')) {
            $this->showOptimizationReport();
        } else {
            $this->optimizeDashboard();
        }

        return Command::SUCCESS;
    }

    /**
     * Optimize dashboard assets
     */
    private function optimizeDashboard(): void
    {
        $this->info('🚀 Starting dashboard optimization...');
        
        // Register optimized assets
        DashboardOptimizationService::registerOptimizedAssets();
        
        $this->info('✅ Dashboard optimization completed!');
        $this->line('');
        $this->line('Optimizations applied:');
        $this->line('- ✅ Script deduplication enabled');
        $this->line('- ✅ Global asset manager registered');
        $this->line('- ✅ Chart.js shared loading configured');
        $this->line('- ✅ QR scanner optimized');
        $this->line('');
        $this->info('💡 Run with --report flag to see detailed statistics');
    }

    /**
     * Show optimization report
     */
    private function showOptimizationReport(): void
    {
        $this->info('📊 Dashboard Optimization Report');
        $this->line('');

        $report = DashboardOptimizationService::generateOptimizationReport();

        // Show summary
        $this->line('<fg=cyan>📋 Summary</>');
        $this->table(
            ['Metric', 'Value'],
            [
                ['Total JS Files', $report['summary']['total_files']],
                ['Total Size', $report['summary']['total_size_mb'] . ' MB'],
                ['Large Files (>100KB)', $report['summary']['large_files_count']],
                ['Optimization Status', $report['summary']['optimization_status']],
            ]
        );

        // Show largest files
        if (!empty($report['largest_files'])) {
            $this->line('');
            $this->line('<fg=yellow>📦 Largest JavaScript Files</>');
            $this->table(
                ['File', 'Size (KB)', 'Path'],
                array_map(fn($file) => [
                    $file['name'],
                    number_format($file['size_kb'], 2),
                    $file['path']
                ], $report['largest_files'])
            );
        }

        // Show recommendations
        if (!empty($report['recommendations'])) {
            $this->line('');
            $this->line('<fg=green>💡 Optimization Recommendations</>');
            
            foreach ($report['recommendations'] as $rec) {
                $icon = match($rec['type']) {
                    'critical' => '🔴',
                    'warning' => '🟡',
                    'info' => '🔵',
                    default => '⚪'
                };
                
                $this->line("{$icon} <fg=white>{$rec['message']}</>");
                $this->line("   Impact: {$rec['impact']}");
                $this->line('');
            }
        }
    }
}
