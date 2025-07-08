<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Department extends Model
{
    protected $primaryKey = 'department_id';
    protected $keyType = 'string';
    public $incrementing = false;
    public $timestamps = false;

    protected $fillable = [
        'department_id',
        'name',
    ];

    public function users()
    {
        return $this->hasMany(User::class, 'department_id', 'department_id');
    }
}
