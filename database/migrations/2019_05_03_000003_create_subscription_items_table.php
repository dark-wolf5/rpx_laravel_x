<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateSubscriptionItemsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('subscription_items', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('subscription_id')->references('id')->on('subscriptions')->onDelete('cascade')->nullable();
            $table->string('stripe_id')->nullable();
            $table->string('stripe_product')->nullable();
            $table->string('stripe_price')->nullable(false);
            $table->integer('quantity')->nullable();
            $table->timestamps();
            $table->softDeletes();

            $table->unique(['subscription_id', 'stripe_price']);
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('subscription_items');
    }
}
