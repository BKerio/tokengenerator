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
        // Counties Table (Seeder calls it 'location')
        if (!Schema::connection('mongodb')->hasTable('location')) {
            Schema::connection('mongodb')->create('location', function (Blueprint $collection) {
                $collection->unsignedBigInteger('id')->index();
                $collection->string('description');
                $collection->integer('status')->default(1);
                $collection->timestamps();
            });
        }

        // Constituencies Table
        if (!Schema::connection('mongodb')->hasTable('constituencies')) {
            Schema::connection('mongodb')->create('constituencies', function (Blueprint $collection) {
                $collection->unsignedBigInteger('id')->index();
                $collection->unsignedBigInteger('location_id')->index(); // County Reference
                $collection->string('description');
                $collection->integer('status')->default(1);
                $collection->timestamps();
            });
        }

        // Wards Table (Seeder calls it 'location_area')
        if (!Schema::connection('mongodb')->hasTable('location_area')) {
            Schema::connection('mongodb')->create('location_area', function (Blueprint $collection) {
                $collection->unsignedBigInteger('id')->index();
                $collection->unsignedBigInteger('location_id')->index(); // County Reference
                $collection->unsignedBigInteger('constituency_id')->index();
                $collection->string('description');
                $collection->integer('status')->default(1);
                $collection->timestamps();
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('location_area');
        Schema::dropIfExists('constituencies');
        Schema::dropIfExists('location');
    }
};
