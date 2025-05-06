<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class ChangeSystemSmsToId extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('system_sms', function (Blueprint $table) {
            $table->unsignedBigInteger('to_id')->references('id')->on('users')->nullable()->change();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('system_sms', function (Blueprint $table) {
            $table->unsignedBigInteger('to_id')->references('id')->on('users')->change();
        });
    }
}
