<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateIndustryStatsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('industry_stats', function (Blueprint $table) {
            $table->increments('id');
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
        Schema::dropIfExists('industry_stats');
    }
}
