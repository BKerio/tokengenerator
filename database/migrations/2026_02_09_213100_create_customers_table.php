<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::connection('mongodb')->create('customers', function (Blueprint $collection) {
            $collection->index('vendor_id');
            $collection->index('meter_id');
            $collection->string('name');
            $collection->string('phone');
            $collection->string('email')->nullable();
            $collection->string('address')->nullable();
            $collection->string('status')->default('active');
            $collection->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::connection('mongodb')->dropIfExists('customers');
    }
};
