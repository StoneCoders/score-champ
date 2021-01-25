<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class League extends Model
{
    use SoftDeletes;

    protected $fillable = [ 
      'is_active',
      'name_he',
      'name_en',
      'is_default',
      'top_player_finished',
      'end_bet_top_score_player',
      'allow_bet_top_score_player',
      'end_bet_winning_team',
      'allow_bet_winning_team',
      'winning_team_finished',
      'current_match_week_id',
      'WINNING_TEAM_ID',
      'WINNING_TOP_SCORE_PLAYER',
      'is_turnir',
      'html_empty_bets_open_en',
      'html_empty_bets_open_he',
      'html_empty_bets_closed_en',
      'html_empty_bets_closed_he',
      'COW_PTS_LEVEL_A',
      'COW_PTS_LEVEL_B',
      'BULL_PTS_LEVEL_A',
      'BULL_PTS_LEVEL_B',
      'WINNING_TEAM_PTS_CALSS_A',
      'WINNING_TEAM_PTS_CALSS_B',
      'WINNING_TEAM_PTS_CALSS_C',
      'TOP_SCORER_PTS_CALSS_A',
      'TOP_SCORER_PTS_CALSS_B',
      'TOP_SCORER_PTS_OTHER',
	    'html_rules_en',
	    'html_rules_he',
	    'global_rank_title_en',
	    'global_rank_title_he',
	    'week_rank_title_en',
	    'week_rank_title_he',
      'winning_team_title_en',
      'winning_team_title_he',
	    'top_player_title_en',
	    'top_player_title_he',
	    'show_league_board',
	    'show_global_rank',
	    'show_week_rank',
	    'score_range'
    ];

    public function match_week()
    {
        return $this->hasMany(MatchWeek::class);
    }

    public function player()
    {
        return $this->hasMany(TopScorePlayer::class);
    }

    public function team()
    {
        return $this->hasMany(WinningTeam::class);
    }
}
