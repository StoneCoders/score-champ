<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddGameLinks extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('games', function (Blueprint $table) {
            $table->longText('link_button_text_he');
            $table->longText('link_button_text_en');
            $table->longText('link_video');
            $table->longText('link_text_info_he');
            $table->longText('link_text_info_en');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('games', function (Blueprint $table) {
            $table->dropColumn('link_button_text_he');
            $table->dropColumn('link_button_text_en');
            $table->dropColumn('link_video');
            $table->dropColumn('link_text_info_he');
            $table->dropColumn('link_text_info_en');
        });
    }
}
