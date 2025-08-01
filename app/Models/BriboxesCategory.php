<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class BriboxesCategory extends Model
{
    use HasFactory;
    protected $table = 'briboxes_category';
    protected $primaryKey = 'bribox_category_id';
    protected $keyType = 'integer';
    public $incrementing = true;
    public $timestamps = false;

    protected $fillable = [
        'category_name',
    ];

    public function briboxes()
    {
        return $this->hasMany(Bribox::class, 'bribox_category_id', 'bribox_category_id');
    }
}
