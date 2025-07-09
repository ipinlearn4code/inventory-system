<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\BriboxesCategory;

class BriboxesCategorySeeder extends Seeder
{
    public function run(): void
    {
        $categories = [
            ['category_name' => 'PC'],
            ['category_name' => 'LAPTOP'],
            ['category_name' => 'TABLET'],
            ['category_name' => 'PRINTER'],
            ['category_name' => 'SCANNER'],
            ['category_name' => 'STORAGE'],
            ['category_name' => 'UPS'],
            ['category_name' => 'PANEL'],
            ['category_name' => 'NETWORK'],
            ['category_name' => 'INFRASTRUCTURE'],
        ];

        foreach ($categories as $category) {
            BriboxesCategory::create($category);
        }
    }
}
