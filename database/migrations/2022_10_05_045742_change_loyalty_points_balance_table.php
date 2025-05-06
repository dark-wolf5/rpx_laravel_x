<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class ChangeLoyaltyPointsBalanceTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('loyalty_point_balances', function (Blueprint $table) {
            $table->dropUnique('loyalty_point_balances_id_unique');
            $table->unsignedBigInteger('business_id')->references('id')->on('business')->onDelete('cascade');
            $table->unsignedBigInteger('from_business')->references('id')->on('business')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('loyalty_point_balances', function (Blueprint $table) {
            $table->dropColumn('from_business');
        });
    }
}
