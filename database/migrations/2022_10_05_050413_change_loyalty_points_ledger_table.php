<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class ChangeLoyaltyPointsLedgerTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('loyalty_point_ledger', function (Blueprint $table) {
            $table->unsignedBigInteger('business_id')->references('id')->on('business')->onDelete('cascade');
            $table->uuid('uuid')->nullable(false)->default()->unique();
            $table->string('type', 25);
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
    }
}
