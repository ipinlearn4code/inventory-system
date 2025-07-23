<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class InventoryLog extends Model
{
    protected $primaryKey = 'log_id';
    public $timestamps = false; // Using custom created_at field

    protected $fillable = [
        'changed_fields',
        'action_type', // enum: CREATE, UPDATE, DELETE
        'old_value',
        'new_value',
        'user_affected',
        'created_at',
        'created_by',
    ];

    protected $casts = [
        'created_at' => 'datetime',
    ];

    public function userAffected()
    {
        return $this->belongsTo(User::class, 'user_affected', 'pn');
    }

    public function createdBy()
    {
        return $this->belongsTo(User::class, 'created_by', 'pn');
    }
}
