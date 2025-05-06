<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreatePromoterBonusTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('promoter_bonus', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('business_id')->references('id')->on('business');
            $table->float('lp_amount')->references('lp_amount')->on('promoter_device_alternators');
            $table->timestamps();
            $table->boolean('redeemed')->default(false);
            $table->unsignedBigInteger('user_id')->references('id')->on('users');
            $table->unsignedBigInteger('promoter_id')->references('id')->on('users');
            $table->string('device_id')->references('device_id')->on('promoter_device_alternators');
            $table->string('device_ip')->references('device_ip')->on('promoter_device_alternators');
            $table->timestamp('expires_at');
            $table->string('day');
            $table->string('time_range_1');
            $table->string('time_range_2');
            $table->string('time_range_3');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('promoter_bonus');
    }
}
