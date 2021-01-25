<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;

class Rank extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'rank {league}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Command description';

    /**
     * Create a new command instance.
     *
     * @return void
     */
    public function __construct()
    {
        parent::__construct();
    }

    /**
     * Execute the console command.
     *
     * @return mixed
     */
    public function handle()
    {
        set_time_limit(0);
        $league_id = $this->argument('league');

        $league = \App\Models\League::find($league_id);
        $ranks = \App\Models\UserLeagueRank::where('league_id', $league_id)->get();
        $match_weeks = \App\Models\MatchWeek::where('league_id', $league_id)->get();
        $league_game_ids = \App\Models\Game::whereIn('match_week_id', $match_weeks->pluck('id'))->get()->pluck('id');
        $current_match_week_game_ids = \App\Models\Game::where('match_week_id', $league->current_match_week_id)->get()->pluck('id');

        foreach ($ranks as $rank) {
            echo ('user: '.$rank->user_id."\n");
            $global_hits = \App\Models\GameBets::where('user_id', $rank->user_id)
                ->whereIn('game_id', $league_game_ids)
                ->where('won_type', 'regular')
                ->count();

            $global_exact_hits = \App\Models\GameBets::where('user_id', $rank->user_id)
                ->whereIn('game_id', $league_game_ids)
                ->where('won_type', 'bull')
                ->count();

            $week_hits = \App\Models\GameBets::where('user_id', $rank->user_id)
                ->whereIn('game_id', $current_match_week_game_ids)
                ->where('won_type', 'regular')
                ->count();

            $week_exact_hits = \App\Models\GameBets::where('user_id', $rank->user_id)
                ->whereIn('game_id', $current_match_week_game_ids)
                ->where('won_type', 'bull')
                ->count();

            $global_points = \App\Models\GameBets::where('user_id', $rank->user_id)
                ->whereIn('game_id', $league_game_ids)
                ->sum('points');

            $week_points = \App\Models\GameBets::where('user_id', $rank->user_id)
                ->whereIn('game_id', $current_match_week_game_ids)
                ->sum('points');

            \App\Models\UserLeagueRank::where('user_id', $rank->user_id)
                ->where('league_id', $league_id)
                ->update([
                    'global_hits'       => $global_hits,
                    'global_exact_hits' => $global_exact_hits,
                    'week_exact_hits'   => $week_exact_hits,
                    'week_hits'         => $week_hits,

                    'week_points'       => $week_points,
                    'global_points'     => $global_points,
                ]);
        }

        \App\Models\User::updateGlobalRank($league_id);
        \App\Models\User::updateWeeklyRank($league_id);
    }
}
