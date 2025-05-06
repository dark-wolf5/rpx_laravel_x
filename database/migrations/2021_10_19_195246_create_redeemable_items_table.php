<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateRedeemableItemsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('redeemable_items', function (Blueprint $table) {
            $table->id();
            $table->uuid('uuid')->nullable(false)->default()->unique();

            $table->unsignedBigInteger('business_id')->references('id')->on('users')->onDelete('cascade');
            $table->unsignedBigInteger('redeemer_id')->references('id')->on('users')->nullable()->default(null);
            $table->unsignedBigInteger('reward_id')->references('id')->on('rewards')->nullable()->default(null);

            $table->float('amount')->nullable(false)->default(0);
            $table->float('total_spent')->nullable(false)->default(0);
            $table->float('dollar_value')->nullable(false)->default(0);
            $table->float('loyalty_point_dollar_percent_value')->nullable(false)->default(0);
            $table->boolean('redeemed')->nullable(false)->default(false);
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
        Schema::dropIfExists('redeemable_items');
    }
}
