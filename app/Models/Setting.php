<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Setting extends Model
{
    protected $fillable = [
        'prevent_bet_minutes_before_game',
        'html_rules_he',
        'html_rules_en',
        'html_empty_group_he',
        'html_empty_group_en',
        'reminder_content_he',
        'reminder_content_en',
        'reminder_title_he',
        'reminder_title_en',
        'show_ads',
	      'adsplash_counter',
    ];
}
