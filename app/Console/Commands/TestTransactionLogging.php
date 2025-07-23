<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Services\DeviceService;
use App\Models\InventoryLog;
use App\Models\Device;
use Illuminate\Support\Facades\DB;

class TestTransactionLogging extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'test:transaction-logging';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Test transaction-based inventory logging';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $this->info('Testing transaction-based inventory logging...');

        try {
            // Test successful transaction
            $this->info('1. Testing successful device creation with logging...');
            $deviceService = app(DeviceService::class);
            
            $initialLogCount = InventoryLog::count();
            $initialDeviceCount = Device::count();
            
            $result = $deviceService->createDevice([
                'brand' => 'TEST',
                'brand_name' => 'Test Device',
                'serial_number' => 'TEST-' . time(),
                'asset_code' => 'TST-' . time(),
                'bribox_id' => 'I5',
                'condition' => 'Baik',
                'status' => 'Tidak Digunakan',
            ]);
            
            $finalLogCount = InventoryLog::count();
            $finalDeviceCount = Device::count();
            
            $this->info('Initial logs: ' . $initialLogCount . ', Final logs: ' . $finalLogCount);
            $this->info('Initial devices: ' . $initialDeviceCount . ', Final devices: ' . $finalDeviceCount);
            $this->info('Device created with ID: ' . $result['deviceId']);
            
            if ($finalLogCount > $initialLogCount && $finalDeviceCount > $initialDeviceCount) {
                $this->info('✅ SUCCESS: Both device and log were created');
            } else {
                $this->error('❌ FAIL: Transaction did not complete properly');
            }

            // Test transaction rollback by forcing a logging error
            $this->info('2. Testing transaction rollback with forced logging error...');
            
            $preTestLogCount = InventoryLog::count();
            $preTestDeviceCount = Device::count();
            
            try {
                DB::transaction(function () {
                    // Create a device
                    $device = Device::create([
                        'brand' => 'ROLLBACK',
                        'brand_name' => 'Rollback Test',
                        'serial_number' => 'RB-' . time(),
                        'asset_code' => 'RB-' . time(),
                        'bribox_id' => 'I5',
                        'condition' => 'Baik',
                        'status' => 'Tidak Digunakan',
                        'created_by' => 'testuser',
                        'created_at' => now(),
                    ]);
                    
                    $this->info('Device temporarily created, now forcing log error...');
                    
                    // Force a logging error by using an invalid field length
                    InventoryLog::create([
                        'changed_fields' => 'devices',
                        'action_type' => 'CREATE',
                        'old_value' => null,
                        'new_value' => json_encode($device->toArray()),
                        'user_affected' => null,
                        'created_by' => 'this_is_too_long_to_fit_in_8_chars', // This will fail
                        'created_at' => now(),
                    ]);
                });
            } catch (\Exception $e) {
                $this->info('Expected error caught: ' . substr($e->getMessage(), 0, 100) . '...');
            }
            
            $postTestLogCount = InventoryLog::count();
            $postTestDeviceCount = Device::count();
            
            $this->info('Pre-test logs: ' . $preTestLogCount . ', Post-test logs: ' . $postTestLogCount);
            $this->info('Pre-test devices: ' . $preTestDeviceCount . ', Post-test devices: ' . $postTestDeviceCount);
            
            if ($postTestLogCount === $preTestLogCount && $postTestDeviceCount === $preTestDeviceCount) {
                $this->info('✅ SUCCESS: Transaction correctly rolled back - no device or log created');
            } else {
                $this->error('❌ FAIL: Transaction rollback did not work properly');
            }

        } catch (\Exception $e) {
            $this->error('Unexpected error: ' . $e->getMessage());
            $this->error('File: ' . $e->getFile() . ':' . $e->getLine());
        }
    }
}
