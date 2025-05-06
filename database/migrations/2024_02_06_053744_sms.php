<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class Sms extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('sms', function (Blueprint $table) {
            $table->id();
            $table->uuid('uuid')->default(DB::raw('(UUID())'));
            $table->string('to_phone', 35);
            $table->boolean('sent')->default(false);
            $table->unsignedFloat('price',8, 5)->nullable()->default(0);;
            $table->unsignedBigInteger('to_id')->references('id')->on('users');
            $table->unsignedBigInteger('from_id')->references('id')->on('business');
            $table->unsignedBigInteger('group_id')->references('id')->on('sms_group');
            $table->timestamps();
            $table->softDeletes();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('sms');
    }
}
