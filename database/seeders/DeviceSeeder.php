<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Device;
use Carbon\Carbon;

class DeviceSeeder extends Seeder
{
    public function run(): void
    {
        $devices = [
            [
                'brand' => 'Dell',
                'brand_name' => 'Dell OptiPlex 7090',
                'serial_number' => 'DL001234567',
                'asset_code' => 'AST001',
                'bribox_id' => 'A1',
                'condition' => 'Baik',
                'spec1' => 'Intel i5-10400',
                'spec2' => '8GB RAM',
                'spec3' => '256GB SSD',
                'dev_date' => '2023-01-15',
                'created_at' => Carbon::now(),
                'created_by' => 'ADMIN01',
            ],
            [
                'brand' => 'HP',
                'brand_name' => 'HP EliteBook 840',
                'serial_number' => 'HP001234567',
                'asset_code' => 'AST002',
                'bribox_id' => 'A1',
                'condition' => 'Baik',
                'spec1' => 'Intel i7-11700',
                'spec2' => '16GB RAM',
                'spec3' => '512GB SSD',
                'dev_date' => '2023-02-20',
                'created_at' => Carbon::now(),
                'created_by' => 'ADMIN01',
            ],
        ];

        foreach ($devices as $device) {
            Device::create($device);
        }
    }
}
