<?php

namespace App\Http\Controllers;

use App\App;
use App\Models\Game;
use App\Models\HiddenLeague;
use App\Models\League;
use App\Models\MatchWeek;
use App\Models\WinningTeam;
use Illuminate\Http\Request;
use App\Http\Requests;

class LeagueController extends Controller
{

    public function getAllLeagues()
    {
        $user = App::get_user();

        $orderBy = ($user->lang == 'he') ? 'name_he' : 'name_en';
        $hiddenLeagues = HiddenLeague::where('user_id', $user->id)->pluck('league_id')->toArray();
        $leagues = League::where('is_active', TRUE)->orderBy($orderBy, 'ASC')->get()->map(function ($league) use ($hiddenLeagues) {
            $league->is_active = !in_array($league->id, $hiddenLeagues);
            return $league;
        });

        return response(['leagues' => $leagues]);
    }

    public function getLeagueBy($league_id)
    {
        $league = League::findOrFail($league_id);
        return response($league);
    }

    public function getLeagueBoard(Request $request)
    {
        $league_id = $request->get('league_id');

        if (is_null($league_id))
            return response(['error' => 'No league id'], 404);

        $league = League::findOrFail($league_id);

        if (is_null($league))
            return response(['error' => 'League is not exist'], 404);

        // Getting all Match weeks for League
        $matchWeeks = MatchWeek::where('league_id', $league->id)->pluck('id');

        // Getting all Games for Match weeks
        $games = Game::whereIn('match_week_id', $matchWeeks)
            ->get();

        $teams = $this->calcGamesPerTeam($games);

        // Sort by points + diff
        array_multisort(array_column($teams, 'points'), SORT_DESC,
            array_column($teams, 'difference'), SORT_DESC,
            $teams);


        return [
            'teams' => $teams,
        ];
    }

    private function calcGamesPerTeam($games) {

        $winning_teams = WinningTeam::where('league_id', request()->get('league_id'))->get()->keyBy('id');
        $teams = [];

        foreach ($games as $game) {

            if (!array_get($winning_teams, $game['team_a_id']) || !array_get($winning_teams, $game['team_b_id']))
                continue;

            if ($game['team_a_id'] === NULL || $game['team_a_id'] === NULL)
                continue;

            try {
                if ($game['team_a_score'] === NULL && !isset($teams[$game['team_a_id']])) {
                    $teams[$game['team_a_id']] = [
                        'team_name_en'   => array_get($winning_teams, $game['team_a_id'])['name'],
                        'team_name_he'   => array_get($winning_teams, $game['team_a_id'])['name_he'],
                        'games'      => 0,
                        'winning'    => 0,
                        'draw'       => 0,
                        'losses'     => 0,
                        'ratio_a'    => 0,
                        'ratio_b'    => 0,
                        'difference' => 0,
                        'points'     => 0,
                    ];
                }

                if ($game['team_b_score'] === NULL && !isset($teams[$game['team_b_id']])) {
                    $teams[$game['team_b_id']] = [
                        'team_name_en' => array_get($winning_teams, $game['team_b_id'])['name'],
                        'team_name_he' => array_get($winning_teams, $game['team_b_id'])['name_he'],
                        'games'      => 0,
                        'winning'    => 0,
                        'draw'       => 0,
                        'losses'     => 0,
                        'ratio_a'    => 0,
                        'ratio_b'    => 0,
                        'difference' => 0,
                        'points'     => 0,
                    ];
                }
            } catch (\Exception $e) {
                // ignore..
                continue;
            }

            if ($game['team_a_score'] === NULL || $game['team_a_score'] === NULL)
                continue;

            // If Team already been set need to update info
            if (isset($teams[$game['team_a_id']])) {
                $teams[$game['team_a_id']]['games'] += 1;
                $teams[$game['team_a_id']]['winning'] += ($game['team_a_score'] > $game['team_b_score']) ? 1 : 0;
                $teams[$game['team_a_id']]['draw'] += ($game['team_a_score'] == $game['team_b_score']) ? 1 : 0;
                $teams[$game['team_a_id']]['losses'] += ($game['team_a_score'] < $game['team_b_score']) ? 1 : 0;
                $teams[$game['team_a_id']]['ratio_a'] += $game['team_a_score'];
                $teams[$game['team_a_id']]['ratio_b'] += $game['team_b_score'];
                $teams[$game['team_a_id']]['difference'] = $teams[$game['team_a_id']]['ratio_a'] - $teams[$game['team_a_id']]['ratio_b'];
                $teams[$game['team_a_id']]['points'] = $teams[$game['team_a_id']]['winning'] * 3 + $teams[$game['team_a_id']]['draw'] * 1;
            }

            if (isset($teams[$game['team_b_id']])) {
                $teams[$game['team_b_id']]['games'] += 1;
                $teams[$game['team_b_id']]['winning'] += ($game['team_b_score'] > $game['team_a_score']) ? 1 : 0;
                $teams[$game['team_b_id']]['draw'] += ($game['team_b_score'] == $game['team_a_score']) ? 1 : 0;
                $teams[$game['team_b_id']]['losses'] += ($game['team_b_score'] < $game['team_a_score']) ? 1 : 0;
                $teams[$game['team_b_id']]['ratio_a'] += $game['team_b_score'];
                $teams[$game['team_b_id']]['ratio_b'] += $game['team_a_score'];
                $teams[$game['team_b_id']]['difference'] = $teams[$game['team_b_id']]['ratio_a'] - $teams[$game['team_b_id']]['ratio_b'];
                $teams[$game['team_b_id']]['points'] = $teams[$game['team_b_id']]['winning'] * 3 + $teams[$game['team_b_id']]['draw'] * 1;
            }

            // If Team not been set yet
            if (!isset($teams[$game['team_a_id']]))
                $teams[$game['team_a_id']] = array(
                    'team_name_en' => array_get($winning_teams, $game['team_a_id'])['name'],
                    'team_name_he' => array_get($winning_teams, $game['team_a_id'])['name_he'],
                    'games' => 1,
                    'winning' => ($game['team_a_score'] > $game['team_b_score']) ? 1 : 0,
                    'draw' => ($game['team_a_score'] == $game['team_b_score']) ? 1 : 0,
                    'losses' => ($game['team_a_score'] < $game['team_b_score']) ? 1 : 0,
                    'ratio_a' => $game['team_a_score'],
                    'ratio_b' => $game['team_b_score'],
                    'difference' => $game['team_a_score'] - $game['team_b_score'],
                    'points' => ( (($game['team_a_score'] > $game['team_b_score']) ? 1 : 0) * 3 ) + ( (($game['team_a_score'] == $game['team_b_score']) ? 1 : 0) * 1 )
                );

            if (!isset($teams[$game['team_b_id']]))
                $teams[$game['team_b_id']] = array(
                    'team_name_en' => array_get($winning_teams, $game['team_b_id'])['name'],
                    'team_name_he' => array_get($winning_teams, $game['team_b_id'])['name_he'],
                    'games' => 1,
                    'winning' => ($game['team_b_score'] > $game['team_a_score']) ? 1 : 0,
                    'draw' => ($game['team_b_score'] == $game['team_a_score']) ? 1 : 0,
                    'losses' => ($game['team_b_score'] < $game['team_a_score']) ? 1 : 0,
                    'ratio_a' => $game['team_b_score'],
                    'ratio_b' => $game['team_a_score'],
                    'difference' => $game['team_b_score'] - $game['team_a_score'],
                    'points' => ((($game['team_b_score'] > $game['team_a_score']) ? 1 : 0) * 3) + ((($game['team_b_score'] == $game['team_a_score']) ? 1 : 0) * 1)
                );
        }

        return array_values($teams);
    }

}
