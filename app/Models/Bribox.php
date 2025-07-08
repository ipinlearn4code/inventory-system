<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Bribox extends Model
{
    protected $primaryKey = 'bribox_id';
    protected $keyType = 'string';
    public $incrementing = false;
    public $timestamps = false;

    protected $fillable = [
        'bribox_id',
        'type',
        'category',
    ];

    public function devices()
    {
        return $this->hasMany(Device::class, 'bribox_id', 'bribox_id');
    }
}
