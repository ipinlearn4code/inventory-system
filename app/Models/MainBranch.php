<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class MainBranch extends Model
{
    protected $table = 'main_branch';
    protected $primaryKey = 'main_branch_id';
    protected $keyType = 'integer';
    public $incrementing = true;
    public $timestamps = false;

    protected $fillable = [
        'main_branch_code',
        'main_branch_name',
    ];

    public function branches()
    {
        return $this->hasMany(Branch::class, 'main_branch_id', 'main_branch_id');
    }
}
