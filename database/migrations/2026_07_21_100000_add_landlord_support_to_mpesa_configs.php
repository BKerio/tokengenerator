<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Extends mpesa_configs so landlords can have Daraja payment API
 * credentials (same shape as vendor configs), keyed by landlord_id.
 */
return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        $connection = Schema::connection('mongodb');

        if (!$connection->hasTable('mpesa_configs')) {
            $connection->create('mpesa_configs', function (Blueprint $collection) {
                $collection->string('vendor_id')->nullable()->index();
                $collection->string('landlord_id')->nullable()->index();
                $collection->string('consumer_key')->nullable();
                $collection->string('consumer_secret')->nullable();
                $collection->string('passkey')->nullable();
                $collection->string('shortcode')->nullable();
                $collection->string('till_no')->nullable();
                $collection->string('env')->nullable(); // sandbox | live
                $collection->string('callback_url')->nullable();
                $collection->string('transaction_type')->nullable(); // CustomerPayBillOnline | CustomerBuyGoodsOnline
                $collection->timestamps();
            });

            return;
        }

        $connection->table('mpesa_configs', function (Blueprint $collection) {
            $collection->string('landlord_id')->nullable()->index();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        if (!Schema::connection('mongodb')->hasTable('mpesa_configs')) {
            return;
        }

        // Only drop landlord_id — do not destroy vendor configs.
        Schema::connection('mongodb')->table('mpesa_configs', function (Blueprint $collection) {
            $collection->dropColumn('landlord_id');
        });
    }
};
