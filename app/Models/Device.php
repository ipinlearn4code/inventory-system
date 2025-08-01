<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Support\Facades\Cache;

class Device extends Model
{
    use HasFactory;
    protected $primaryKey = 'device_id';
    public $timestamps = false; // Using custom created_at/updated_at fields

    protected $fillable = [
        'brand',
        'brand_name',
        'serial_number',
        'asset_code',
        'bribox_id',
        'condition',
        'status',
        'spec1',
        'spec2',
        'spec3',
        'spec4',
        'spec5',
        'dev_date',
        'created_at',
        'created_by',
        'updated_at',
        'updated_by',
    ];

    protected $casts = [
        'dev_date' => 'date',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];

    // Default eager loading for performance
    protected $with = ['bribox'];

    public function bribox(): BelongsTo
    {
        return $this->belongsTo(Bribox::class, 'bribox_id', 'bribox_id');
    }

    public function assignments(): HasMany
    {
        return $this->hasMany(DeviceAssignment::class, 'device_id', 'device_id');
    }

    public function currentAssignment(): HasOne
    {
        return $this->hasOne(DeviceAssignment::class, 'device_id', 'device_id')
                    ->whereNull('returned_date');
    }
    
    /**
     * Get the asset code with device type - used for display in dropdowns
     * This method is cached for performance
     *
     * @return string
     */
    public function getAssetCodeWithTypeAttribute(): string
    {
        return Cache::remember(
            "device_asset_code_with_type_{$this->device_id}",
            3600, // 1 hour
            function () {
                $type = $this->bribox ? $this->bribox->type_name : 'Unknown';
                return "{$this->serial_number} - {$type} ({$this->brand} {$this->brand_name}) {$this->asset_code}";
            }
        );
    }
    
    /**
     * Scope a query to only include available devices (not currently assigned)
     *
     * @param \Illuminate\Database\Eloquent\Builder $query
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function scopeAvailable($query)
    {
        return $query->whereDoesntHave('currentAssignment');
    }
    
    /**
     * Get the QR code data for this device
     *
     * @return string
     */
    public function getQRCodeData(): string
    {
        return "briven-{$this->asset_code}";
    }
    
    /**
     * Get the QR code sticker URL for this device
     *
     * @return string
     */
    public function getQRCodeStickerUrl(): string
    {
        return route('qr-code.sticker', $this->device_id);
    }
    
    /**
     * Get the QR code image URL for this device
     *
     * @return string
     */
    public function getQRCodeImageUrl(): string
    {
        return route('qr-code.generate', $this->asset_code);
    }
}
