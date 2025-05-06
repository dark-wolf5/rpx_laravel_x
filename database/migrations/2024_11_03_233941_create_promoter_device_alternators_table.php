<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreatePromoterDeviceAlternatorsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('promoter_device_alternators', function (Blueprint $table) {
            $table->id();
            $table->float('loc_x', 8, 6)->nullable();
            $table->float('loc_y', 8, 6)->nullable();
            $table->string('business_list', 2500)->nullable();
            $table->string('device_id', 65)->unique()->nullable();
            $table->string('device_ip', 65)->unique()->nullable();
            $table->unsignedBigInteger('user_id')->references('id')->on('users');
            $table->float('lp_amount')->nullable(false)->default(0);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('promoter_device_alternators');
    }
}
