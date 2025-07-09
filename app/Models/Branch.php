<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Branch extends Model
{
    protected $table = 'branch';
    protected $primaryKey = 'branch_id';
    protected $keyType = 'integer';
    public $incrementing = true;
    public $timestamps = false;

    protected $fillable = [
        'branch_code',
        'unit_name',
        'main_branch_id',
    ];

    public function mainBranch()
    {
        return $this->belongsTo(MainBranch::class, 'main_branch_id', 'main_branch_id');
    }

    public function users()
    {
        return $this->hasMany(User::class, 'branch_id', 'branch_id');
    }

    public function deviceAssignments()
    {
        return $this->hasMany(DeviceAssignment::class, 'branch_id', 'branch_id');
    }
}
