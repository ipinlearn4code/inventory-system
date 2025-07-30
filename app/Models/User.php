<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Laravel\Sanctum\HasApiTokens;

class User extends Authenticatable
{
    use HasFactory, HasApiTokens;

    protected $primaryKey = 'user_id';
    protected $keyType = 'integer';
    public $incrementing = true;
    public $timestamps = false; // No timestamps in users table

    protected $fillable = [
        'pn',
        'name',
        'department_id',
        'branch_id',
        'position',
    ];

    // Default eager loading for performance
    protected $with = ['department', 'branch'];

    public function department(): BelongsTo
    {
        return $this->belongsTo(Department::class, 'department_id', 'department_id');
    }

    public function branch(): BelongsTo
    {
        return $this->belongsTo(Branch::class, 'branch_id', 'branch_id');
    }

    public function auth(): HasOne
    {
        return $this->hasOne(Auth::class, 'pn', 'pn');
    }

    public function deviceAssignments(): HasMany
    {
        return $this->hasMany(DeviceAssignment::class, 'user_id', 'user_id');
    }

    public function currentDeviceAssignment(): HasMany
    {
        return $this->hasMany(DeviceAssignment::class, 'user_id', 'user_id')
                    ->whereNull('returned_date');
    }

    public function assignmentLetters(): HasMany
    {
        return $this->hasMany(AssignmentLetter::class, 'approver_id', 'user_id');
    }
}
