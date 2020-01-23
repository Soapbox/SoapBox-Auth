<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateSoapboxStatsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('soapbox_stats', function (Blueprint $table) {
            $table->increments('id');
            $table->integer('soapbox_id')->unsigned();
            $table->index('soapbox_id');
            $table->double('avg_close_ratio_30');
            $table->double('avg_close_ratio_90');
            $table->double('avg_close_ratio_365');
            $table->double('avg_meeting_rating_30');
            $table->double('avg_meeting_rating_90');
            $table->double('avg_meeting_rating_365');
            $table->timestamp('created_at')->useCurrent();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('soapbox_stats');
    }
}
