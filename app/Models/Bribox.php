<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Bribox extends Model
{
    use HasFactory;
    protected $primaryKey = 'bribox_id';
    protected $keyType = 'string';
    public $incrementing = false;
    public $timestamps = false;

    protected $fillable = [
        'bribox_id',
        'type',
        'bribox_category_id',
    ];

    public function category()
    {
        return $this->belongsTo(BriboxesCategory::class, 'bribox_category_id', 'bribox_category_id');
    }

    public function devices()
    {
        return $this->hasMany(Device::class, 'bribox_id', 'bribox_id');
    }
}
