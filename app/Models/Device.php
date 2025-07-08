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
}
