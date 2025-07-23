<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Contracts\InventoryLogServiceInterface;
use App\Models\InventoryLog;

class TestLogging extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'test:logging';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Test inventory logging functionality';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $this->info('Testing inventory logging...');

        try {
            // Test direct model creation
            $this->info('Testing direct InventoryLog model...');
            $log = InventoryLog::create([
                'changed_fields' => 'direct_test',
                'action_type' => 'CREATE',
                'old_value' => null,
                'new_value' => json_encode(['test' => 'direct']),
                'user_affected' => null,
                'created_by' => 'testuser',
                'created_at' => now(),
            ]);
            $this->info('Direct model log created with ID: ' . $log->getKey());

            // Test service
            $this->info('Testing InventoryLogService...');
            $service = app(InventoryLogServiceInterface::class);
            $this->info('Service resolved: ' . get_class($service));
            
            $service->logInventoryAction('service_test', 'CREATE', null, ['test' => 'service']);
            $this->info('Service log created');

            // Check total count
            $count = InventoryLog::count();
            $this->info('Total logs in database: ' . $count);

            // Show recent logs
            $recent = InventoryLog::latest('created_at')->limit(3)->get(['log_id', 'changed_fields', 'action_type', 'created_by']);
            $this->info('Recent logs:');
            foreach ($recent as $logItem) {
                $this->line('ID: ' . $logItem->log_id . ', Field: ' . $logItem->changed_fields . ', Action: ' . $logItem->action_type . ', By: ' . $logItem->created_by);
            }

        } catch (\Exception $e) {
            $this->error('Error: ' . $e->getMessage());
            $this->error('File: ' . $e->getFile() . ':' . $e->getLine());
        }
    }
}
