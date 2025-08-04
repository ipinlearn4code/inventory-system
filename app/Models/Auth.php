<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Hash;
use Spatie\Permission\Traits\HasRoles;

class Auth extends Model
{
    use HasRoles;

    protected $guard_name = 'web'; // Define the guard for Spatie permissions

    protected $table = 'auth';
    protected $primaryKey = 'pn';
    protected $keyType = 'string';
    public $incrementing = false;
    public $timestamps = false;

    protected $fillable = [
        'pn',
        'password',
        'role',
        'remember_token',
    ];

    protected $hidden = [
        'password',
    ];

    protected $casts = [
        // Remove password hashing from casts since we handle it in seeder
    ];

    public function user()
    {
        return $this->belongsTo(User::class, 'pn', 'pn');
    }

    // Note: Password hashing is handled in the seeder using Hash::make()
    // No need for setPasswordAttribute mutator to avoid double-hashing
}
