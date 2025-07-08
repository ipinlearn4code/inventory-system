<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class DeviceAssignment extends Model
{
    protected $primaryKey = 'assignment_id';
    public $timestamps = false; // Using custom created_at/updated_at fields

    protected $fillable = [
        'device_id',
        'pn',
        'branch_code',
        'assigned_date',
        'returned_date',
        'status',
        'notes',
        'created_at',
        'created_by',
        'updated_at',
        'updated_by',
    ];

    protected $casts = [
        'assigned_date' => 'date',
        'returned_date' => 'date',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];

    public function device()
    {
        return $this->belongsTo(Device::class, 'device_id', 'device_id');
    }

    public function user()
    {
        return $this->belongsTo(User::class, 'pn', 'pn');
    }

    public function branch()
    {
        return $this->belongsTo(Branch::class, 'branch_code', 'branch_code');
    }
}
