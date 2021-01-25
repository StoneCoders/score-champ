<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;

class User extends Model
{
    protected $fillable = [
        'first_name',
        'last_name',
        'gender',
        'email',
	      'password',
        'lang',
        'isPushActive',
        'isPushReminderActive',
        'facebook_id',
	      'image_url'
    ];

    public function score_player() {
        return $this->belongsToMany(TopScorePlayer::class, 'top_score_bets', 'user_id', 'top_score_player_id');
    }

    public function winning_team() {
        return $this->belongsToMany(WinningTeam::class, 'winning_team_bets', 'user_id', 'winning_team_id');
    }

    public function winning_team_bet() {
        return $this->belongsToMany(WinningTeamBet::class, 'winning_team_bets');
    }

    public function game_bets() {
        $relation = $this->belongsToMany(Game::class, 'game_bets', 'user_id', 'game_id');
        if(!$relation)
            return $relation;

        return $relation->withPivot('id', 'team_a_score', 'team_b_score', 'is_won', 'won_type', 'points')->withTimestamps();
    }

    public function owned_groups() {
        return $this->hasMany(Group::class, 'owner_id', 'id');
    }

    public function groups() {
        return $this->belongsToMany(Group::class, 'group_users', 'user_id', 'group_id');
    }

    public function push_tokens() {
        return $this->hasMany(PushToken::class);
    }

    public function hidden_leagues()
    {
        return $this->hasMany(HiddenLeague::class);
    }

    public function league_rank()
    {
        return $this->hasMany(UserLeagueRank::class);
    }

    public static function updateGlobalRank($league_id) {
        // Update rank
        DB::statement(DB::raw('SET @rownumber = 0'));
        DB::update("
            UPDATE user_league_ranks
            SET global_rank = (@rownumber:=@rownumber+1)
            WHERE league_id = '$league_id'
            ORDER BY global_points DESC, global_exact_hits DESC;
        ");
    }

    public static function updateWeeklyRank($league_id) {
        // Update rank
        DB::statement(DB::raw('SET @rownumber = 0'));
        DB::update("
            UPDATE user_league_ranks
            SET week_rank = (@rownumber:=@rownumber+1)
            WHERE league_id = '$league_id'
            ORDER BY week_points DESC, week_exact_hits DESC;
        ");
    }
}
