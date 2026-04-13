<?php

namespace App\Models;

use MongoDB\Laravel\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class ContactEnquiry extends Model
{
    use HasFactory;

    protected $connection = 'mongodb';
    protected $collection = 'contact_enquiries';

    protected $fillable = [
        'first_name',
        'last_name',
        'email',
        'phone',
        'city',
        'premises_type',
        'residence_type',
        'meter_type',
        'main_meter_type',
        'message',
        'status',
    ];

    protected $casts = [
        'meter_type' => 'array',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];
}
