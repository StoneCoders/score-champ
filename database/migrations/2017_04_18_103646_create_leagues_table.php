<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateLeaguesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('leagues', function (Blueprint $table) {
            $table->increments('id');
            $table->boolean('is_active');
            $table->boolean('is_default');
            $table->boolean('is_turnir')->default(FALSE);
            $table->string('name_he');
            $table->string('name_en');
            $table->boolean('top_player_finished')->default(FALSE);
            $table->boolean('winning_team_finished')->default(FALSE);
            $table->dateTime('end_bet_top_score_player');
            $table->boolean('allow_bet_top_score_player');
            $table->boolean('allow_bet_winning_team');
            $table->integer('current_match_week_id')->default(1);
            $table->dateTime('end_bet_winning_team');
            $table->integer('WINNING_TEAM_ID')->nullable();
            $table->integer('WINNING_TOP_SCORE_PLAYER')->nullable();

            $table->integer('COW_PTS_LEVEL_A')->nullable();
            $table->integer('COW_PTS_LEVEL_B')->nullable();
            $table->integer('BULL_PTS_LEVEL_A')->nullable();
            $table->integer('BULL_PTS_LEVEL_B')->nullable();
            $table->integer('WINNING_TEAM_PTS_CALSS_A')->nullable();
            $table->integer('WINNING_TEAM_PTS_CALSS_B')->nullable();
            $table->integer('WINNING_TEAM_PTS_CALSS_C')->nullable();
            $table->integer('TOP_SCORER_PTS_CALSS_A')->nullable();
            $table->integer('TOP_SCORER_PTS_CALSS_B')->nullable();
            $table->integer('TOP_SCORER_PTS_OTHER')->nullable();
            $table->integer('api_id')->nullable();

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
        Schema::drop('leagues');
    }
}
