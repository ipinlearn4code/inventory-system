<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class DeviceAssignment extends Model
{
    use HasFactory;
    protected $primaryKey = 'assignment_id';
    public $timestamps = false; // Using custom created_at/updated_at fields

    protected $fillable = [
        'device_id',
        'user_id',
        'branch_id',
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
    public function department()
    {
        return $this->belongsTo(Department::class, 'department_id', 'department_id');
    }

    public function user()
    {
        return $this->belongsTo(User::class, 'user_id', 'user_id');
    }

    public function branch()
    {
        return $this->belongsTo(Branch::class, 'branch_id', 'branch_id');
    }

    public function assignmentLetters()
    {
        return $this->hasMany(AssignmentLetter::class, 'assignment_id', 'assignment_id');
    }
}
