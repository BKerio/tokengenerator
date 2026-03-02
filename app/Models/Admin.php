<?php

namespace App\Models;

use MongoDB\Laravel\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Admin extends Authenticatable
{
    use HasFactory, Notifiable, HasApiTokens;

    protected $connection = 'mongodb';
    protected $table = 'admins';

    protected $fillable = [
        'name',
        'username',
        'email',
        'password',
        'profile_image',
        'bio',
        'role', // admin, system_admin, attendance_staff
    ];

    protected $hidden = [
        'password',
        'remember_token',
    ];

    protected $casts = [
        'email_verified_at' => 'datetime',
        'password' => 'hashed',
        'role' => 'string',
    ];
}
