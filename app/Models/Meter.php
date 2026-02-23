<?php

namespace App\Models;

use MongoDB\Laravel\Eloquent\Model as DocumentModel;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Meter extends DocumentModel
{
    use HasFactory;

    protected $connection = 'mongodb';
    protected $collection = 'meters';

    protected $fillable = [
        'vendor_id',
        'meter_number',
        'type',
        'initial_reading',
        'price_per_unit',
        'status',
    ];

    public function vendor()
    {
        return $this->belongsTo(Vendor::class);
    }

    public function customers()
    {
        return $this->hasMany(Customer::class);
    }
}
