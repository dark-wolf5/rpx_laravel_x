<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class SmsGroup extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('sms_group', function (Blueprint $table) {
            $table->id()->references('group_id')->on('sms');
            $table->uuid('uuid')->default(DB::raw('(UUID())'));
            $table->text('body', 320);
            $table->unsignedBigInteger('from_id')->references('id')->on('business');
            $table->unsignedFloat('price', 8, 5)->nullable()->default(0);
            $table->unsignedInteger('total_sent')->nullable()->default(0);
            $table->unsignedInteger('total')->nullable()->default(0);
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
        Schema::dropIfExists('sms_group');
    }
}
