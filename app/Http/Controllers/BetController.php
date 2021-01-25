<?php

namespace App\Http\Controllers;

use App\App;
use App\Models\League;
use App\Models\TopScoreBet;
use App\Models\UserLeagueRank;
use App\Models\WinningTeamBet;
use Illuminate\Http\Request;
use App\Models\GameBets;
use Carbon\Carbon;
use App\Models\Game;
use App\Models\Setting;
use App\Models\WinningTeam;
use App\Models\TopScorePlayer;
use Illuminate\Support\Facades\URL;

class BetController extends Controller
{
    public function get_bets() {
        $league_id = (int) request()->get('league_id');
        $user = App::get_user();
        $settings = Setting::first();
        $returnData = ['status' => '1'];
        $currentTime = Carbon::now()->subMinutes(request()->get('timezone_offset') ?: -120);
        $league = League::findOrFail($league_id);
        $match_week_ids = $league->match_week()->get()->pluck('id');
        $winning_teams = WinningTeam::where('league_id', $league_id)->get()->keyBy('id');

        // Get total score players
        $returnData['top_score_players']['isActive'] = (int) ($league->allow_bet_top_score_player && Carbon::now() < Carbon::parse($league->end_bet_top_score_player)->subMinute($settings->prevent_bet_minutes_before_game));
        $returnData['top_score_players']['isHidden'] = !$league->allow_bet_top_score_player;


        $returnData['top_score_players']['list'] = TopScorePlayer::select('id', 'name', 'name_he')->where('league_id', $league_id)->get();
        $returnData['top_score_players']['selected'] = $user->score_player->where('league_id', $league_id)->first() ? $user->score_player->where('league_id', $league_id)->first()->id : 0;
        $returnData['top_score_players']['isFinished'] = $league->WINNING_TOP_SCORE_PLAYER ? 1 : 0;
        if($returnData['top_score_players']['isFinished']) {
            $topPlayer = TopScorePlayer::find($league->WINNING_TOP_SCORE_PLAYER);
            if($topPlayer && $user->score_player->first() && $user->score_player->first()->id == $topPlayer->id) {
                switch($topPlayer->class) {
                    case 'a':
                        $returnData['top_score_players']['points'] = $league->TOP_SCORER_PTS_CALSS_A;
                        break;
                    case 'b':
                        $returnData['top_score_players']['points'] = $league->TOP_SCORER_PTS_CALSS_B;
                        break;
                    case 'other':
                        $returnData['top_score_players']['points'] = $league->TOP_SCORER_PTS_OTHER;
                        break;
                }
            }
        }

        // Get winning teams
        $returnData['winning_teams']['isActive'] = (int) ($league->allow_bet_winning_team && Carbon::now() < Carbon::parse($league->end_bet_winning_team)->subMinute($league->prevent_bet_minutes_before_game));
        $returnData['winning_teams']['isHidden'] = ! $league->allow_bet_winning_team;
        $returnData['winning_teams']['list'] = WinningTeam::select('id', 'name', 'name_he', 'isInGame')->where('league_id', $league_id)->get();
        $userWinningTeam = $user->winning_team->where('league_id', $league_id)->first();
        $returnData['winning_teams']['selected'] = $userWinningTeam ? $userWinningTeam->id : 0;
        $returnData['winning_teams']['isFinished'] = $league->WINNING_TOP_SCORE_PLAYER ? 1 : 0;
        if($returnData['winning_teams']['isFinished']) {
            $winningTeam = WinningTeam::find($league->WINNING_TEAM_ID);
            $userWinningTeam = $user->winning_team->where('league_id', $league_id)->first();
            if($winningTeam && $userWinningTeam && $userWinningTeam->id == $winningTeam->id) {
                switch($winningTeam->class) {
                    case 'a':
                        $returnData['winning_teams']['points'] = $league->WINNING_TEAM_PTS_CALSS_A;
                        break;
                    case 'b':
                        $returnData['winning_teams']['points'] = $league->WINNING_TEAM_PTS_CALSS_B;
                        break;
                    case 'c':
                        $returnData['winning_teams']['points'] = $league->WINNING_TEAM_PTS_CALSS_C;
                        break;
                }
            }
        }

        $games = Game::orderBy('game_date', 'ASC')
            ->whereIn('match_week_id', $match_week_ids)
            ->orderBy('match_week_id', 'ASC')
            ->with('match_week')
            ->get();

        $userBets = GameBets::whereIn('game_id', $games->pluck('id'))
            ->where('user_id', $user->id)
            ->get()
            ->keyBy('game_id');

        $activeGames = [];
        $finishedGames = [];

        $today_text     = $user->lang == 'he' ? 'היום'  : 'today';
        $tomorrow_text  = $user->lang == 'he' ? 'מחר'   : 'tomorrow';
        $yesterday_text = $user->lang == 'he' ? 'אתמול' : 'yesterday';

        $month_translations = [
            'January'   => 'ינואר',
            'February'  => 'פברואר',
            'March'     => 'מרץ',
            'April'     => 'אפריל',
            'May'       => 'מאי',
            'June'      => 'יוני',
            'July'      => 'יולי',
            'August'    => 'אוגוסט',
            'September' => 'ספטמבר',
            'October'   => 'אוקטובר',
            'November'  => 'נובמבר',
            'December'  => 'דצמבר',
        ];

        foreach($games as $game) {
            if($game->hide)
                continue;

            if( ! $game->team_a_id || ! $game->team_b_id)
                continue;

            $gameStart = Carbon::parse($game->game_date)->subMinutes(request()->get('timezone_offset') ?: -120);
            $gameStartDate = $gameStart->subMinute($settings->prevent_bet_minutes_before_game);

            $gameData = [
                'id'              => $game->id,
                'stage'           => $game->stage,
                'team_a_title'    => array_get($winning_teams, $game['team_a_id'])['name'],
                'team_a_title_he' => array_get($winning_teams, $game['team_a_id'])['name_he'],
                'team_b_title'    => array_get($winning_teams, $game['team_b_id'])['name'],
                'team_b_title_he' => array_get($winning_teams, $game['team_b_id'])['name_he'],
                'team_a_color1'   => array_get($winning_teams, $game['team_a_id'])['team_color1'] ?: NULL,
                'team_a_color2'   => array_get($winning_teams, $game['team_a_id'])['team_color2'] ?: NULL,
                'team_b_color1'   => array_get($winning_teams, $game['team_b_id'])['team_color1'] ?: NULL,
                'team_b_color2'   => array_get($winning_teams, $game['team_b_id'])['team_color2'] ?: NULL,
                'game_date'       => $game->game_date,
                'team_a_flag'     => array_get($winning_teams, $game['team_a_id'])['team_flag'] ? asset(array_get($winning_teams, $game['team_a_id'])['team_flag']) : NULL,
                'team_b_flag'     => array_get($winning_teams, $game['team_b_id'])['team_flag'] ? asset(array_get($winning_teams, $game['team_b_id'])['team_flag']) : NULL,
                'hour'            => Carbon::parse($game->game_date, 'UTC')->subMinutes(request()->get('timezone_offset') ?: -120)->format('H:i'),//
                'link_button_text_he' => $game->link_button_text_he,
                'link_button_text_en' => $game->link_button_text_en,
                'link_video'          => $game->link_video,
                'link_text_info_he'   => nl2br($game->link_text_info_he),
                'link_text_info_en'   => nl2br($game->link_text_info_en),
            ];

            $currentBet = array_get($userBets, $game->id);
            $gameData['is_user_bet'] = $currentBet ? 1 : 0;
            if($gameData['is_user_bet']) {
                $gameData['user_bets'] = [
                    'team_a_score' => $userBets[$game->id]['team_a_score'],
                    'team_b_score' => $userBets[$game->id]['team_b_score'],
                ];
            }

            $game_date = strtoupper($gameStart->isToday() ? $today_text : ($gameStart->isTomorrow() ? $tomorrow_text : ($gameStart->isYesterday() ? $yesterday_text : $gameStart->formatLocalized($user->lang == 'he' ? '%d %B ,%Y' : '%B %d ,%Y'))));
            $category_title = $game_date.' - '.$game->match_week->{ $user->lang == 'he' ? 'title_he' : 'title_en' };

            if ($user->lang == 'he')
            {
                foreach ($month_translations as $month_eng => $month_he)
                {
                    $category_title = str_replace(strtoupper($month_eng), $month_he, $category_title);
                }
            }

            if($currentTime < $gameStartDate)
            {
                $activeGames[$category_title][] = $gameData;
            }
            else
            {
                $gameData['is_game_finished'] = $game->isFinished;
                if($gameData['is_game_finished']) {
                    if($gameData['is_user_bet']) {
                        $gameData['user_bets']['is_won'] = (int) $currentBet['is_won'];
                        $gameData['user_bets']['points'] = (int) $currentBet['points'];
                    }

                    $gameData['team_a_score'] = $game->team_a_score;
                    $gameData['team_b_score'] = $game->team_b_score;
                }

                $finishedGames[$category_title][] = $gameData;
            }
        }

        $returnData['active_games'] = $activeGames;
        $returnData['finished_games'] = array_reverse($finishedGames);

        $returnData['empty_contents'] = [
            'open_message_en'   => nl2br($league->html_empty_bets_open_en),
            'open_message_he'   => nl2br($league->html_empty_bets_open_he),
            'closed_message_en' => nl2br($league->html_empty_bets_closed_en),
            'closed_message_he' => nl2br($league->html_empty_bets_closed_he),
        ];

        $dirugim = array(
        	"global_rank_title_he" => "דירוג עונתי",
	        "global_rank_title_en" => "Seasonal Leaderborard",
	        "week_rank_title_he" => "דירוג שבועי",
	        "week_rank_title_en" => "Weekly Leaderboard",
        );

        $wttpDefaultTitles = [
        	'winning_team_title_he' => 'הקבוצה האלופה',
        	'winning_team_title_en' => 'The Champion',
	        'top_player_title_he' => 'מלך השערים',
	        'top_player_title_en' => 'Top Scorer',
        ];

        $returnData['league_settings'] = array(
	        'html_rules' => $league->{'html_rules_' . $user->lang} ?: $settings->{'html_rules_' . $user->lang},
	        'global_rank_title' => $league->{'global_rank_title_'.$user->lang} ?: $dirugim['global_rank_title_' . $user->lang],
	        'week_rank_title' => $league->{'week_rank_title_'.$user->lang} ?: $dirugim['week_rank_title_' . $user->lang],
	        'show_league_board' => $league->show_league_board,
	        'show_global_rank' =>  $league->show_global_rank,
	        'show_week_rank' => $league->show_week_rank,
	        'winning_team_title' => $league->{'winning_team_title_' . $user->lang} ?: $wttpDefaultTitles['winning_team_title_' . $user->lang],
	        'top_player_title' => $league->{'top_player_title_' . $user->lang} ?: $wttpDefaultTitles['top_player_title_' . $user->lang],
	        'score_range' => $league->score_range ? str_replace(" ", "", $league->score_range) : '0-100'
        );

        return response($returnData);
    }

    public function winning_team()
    {
        $settings = Setting::first();
        $user_id = App::get_user()->id;

        $team_id = $this->request->get('team_id', false);
        if(!$team_id) {
            return response(['status' => '0', 'error' => 'TEAM_ID_IS_MISSING'], 404);
        }


        // Validate winning team exist
        $team = WinningTeam::find($team_id);
        $league = League::findOrFail($team->league_id);

        // Check if bet is disabled by settings
        if (!$league->allow_bet_winning_team)
            return response(['status' => '0', 'error' => 'BET_IS_DISABLED'], 404);

        // Check if bet date passed
        if (Carbon::now() > Carbon::parse($league->end_bet_winning_team)->subMinute($settings->prevent_bet_minutes_before_game))
            return response(['status' => '0', 'error' => 'BET_ARE_CLOSED'], 404);

        if(!$team) {
            return response(['status' => '0', 'error' => 'TEAM_ID_NOT_VALID'], 404);
        }

        $winning_team_ids_in_league = WinningTeam::where('league_id', $league->id)->get()->pluck('id');
        WinningTeamBet::whereIn('winning_team_id', $winning_team_ids_in_league)
            ->where('user_id', $user_id)
            ->delete();

        WinningTeamBet::create([
            'winning_team_id' => $team_id,
            'user_id' => $user_id,
        ]);

		    try {
			    UserLeagueRank::where('league_id', $league->id)->where('user_id', $user_id)->firstOrFail();
		    } catch (\Exception $e) {
			    $rank = UserLeagueRank::where('league_id', $league->id)->count() + 1;

			    UserLeagueRank::create([
				    'user_id' => $user_id,
				    'league_id' => $league->id,
				    'global_rank' => $rank,
				    'week_rank' => $rank,
			    ]);
		    }

        return response(['status' => '1']);
    }

    public function top_score()
    {
        $settings = Setting::first();
        $user_id = App::get_user()->id;

        $player_id = $this->request->get('player_id', false);
        if(!$player_id) {
            return response(['status' => '0', 'error' => 'PLAYER_ID_IS_MISSING'], 404);
        }

        // Validate winning team exist
        $player = TopScorePlayer::find($player_id);
        $league = League::findOrFail(request()->get('league_id'));

        // Check if bet is disabled by settings
        if (!$league->allow_bet_top_score_player)
            return response(['status' => '0', 'error' => 'BET_IS_DISABLED'], 404);

        // Check if bet date passed
        if (Carbon::now() > Carbon::parse($league->end_bet_top_score_player)->subMinute($settings->prevent_bet_minutes_before_game))
            return response(['status' => '0', 'error' => 'BET_ARE_CLOSED'], 404);

        if(!$player) {
            return response(['status' => '0', 'error' => 'PLAYER_ID_NOT_VALID'], 404);
        }


        $top_score_ids_in_league = TopScorePlayer::where('league_id', $league->id)->get()->pluck('id');
        TopScoreBet::whereIn('top_score_player_id', $top_score_ids_in_league)
            ->where('user_id', $user_id)
//	          ->where('league_id', $league->id)
            ->delete();

        TopScoreBet::create([
            'top_score_player_id' => $player_id,
            'user_id' => $user_id,
//	          'league_id' => $league->id
        ]);



		    try {
			    UserLeagueRank::where('league_id', $league->id)->where('user_id', $user_id)->firstOrFail();
		    } catch (\Exception $e) {
			    $rank = UserLeagueRank::where('league_id', $league->id)->count() + 1;

			    UserLeagueRank::create([
				    'user_id' => $user_id,
				    'league_id' => $league->id,
				    'global_rank' => $rank,
				    'week_rank' => $rank,
			    ]);
		    }
        return response(['status' => '1']);
    }

    public function game()
    {
        $game_id        = $this->request->get('game_id', false);
        $team_a_score   = $this->request->get('team_a_score', false);
        $team_b_score   = $this->request->get('team_b_score', false);
        if(!$game_id || $team_a_score === false || $team_b_score === false) {
            return response(['status' => '0', 'error' => 'MISSING_FIELDS'], 404);
        }

        if($team_a_score < 0 || $team_b_score < 0) {
            return response(['status' => '0', 'error' => 'MIN_BET_IS_0'], 404);
        }

//        if($team_a_score > 15 || $team_b_score > 15 ) {
//            return response(['status' => '0', 'error' => 'MAX_BET_IS_15'], 404);
//        }

        $game_id = (int) ($game_id);
        $game = Game::find($game_id);
        if(!$game) {
            return response(['status' => '0', 'error' => 'GAME_NOT_FOUND'], 404);
        }

        if($game->hide) {
            return response(['status' => '0', 'error' => 'GAME_NOT_FOUND'], 405);
        }

        $currentTime = Carbon::now();
        $gameStartDate = Carbon::parse($game->game_date)->subMinute(Setting::first()->prevent_bet_minutes_before_game);
        if($currentTime >= $gameStartDate) {
            return response(['status' => '0', 'error' => 'BETS_ARE_CLOSED'], 404);
        }

        GameBets::updateOrCreate([
            'user_id' => App::get_user()->id,
            'game_id' => $game_id,
        ], [
            'team_a_score' => $team_a_score,
            'team_b_score' => $team_b_score
        ]);

        return response(['status' => '1']);
    }

    public function get_reminders()
    {
        $user = App::get_user();
        $league_id = request()->get('league_id');

        $league = League::findOrFail($league_id);

        if ( ! $user->isPushReminderActive)
            return [];

        $settings = Setting::first();

        $match_week_ids = $league->match_week()->pluck('id');
        $min_date_can_bet = date('Y-m-d H:i:s', time() + ($settings->prevent_bet_minutes_before_game * 60));
        $games_to_remind_to_bet = Game::whereDoesntHave('bets', function($q) use ($user) {
                $q->where('user_id', $user->id);
            })
            ->whereIn('match_week_id', $match_week_ids)
            ->where('game_date', '>', $min_date_can_bet)
            ->where('hide', FALSE)
            ->orderBy('game_date')
            // Returning only first 10 reminders. in some of android devices it crashes when we try to schedule too many alarms
            ->limit(10)
            ->get();


        try {
            UserLeagueRank::where('league_id', $league_id)->where('user_id', $user->id)->firstOrFail();
        } catch (\Exception $e) {
            $rank = UserLeagueRank::where('league_id', $league_id)->count() + 1;

            UserLeagueRank::create([
                'user_id' => $user->id,
                'league_id' => $league_id,
                'global_rank' => $rank,
                'week_rank' => $rank,
            ]);
        }

        $reminders = $games_to_remind_to_bet->map(function($game) use ($user, $settings) {
            return [
                'id'        => $game->id,
                'title'     => $user->lang == 'he' ? $settings->reminder_title_he : $settings->reminder_title_en,
                'text'      => $user->lang == 'he' ? $settings->reminder_content_he : $settings->reminder_content_en,
                'timestamp' => Carbon::parse($game->game_date)
                    ->subMinutes(request()->get('timezone_offset') ?: -120)//
                    ->subMinute(15)
                    ->subMinute($settings->prevent_bet_minutes_before_game)
                    ->format('U'),
            ];
        });

        $dates = [];
        $reminders_per_day = [];

        foreach ($reminders as $reminder) {
            $reminder_date = date('d-m-y', $reminder['timestamp']);
            if ( ! in_array($reminder_date, $dates)) {
                $dates[] = $reminder_date;
                $reminders_per_day[] = $reminder;
            }
        }

        return $reminders_per_day;
    }

    public function getStatisticGameBets(Request $request)
    {
        $game_id = $request->get('game_id');

        if ( ! $game_id)
            return response(['error' => 'No Game id'], 404);

        $game = Game::find($game_id);

        if ( ! $game)
            return response(['error' => 'No Game exist'], 404);

        $bets = GameBets::where('game_id', $game_id)->get();

        $statistic = [
            'total_bets' => $bets->count(),
            'team_a_win_bets' => 0,
            'team_a_win_percent' => 0,
            'team_b_win_bets' => 0,
            'team_b_win_percent' => 0,
            'tie_bets' => 0,
            'tie_percent' => 0
        ];

        foreach ($bets as $bet) {
            if ($bet['team_a_score'] > $bet['team_b_score']) {
                $statistic['team_a_win_bets']++;
            } else if ($bet['team_a_score'] < $bet['team_b_score']) {
                $statistic['team_b_win_bets']++;
            } else if ($bet['team_a_score'] == $bet['team_b_score']) {
                $statistic['tie_bets']++;
            }
        }

        $statistic['team_a_win_percent'] = $statistic['total_bets'] ? floor($statistic['team_a_win_bets'] * 100 / $statistic['total_bets']) : '--';
        $statistic['team_b_win_percent'] = $statistic['total_bets'] ? floor($statistic['team_b_win_bets'] * 100 / $statistic['total_bets']) : '--';
        $statistic['tie_percent']        = $statistic['total_bets'] ? 100 - $statistic['team_a_win_percent'] - $statistic['team_b_win_percent'] : '--';

        return response([
            'statistic' => $statistic
        ], 200);
    }
}
