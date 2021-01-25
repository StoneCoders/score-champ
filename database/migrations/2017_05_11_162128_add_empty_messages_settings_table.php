<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddEmptyMessagesSettingsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('settings', function (Blueprint $table) {
            $table->longText('html_empty_bets_open_en');
            $table->longText('html_empty_bets_open_he');
            $table->longText('html_empty_bets_closed_en');
            $table->longText('html_empty_bets_closed_he');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('settings', function (Blueprint $table) {
            $table->dropColumn('html_empty_bets_open_en');
            $table->dropColumn('html_empty_bets_open_he');
            $table->dropColumn('html_empty_bets_closed_en');
            $table->dropColumn('html_empty_bets_closed_he');
        });
    }
}
