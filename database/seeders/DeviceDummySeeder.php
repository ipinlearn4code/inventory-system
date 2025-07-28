<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Device;
use Illuminate\Support\Carbon;

class DeviceDummySeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $brands = ['Dell', 'HP', 'Lenovo', 'Asus', 'Acer', 'MSI', 'Toshiba', 'Apple', 'Samsung', 'Fujitsu'];
        $devices = [];

        for ($i = 1; $i <= 50; $i++) {
            $devices[] = [
                'brand' => $brands[$i % count($brands)],
                'brand_name' => 'Model ' . $i,
                'serial_number' => 'SN' . str_pad($i, 6, '0', STR_PAD_LEFT),
                'asset_code' => 'ASTD' . str_pad($i, 3, '0', STR_PAD_LEFT),
                'bribox_id' => 'B' . rand(1, 3),
                'condition' => 'Baik',
                'status' => 'Tidak Digunakan',
                'spec1' => 'Intel i5 Gen' . rand(8, 12),
                'spec2' => rand(4, 32) . 'GB RAM',
                'spec3' => rand(128, 1024) . 'GB SSD',
                'dev_date' => Carbon::now()->subDays(rand(30, 900))->format('Y-m-d'),
                'created_at' => Carbon::now(),
                'created_by' => 'SUPER01',
            ];
        }

        Device::insert($devices);
    }
}
