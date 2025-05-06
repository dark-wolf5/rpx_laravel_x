<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddAccountCompletedColumnInRpxUsersTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('rpx_users', function (Blueprint $table) {
            $table->boolean('account_completed')->nullable(false)->default(false);
            $table->unsignedBigInteger('created_in_business')->nullable()->references('id')->on('business');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('rpx_users', function (Blueprint $table) {
            $table->dropColumn('account_completed');
            $table->dropColumn('created_in_business');
        });
    }
}
