<?php

namespace App\Http\Controllers;

use App\App;
use App\Models\UserLeagueRank;
use Carbon\Carbon;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class StatsController extends Controller
{
    protected $user;
    public function __construct(Request $request)
    {
        parent::__construct($request);
        $this->user = App::get_user();
    }

    public function get() {
        $league_id = (int) request()->get('league_id');
        
        if (!$league_id) {
            return response(['status' => '0', 'error' => 'MISSING_FIELDS'], 404);
        }


        try {
            $user_league_rank = UserLeagueRank::where('league_id', $league_id)->where('user_id', App::get_user()->id)->firstOrFail();
        } catch (\Exception $e) {
            $rank = UserLeagueRank::where('league_id', $league_id)->count() + 1;

            $user_league_rank = UserLeagueRank::firstOrCreate([
                'user_id' => App::get_user()->id,
                'league_id' => $league_id,
                'global_rank' => $rank,
                'week_rank' => $rank,
            ]);
        }
        
        $score_player = $this->user->score_player->where('league_id', $league_id)->first();
        $winning_team = $this->user->winning_team->where('league_id', $league_id)->first();
        $stats = [
            'week_points'       => (int) $user_league_rank->week_points,
            'week_hits'         => (int) $user_league_rank->week_hits,
            'week_exact_hits'   => (int) $user_league_rank->week_exact_hits,
            'global_rank'       => (int) $user_league_rank->global_rank,
            'total'             => (int) $user_league_rank->global_points,
            'total_guesses'     => (int) $this->user->game_bets->where('league_id', $league_id)->count(),
            'total_hits'        => (int) $user_league_rank->global_hits,
            'total_exact_hits'  => (int) $user_league_rank->global_exact_hits,
            'top_scorer_goals'  => $score_player ? (int) $score_player->goals : 0,
            'winning_team_play' => $winning_team ? (int) $winning_team->isInGame : 0,
            'name'              => $this->user->first_name,
            'facebook_id'       => $this->user->facebook_id,
        ];

        return response(['status' => '1', 'stats' => $stats]) ;
    }

    public function byFacebookID()
    {
        $league_id = (int) request()->get('league_id');

        $facebookID = $this->request->get('facebook_id', false);
        if(!$facebookID || !$league_id) {
            return response(['status' => '0', 'error' => 'MISSING_FIELDS'], 404);
        }

        $user = User::where('facebook_id', $facebookID)->orWhereRaw('md5(id) = ?', [$facebookID])->first();
        if(!$user) {
            return response(['status' => '0', 'error' => 'USER_NOT_EXISTS'], 404);
        }

        try {
            $user_league_rank = UserLeagueRank::where('league_id', $league_id)->where('user_id', $user->id)->firstOrFail();
        } catch (\Exception $e) {
            $rank = UserLeagueRank::where('league_id', $league_id)->count() + 1;

            try {
                $user_league_rank = UserLeagueRank::firstOrCreate([
                    'user_id' => App::get_user()->id,
                    'league_id' => $league_id,
                    'global_rank' => $rank,
                    'week_rank' => $rank,
                ]);
            } catch (\Exception $e) {
                $user_league_rank = UserLeagueRank::where([
                    'user_id' => App::get_user()->id,
                    'league_id' => $league_id,
                ])
                    ->firstOrFail();
            }
        }

        $score_player = $user->score_player->where('league_id', $league_id)->first();
        $winning_team = $user->winning_team->where('league_id', $league_id)->first();
        $stats = [
            'week_points'       => (int) $user_league_rank->week_points,
            'week_hits'         => (int) $user_league_rank->week_hits,
            'week_exact_hits'   => (int) $user_league_rank->week_exact_hits,
            'global_rank'       => (int) $user_league_rank->global_rank,
            'total'             => (int) $user_league_rank->global_points,
            'total_guesses'     => (int) $user->game_bets->count(),
            'total_hits'        => (int) $user_league_rank->global_hits,
            'total_exact_hits'  => (int) $user_league_rank->global_exact_hits,
            'top_scorer_goals'  => $score_player ? (int) $score_player->goals : 0,
            'winning_team_play' => $winning_team ? (int) $winning_team->isInGame : 0,
            'name'              => $user->first_name,
            'facebook_id'       => $user->facebook_id,
        ];

        return response(['status' => '1', 'stats' => $stats]) ;
    }
}
