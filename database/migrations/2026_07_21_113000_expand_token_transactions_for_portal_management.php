<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::connection('mongodb')->table('token_transactions', function (Blueprint $collection) {
            $collection->string('landlord_id')->nullable()->index();
            $collection->string('owner_type')->nullable()->index();
            $collection->string('owner_id')->nullable()->index();
            $collection->string('actor_user_id')->nullable()->index();
            $collection->decimal('units', 12, 3)->nullable();
            $collection->string('token_type')->nullable()->index();
            $collection->string('vend_mode')->nullable();
            $collection->integer('control_index')->nullable();
            $collection->integer('control_value')->nullable();
            $collection->string('transaction_id')->nullable()->index();
            $collection->timestamp('transaction_time')->nullable();
            $collection->boolean('send_sms')->default(false);
            $collection->boolean('sms_sent')->default(false);
            $collection->string('phone')->nullable();
        });
    }

    public function down(): void
    {
        Schema::connection('mongodb')->table('token_transactions', function (Blueprint $collection) {
            $collection->dropColumn([
                'landlord_id',
                'owner_type',
                'owner_id',
                'actor_user_id',
                'units',
                'token_type',
                'vend_mode',
                'control_index',
                'control_value',
                'transaction_id',
                'transaction_time',
                'send_sms',
                'sms_sent',
                'phone',
            ]);
        });
    }
};
