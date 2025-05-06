<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddLedgerIdToPromotersBonusTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('promoter_bonus', function (Blueprint $table) {
            $table->string('ledger_record_id')->references('id')->on('loyalty_point_ledger');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('promoters_bonus', function (Blueprint $table) {
            $table->dropColumn('ledger_record_id');
        });
    }
}
