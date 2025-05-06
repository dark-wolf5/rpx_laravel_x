<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateLoyaltyPointsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('loyalty_point_balances', function (Blueprint $table) {
            $table->unsignedBigInteger('id')->references('id')->on('users')->onDelete('cascade')->id()->unique();
            $table->float('balance')->nullable(false)->default(0);
            $table->float('reset_balance')->nullable(false)->default(0);
            $table->float('loyalty_point_dollar_percent_value', 6, 2)->nullable(false)->default(0);
            $table->timestamp('end_of_month', $precision = 0)->nullable(true);
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
        Schema::dropIfExists('loyalty_point_balances');
    }
}
