<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use DB;

class GameBets extends Model
{
    protected $table = 'game_bets';
    protected $fillable = [
        'user_id',
        'game_id',
        'team_a_score',
        'team_b_score',
        'is_won',
        'won_type',
        'points',
    ];

    protected static function boot()
    {
        static::created(function ($game_bet) {
            $game = Game::find($game_bet->game_id);
            $match_week = $game->match_week;


            try {
                UserLeagueRank::where('league_id', $match_week->league_id)->where('user_id', $game_bet->user_id)->firstOrFail();
            } catch (\Exception $e) {
                $rank = UserLeagueRank::where('league_id', $match_week->league_id)->count() + 1;

                UserLeagueRank::firstOrCreate([
                    'user_id' => $game_bet->user_id,
                    'league_id' => $match_week->league_id,
                    'global_rank' => $rank,
                    'week_rank' => $rank,
                ]);
            }
        });
    }

    public function user()
    {
        return $this->belongsTo('App\Models\User');
    }

    public static function removeDuplicateBets()
    {
        $duplicate_bets = DB::table('game_bets')
            ->select('game_id', 'user_id', DB::raw('MIN(id) as min_id'))
            ->groupBy('game_id')
            ->groupBy('user_id')
            ->havingRaw('COUNT(1) > 1')
            ->get();

        foreach ($duplicate_bets as $duplicate_bet)
        {
            DB::table('game_bets')
                ->where('game_id', '=', $duplicate_bet->game_id)
                ->where('user_id', '=', $duplicate_bet->user_id)
                ->where('id',     '!=', $duplicate_bet->min_id)
                ->delete();
        }
    }

}
