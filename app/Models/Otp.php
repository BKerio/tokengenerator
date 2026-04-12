<?php

namespace App\Models;

use MongoDB\Laravel\Eloquent\Model as DocumentModel;

class Otp extends DocumentModel
{
    protected $connection = 'mongodb';
    protected $collection = 'otps';

    protected $fillable = [
        'phone',
        'email',
        'otp',
        'expires_at',
    ];

    protected $casts = [
        'expires_at' => 'datetime',
    ];
}
