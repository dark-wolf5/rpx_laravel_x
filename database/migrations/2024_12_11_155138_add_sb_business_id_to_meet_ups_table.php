<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddSbBusinessIdToMeetUpsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('meet_ups', function (Blueprint $table) {
            $table->string('business_id', 65)->nullable()->change();
            $table->dropColumn( 'friend_id');
            $table->text( 'friend_list');
            $table->unsignedBigInteger('business_id_sb')->references('id')->on('businesses')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('meet_ups', function (Blueprint $table) {
            $table->dropColumn( 'business_id_sb');
            $table->string('business_id', 65)->change();
        });
    }
}
