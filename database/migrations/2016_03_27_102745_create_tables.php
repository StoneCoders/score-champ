<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateTables extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('top_score_players', function (Blueprint $table) {
            $table->increments('id');
            $table->string('name');
            $table->string('name_he');
            $table->integer('goals');
            $table->enum('class', ['a','b','other']);
            $table->timestamps();
        });


        Schema::create('top_score_bets', function (Blueprint $table) {
            $table->integer('user_id')->unsigned();
            $table->foreign('user_id')->references('id')->on('users')->onDelete('cascade');

            $table->integer('top_score_player_id')->unsigned();
            $table->foreign('top_score_player_id')->references('id')->on('top_score_players')->onDelete('cascade');
            $table->integer('points')->nullable();
            $table->timestamps();
        });

        Schema::create('winning_teams', function (Blueprint $table) {
            $table->increments('id');
            $table->string('name');
            $table->string('name_he');
            $table->string('team_flag')->default('');
            $table->string('team_color1')->default('');
            $table->string('team_color2')->default('');
            $table->boolean('isInGame')->default(1);
            $table->enum('class', ['a','b','c']);
            $table->timestamps();
        });

        Schema::create('winning_team_bets', function (Blueprint $table) {
            $table->integer('user_id')->unsigned();
            $table->foreign('user_id')->references('id')->on('users')->onDelete('cascade');

            $table->integer('winning_team_id')->unsigned();
            $table->foreign('winning_team_id')->references('id')->on('winning_teams')->onDelete('cascade');
            $table->integer('points')->nullable();
            $table->timestamps();
        });

        Schema::create('games', function (Blueprint $table) {
            $table->increments('id');
            $table->integer('team_a_id')->nullable();
            $table->integer('team_b_id')->nullable();
            $table->dateTime('game_date');
            $table->integer('team_a_score')->nullable();
            $table->integer('team_b_score')->nullable();
            $table->enum('stage', ['a','b']);

            $table->boolean('isFinished');
            $table->boolean('hide');
            $table->timestamps();
        });


        Schema::create('users_friends', function (Blueprint $table) {
            $table->integer('user_id1')->unsigned();
            $table->foreign('user_id1')->references('id')->on('users')->onDelete('cascade');

            $table->integer('user_id2')->unsigned();
            $table->foreign('user_id2')->references('id')->on('users')->onDelete('cascade');
        });

        Schema::create('game_bets', function (Blueprint $table) {
            $table->increments('id');
            $table->integer('user_id')->unsigned();
            $table->foreign('user_id')->references('id')->on('users')->onDelete('cascade');

            $table->integer('game_id')->unsigned();
            $table->foreign('game_id')->references('id')->on('games')->onDelete('cascade');

            $table->integer('team_a_score');
            $table->integer('team_b_score');
            $table->boolean('is_won')->nullable();
            $table->enum('won_type', ['regular','bull'])->nullable();
            $table->integer('points')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::drop('game_bets');
        Schema::drop('users_friends');
        Schema::drop('games');
        Schema::drop('winning_team_bets');
        Schema::drop('winning_teams');
        Schema::drop('top_score_bets');
        Schema::drop('top_score_players');
    }
}
