<?php

namespace App\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Model;

class Game extends Model
{
    protected $fillable = [
        'team_a_id',
        'team_b_id',
        'game_date',
        'team_a_score',
        'team_b_score',
        'stage',
        'hide',
        'show_in_date',
        'isFinished',
        'match_week_id',
        'link_button_text_he',
        'link_button_text_en',
        'link_video',
        'link_text_info_he',
        'link_text_info_en',
        'api_id'
    ];

    public function team_a()
    {
        return $this->belongsTo(WinningTeam::class, 'team_a_id');
    }

    public function team_b()
    {
        return $this->belongsTo(WinningTeam::class, 'team_b_id');
    }

    public function bets()
    {
        return $this->hasMany(GameBets::class);
    }

    public function match_week()
    {
        return $this->belongsTo(MatchWeek::class);
    }

    public function getIsGameDatePassedAttribute() {
        $gameStart = Carbon::parse($this->game_date)->subMinutes(request()->get('timezone_offset') ?: -120);
        $current_time = \Carbon\Carbon::now()->subMinutes(request()->get('timezone_offset') ?: -120);
        
        return $gameStart < $current_time;
    }
}
