<?php

namespace App\Models;

use MongoDB\Laravel\Eloquent\Model as DocumentModel;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Customer extends DocumentModel
{
    use HasFactory;

    protected $connection = 'mongodb';
    protected $collection = 'customers';

    protected $fillable = [
        'vendor_id',
        'meter_id',
        'name',
        'phone',
        'email',
        'address',
        'county_id',
        'constituency_id',
        'ward_id',
        'status',
    ];

    public function vendor()
    {
        return $this->belongsTo(Vendor::class);
    }

    public function meter()
    {
        return $this->belongsTo(Meter::class);
    }
}
