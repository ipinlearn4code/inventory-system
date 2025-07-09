<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class AssignmentLetter extends Model
{
    protected $primaryKey = 'letter_id';
    public $timestamps = false; // Using custom created_at/updated_at fields

    protected $fillable = [
        'assignment_id',
        'letter_type',
        'letter_number',
        'letter_date',
        'approver_id',
        'file_path',
        'created_at',
        'created_by',
        'updated_at',
        'updated_by',
    ];

    protected $casts = [
        'letter_date' => 'date',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];

    public function assignment()
    {
        return $this->belongsTo(DeviceAssignment::class, 'assignment_id', 'assignment_id');
    }

    public function approver()
    {
        return $this->belongsTo(User::class, 'approver_id', 'user_id');
    }
}
