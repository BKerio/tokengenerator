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
        Schema::connection('mongodb')->create('meters', function (Blueprint $collection) {
            $collection->index('vendor_id');
            $collection->unique('meter_number');
            $collection->string('type'); // water, electricity, etc.
            $collection->decimal('initial_reading', 15, 2)->default(0);
            $collection->decimal('price_per_unit', 15, 2)->default(0);
            $collection->string('status')->default('active'); // active, inactive, maintenance
            $collection->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::connection('mongodb')->dropIfExists('meters');
    }
};
