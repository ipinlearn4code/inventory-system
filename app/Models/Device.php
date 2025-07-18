<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Device extends Model
{
    protected $primaryKey = 'device_id';
    public $timestamps = false; // Using custom created_at/updated_at fields

    protected $fillable = [
        'brand_name',
        'serial_number',
        'asset_code',
        'bribox_id',
        'condition',
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

    public function bribox()
    {
        return $this->belongsTo(Bribox::class, 'bribox_id', 'bribox_id');
    }

    public function assignments()
    {
        return $this->hasMany(DeviceAssignment::class, 'device_id', 'device_id');
    }

    public function currentAssignment()
    {
        return $this->hasOne(DeviceAssignment::class, 'device_id', 'device_id')
                    ->whereNull('returned_date');
    }
    
    /**
     * Get the asset code with device type - used for display in dropdowns
     *
     * @return string
     */
    public function getAssetCodeWithTypeAttribute(): string
    {
        $type = $this->bribox ? $this->bribox->type_name : 'Unknown';
        return "{$this->asset_code} - {$type} ({$this->brand_name})";
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
