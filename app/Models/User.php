<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class User extends Model
{
    use HasFactory;

    protected $primaryKey = 'pn';
    protected $keyType = 'string';
    public $incrementing = false;
    public $timestamps = false; // No timestamps in users table

    protected $fillable = [
        'pn',
        'name',
        'department_id',
        'position',
    ];

    public function department()
    {
        return $this->belongsTo(Department::class, 'department_id', 'department_id');
    }

    public function auth()
    {
        return $this->hasOne(Auth::class, 'pn', 'pn');
    }

    public function deviceAssignments()
    {
        return $this->hasMany(DeviceAssignment::class, 'pn', 'pn');
    }
}
