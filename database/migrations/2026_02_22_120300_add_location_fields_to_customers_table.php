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
        Schema::connection('mongodb')->table('customers', function (Blueprint $collection) {
            $collection->unsignedBigInteger('county_id')->nullable();
            $collection->unsignedBigInteger('constituency_id')->nullable();
            $collection->unsignedBigInteger('ward_id')->nullable();
            
            // Indexes for faster filtering
            $collection->index('county_id');
            $collection->index('constituency_id');
            $collection->index('ward_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::connection('mongodb')->table('customers', function (Blueprint $collection) {
            $collection->dropColumn(['county_id', 'constituency_id', 'ward_id']);
        });
    }
};
