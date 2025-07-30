<?php

namespace App\Services;

use App\Models\Device;
use App\Models\Bribox;
use App\Models\BriboxesCategory;
use Illuminate\Support\Facades\Cache;

class DropdownOptionsService
{
    /**
     * Get cached device brands
     */
    public static function getDeviceBrands(): array
    {
        return Cache::remember('device_brands', 3600, function () {
            return Device::distinct()
                ->whereNotNull('brand')
                ->pluck('brand', 'brand')
                ->toArray();
        });
    }

    /**
     * Get cached device brand names
     */
    public static function getDeviceBrandNames(): array
    {
        return Cache::remember('device_brand_names', 3600, function () {
            return Device::distinct()
                ->whereNotNull('brand_name')
                ->pluck('brand_name', 'brand_name')
                ->toArray();
        });
    }

    /**
     * Get cached bribox categories
     */
    public static function getBriboxCategories(): array
    {
        return Cache::remember('bribox_categories', 3600, function () {
            return BriboxesCategory::all()
                ->pluck('category_name', 'bribox_category_id')
                ->toArray();
        });
    }

    /**
     * Get cached bribox types
     */
    public static function getBriboxTypes(): array
    {
        return Cache::remember('bribox_types', 3600, function () {
            return Bribox::all()
                ->pluck('type', 'bribox_id')
                ->toArray();
        });
    }

    /**
     * Get cached device conditions
     */
    public static function getDeviceConditions(): array
    {
        return Cache::remember('device_conditions', 86400, function () { // Cache for 24 hours as this rarely changes
            return [
                'Baik' => 'Baik',
                'Rusak' => 'Rusak',
                'Perlu Pengecekan' => 'Perlu Pengecekan',
            ];
        });
    }

    /**
     * Clear all cached dropdown options
     */
    public static function clearCache(): void
    {
        Cache::forget('device_brands');
        Cache::forget('device_brand_names');
        Cache::forget('bribox_categories');
        Cache::forget('bribox_types');
        Cache::forget('device_conditions');
    }
}
