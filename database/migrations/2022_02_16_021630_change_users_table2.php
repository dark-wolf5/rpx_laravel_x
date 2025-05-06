<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class ChangeUsersTable2 extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        if (Schema::hasColumn('users', 'city'))
        {
            Schema::table('users', function (Blueprint $table) {
                $table->string('city', 255)->nullable()->default(config('rpx.my_city'))->change();
                $table->string('country', 255)->nullable()->default(config('rpx.my_country'))->change();
                $table->string('line1', 255)->nullable()->default(config('rpx.my_line_1'))->change();
                $table->string('postal_code', 255)->nullable()->default(config('rpx.my_zip_code'))->change();
                $table->string('state', 255)->nullable()->default(config('rpx.my_state'))->change();
            });
        }
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('users', function (Blueprint $table) {
        });
    }
}
