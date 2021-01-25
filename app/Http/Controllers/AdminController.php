<?php

namespace App\Http\Controllers;

use App\Models\Game;
use App\Models\Group;
use App\Models\GameBets;
use App\Models\League;
use App\Models\MatchWeek;
use App\Models\Setting;
use App\Models\TopScoreBet;
use App\Models\TopScorePlayer;
use App\Models\User;
use App\Models\UserLeagueRank;
use App\Models\WinningTeam;
use App\Models\WinningTeamBet;
use Carbon\Carbon;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Input;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Storage;
use SoccerAPI;
class AdminController extends Controller
{
    public function welcome() {
        return redirect()->route('showLeagues');
    }

    public function leagueSettings($league_id) {
        return view('admin/league_settings', [
            'league' => League::findOrFail($league_id),
        ]);
    }

    public function showSettings() {
        return view('admin/settings', [
            'leagues' => League::where('is_active', TRUE)->get(),
            'settings' => Setting::first()
        ]);
    }

    public function updateSettings() {
        $postData = Input::only(
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
            'adsplash_counter'
        );

        $validator = Validator::make($postData, [
            'prevent_bet_minutes_before_game' => 'required|numeric',
        ]);

        League::where('is_default', TRUE)->update([
            'is_default' => FALSE,
        ]);

        League::find(request()->get('default_league_id'))->update([
            'is_default' => TRUE,
        ]);


        if ($validator->passes())
            Setting::first()->update($postData);

        return view('admin/settings', [
            'leagues'  => League::where('is_active', TRUE)->get(),
            'settings' => Setting::first(),
            'message'  => $validator->passes() ? 'הגדרות נשמרו בהצלחה!' : NULL,
            'error'    => $validator->fails()  ? 'שגיאה בשמירת נתונים. הגדרות יכולות להכיל מספרים בלבד' : NULL,
        ]);
    }

    public function updateLeagueSettings($league_id) {
        $postData = Input::only(
            'name_he',
            'name_en',
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
		        'show_league_board',
		        'show_global_rank',
		        'show_week_rank',
		        'winning_team_title_en',
		        'winning_team_title_he',
		        'top_player_title_en',
		        'top_player_title_he',
            'score_range'
        );

        $validator = Validator::make($postData, [
            'name_he'                       => 'required',
            'name_en'                       => 'required',
            'COW_PTS_LEVEL_A'               => 'required|numeric',
            'COW_PTS_LEVEL_B'               => 'required|numeric',
            'BULL_PTS_LEVEL_A'              => 'required|numeric',
            'BULL_PTS_LEVEL_B'              => 'required|numeric',
            'WINNING_TEAM_PTS_CALSS_A'      => 'required|numeric',
            'WINNING_TEAM_PTS_CALSS_B'      => 'required|numeric',
            'WINNING_TEAM_PTS_CALSS_C'      => 'required|numeric',
            'TOP_SCORER_PTS_CALSS_A'        => 'required|numeric',
            'TOP_SCORER_PTS_CALSS_B'        => 'required|numeric',
            'TOP_SCORER_PTS_OTHER'          => 'required|numeric',
						'score_range'                   =>  array(
							'required',
							'regex:/^[0-9-, ]*$/'
						)
        ]);

        if ($validator->passes()){
            League::find($league_id)->update($postData);
            return redirect()->route('showLeagues');
        }
		    return view('admin/league_edit', [
			    'league' => League::findOrFail($league_id),
			    'message'  => $validator->passes() ? 'הגדרות נשמרו בהצלחה!' : NULL,
			    'error'    => $validator->fails()  ? ' שגיאה בשמירת נתונים. '
				    . implode('<br/>', array_values($validator->errors()->messages())[0]) : NULL,
		    ]);
    }

    public function showTopPlayer($league_id) {
        $league = League::findOrFail($league_id);
        return view('admin/players', [
            'settings'     => Setting::first(),
            'players'      => TopScorePlayer::where('league_id', $league_id)->get(),
            'allow_finish' => Carbon::now('UTC') > $league->end_bet_top_score_player,
            'league_id'    => $league_id,
            'league'       => $league,
        ]);
    }

    public function updateTopPlayer($league_id) {
        $league = League::findOrFail($league_id);

        // Update settings
        $settingsData = Input::get('settings');
        $settingsData['allow_bet_top_score_player'] = array_get($settingsData, 'allow_bet_top_score_player', FALSE);
        $league->update($settingsData);

        foreach ((array) Input::get('player') as $player_id => $player_data) {
            TopScorePlayer::find($player_id)->update($player_data);
        }


        return view('admin/players', [
            'players'      => TopScorePlayer::where('league_id', $league_id)->get(),
//            'settings'     => Setting::first(),
			'message' => 'הגדרות נשמרו בהצלחה!',
			'allow_finish' => Carbon::now('UTC') > $league->end_bet_top_score_player,
			'league_id' => $league_id,
			'league' => League::findOrFail($league_id),
		]);
	}

	public function createTopPlayer($league_id) {
		TopScorePlayer::create([
			'league_id' => $league_id,
		]);

		return back();
	}

	public function toggleActiveLeague($league_id) {
		$league = League::findOrFail($league_id);
		$league->update(['is_active' => !$league->is_active]);

		return back();
	}

	public function deleteTopPlayer($player_id) {
		$player = TopScorePlayer::find($player_id);
		$player->delete();

		// Remove existing bets
		TopScoreBet::where('top_score_player_id', $player_id)->delete();

		return back();
	}

	public function createTeam($league_id) {
		WinningTeam::create([
			'league_id' => $league_id,
		]);

		return back();
	}

	public function deleteTeam($team_id) {
		$team = WinningTeam::find($team_id);
		$team->delete();

		// Remove existing bets
		WinningTeamBet::where('winning_team_id', $team_id)->delete();

		return back();
	}

	public function add_league() {
		League::create([
			'current_match_week_id' => 0,
			'is_active' => FALSE,
			'allow_bet_winning_team' => 1,
			'end_bet_top_score_player' => date('Y-m-d', strtotime("+30 days")),
			'allow_bet_top_score_player' => 1,
			'end_bet_winning_team' => date('Y-m-d', strtotime("+30 days")),
			'is_default' => 0,
			'name_he' => 'ליגה חדשה',
			'name_en' => 'New League',
			'score_range' => '0-100'
		]);

		return back();
	}

	public function add_match_week($league_id) {
		MatchWeek::create([
			'league_id' => $league_id,
			'title_en' => 'new match week',
			'title_he' => 'מחזור חדש',
		]);

		if (MatchWeek::where('league_id', $league_id)->count() === 1) {
			League::find($league_id)->update([
				'current_match_week_id' => MatchWeek::where('league_id', $league_id)->first()->id,
			]);
		}

		return back();
	}

	public function delete_match_week($match_week_id) {
		$game_ids = Game::where('match_week_id', $match_week_id)->get()->pluck('id');

		// delete game
		Game::whereIn('id', $game_ids)->delete();

		// delete match week
		MatchWeek::where('id', $match_week_id)->delete();

		// delete gamebet
		GameBets::whereIn('game_id', $game_ids)->delete();

		return back();
	}

	public function copy_match_week($match_week_id) {
		$games = Game::where('match_week_id', $match_week_id)->get();
		$old_match_week = MatchWeek::where('id', $match_week_id)->get()->first();

		$new_match_week = new MatchWeek();
		$new_match_week->title_he = $old_match_week->title_he . ' העתק';
		$new_match_week->title_en = $old_match_week->title_en . ' copy';
		$new_match_week->league_id = $old_match_week->league_id;
		$new_match_week->save();

		foreach ($games as $game) {
			$newGame = new Game();
			$dateInTowMonth = Carbon::now()->addMonth(2);
			$newGame->game_date = $dateInTowMonth;
			$newGame->show_in_date = $dateInTowMonth;
			$newGame->hide = 1;

			$newGame->team_a_id = $game->team_a_id;
			$newGame->team_b_id = $game->team_b_id;
			$newGame->team_a_score = 0;
			$newGame->team_b_score = 0;
			$newGame->stage = $game->stage;
			$newGame->isFinished = 0;
			$newGame->created_at = $game->created_at;
			$newGame->updated_at = $game->updated_at;
			$newGame->match_week_id = $new_match_week->id;
			$newGame->link_button_text_he = $game->link_button_text_he;
			$newGame->link_button_text_en = $game->link_button_text_en;
			$newGame->link_video = $game->link_video;
			$newGame->link_text_info_he = $game->link_text_info_he;
			$newGame->link_text_info_en = $game->link_text_info_en;

			$newGame->save();
		}
		return back();
	}

	public function delete_game($game_id) {
		$game = Game::find($game_id);

		$match_week = MatchWeek::find($game->match_week_id);

		$league_id = $match_week->league_id;

		// delete game
		$game->delete();

		// delete gamebet
		GameBets::where('game_id', $game_id)->delete();

		if ($game->isFinished) {
			User::updateGlobalRank($league_id);
			User::updateWeeklyRank($league_id);
		}

		return back();
	}

	public function add_game($match_week_id) {
		// delete game
		Game::create([
			'match_week_id' => $match_week_id,
			'hide' => TRUE,
		]);
		session()->flash('match_id', $match_week_id);

		return back();
	}

	public function showWinningTeam($league_id) {
		$league = League::findOrFail($league_id);
		return view('admin/teams', [
			'teams' => WinningTeam::where('league_id', $league_id)->get(),
			'allow_finish' => Carbon::now('UTC') > $league->end_bet_winning_team && WinningTeam::where('league_id', $league_id)->where('isInGame', 1)->count() == 1,
			'league_id' => $league_id,
			'league' => $league,
		]);
	}

	public function updateWinningTeam($league_id) {
		$league = League::findOrFail($league_id);

		// Update settings
		$settingsData = Input::get('settings');
		$settingsData['allow_bet_winning_team'] = array_get($settingsData, 'allow_bet_winning_team', FALSE);
		$league->update($settingsData);
//var_dump(Input::get('team')); exit;
		foreach ((array)Input::get('team') as $team_id => $team_data) {
			if (request()->hasFile("team.$team_id.upload_shirt")) {
				// Upload image
				$file = request()->file("team.$team_id.upload_shirt");
				$filename = time() . '_' . rand(1, 9999) . '.' . $file->getClientOriginalExtension();
				$team_data['team_color1'] = '';
				$team_data['team_color2'] = '';
				$contents = file_get_contents($file);
				file_put_contents(public_path('images') . DIRECTORY_SEPARATOR . $filename, $contents);
				$team_data['team_flag'] = 'images/' . $filename;
			} else {
				//if (array_get($team_data, 'team_color1'))
					//$team_data['team_flag'] = '';
			}

			$team_data['isInGame'] = array_get($team_data, 'isInGame', FALSE);
			WinningTeam::find($team_id)->update($team_data);
		}

		return view('admin/teams', [
			'teams' => WinningTeam::where('league_id', $league_id)->get(),
			'message' => 'הגדרות נשמרו בהצלחה!',
			'allow_finish' => Carbon::now('UTC') > $league->end_bet_winning_team && WinningTeam::where('league_id', $league_id)->where('isInGame', 1)->count() == 1,
			'league_id' => $league_id,
			'league' => $league,
		]);
	}


	public function showGames($league_id) {

		return view('admin/games', [
			'winning_teams' => WinningTeam::where('league_id', $league_id)->get()->keyBy('id'),
			'match_weeks' => MatchWeek::where('league_id', $league_id)->orderBy('api_id', 'ASC')->with('game')->get(),
			//'match_weeks' => MatchWeek::where('league_id', $league_id)->with('game')->get(),
			'league' => League::findOrFail($league_id),
			'settings' => Setting::first(),
			'match_id' => session()->get('match_id'),
		]);
	}

	private $need_to_update_weekly_rank = FALSE;

	public function updateGames($league_id) {
		$league = League::findOrFail($league_id);
		$settings = Setting::first();

		GameBets::removeDuplicateBets();

		MatchWeek::find(request()->get('match_week_id'))->update([
			'title_en' => request()->get('match_week_en'),
			'title_he' => request()->get('match_week_he'),
		]);

		foreach ((array)Input::get('game') as $game_id => $game_data) {
			$game = Game::find($game_id);
			$game->fill($game_data);

			$game_data['game_show_in_date'] = array_get($game_data, 'game_show_in_date') ? $game_data['game_show_in_date'] : "NULL";
			$game->show_in_date = array_get($game_data, 'hide') ? $game_data['game_show_in_date'] : "NULL";

			if (!array_key_exists('team_a_score', $game_data)) {
				// disabled (finished) games
				if ($game->isDirty())
					$game->update();

				continue;
			}

			// Cast to integer for isDirty() check
			$game->hide = (int)array_get($game_data, 'hide', FALSE);
			$game->isFinished = (int)array_get($game_data, 'isFinished', FALSE);

			if (!$game->isFinished) {
				$game->team_a_score = NULL;
				$game->team_b_score = NULL;
			}

			if (!$game->isDirty())
				continue;

			DB::transaction(function () use ($game, $settings, $league) {

				$changed_attributes = $game->getDirty();
				$game->update();

				// "is finished" changed, need to add points
				if (array_get($changed_attributes, 'isFinished')) {
					// Get points for this game
					$points_cow = $league['COW_PTS_LEVEL_' . strtoupper($game->stage)];
					$points_bull = $league['BULL_PTS_LEVEL_' . strtoupper($game->stage)];


					// Get game bets cow
					$game_bets_hit_cow = GameBets::where('game_id', $game->id)
						->whereRaw('team_a_score ' . ($game->team_a_score > $game->team_b_score ? '>' : ($game->team_a_score < $game->team_b_score ? '<' : '=')) . ' team_b_score')
						->where(function ($q) use ($game) {
							$q->where('team_a_score', '!=', $game->team_a_score)->orWhere('team_b_score', '!=', $game->team_b_score);
						})->get();

					// Update game bets cow
					if ($game_bets_hit_cow->count()) {
						GameBets::whereIn('id', array_column($game_bets_hit_cow->toArray(), 'id'))
							->update([
								'is_won' => TRUE,
								'won_type' => 'regular',
								'points' => $points_cow,
							]);
					}


					// Get game bets bull
					$game_bets_hit_bull = GameBets::where('game_id', $game->id)
						->where('team_a_score', $game->team_a_score)
						->where('team_b_score', $game->team_b_score)
						->get();

					// Update game bets bull
					if ($game_bets_hit_bull->count()) {
						GameBets::whereIn('id', array_column($game_bets_hit_bull->toArray(), 'id'))
							->update([
								'is_won' => TRUE,
								'won_type' => 'bull',
								'points' => $points_bull,
							]);
					}

					// Check if first finished game in match week
					if ($game->match_week_id > $league->current_match_week_id) {
						$league->update(['current_match_week_id' => $game->match_week_id]);

						$this->need_to_update_weekly_rank = TRUE;

						DB::table('user_league_ranks')
							->where('league_id', $league->id)
							->update([
								'week_exact_hits' => 0,
								'week_hits' => 0,
								'week_points' => 0,
							]);
					}

					$update_cow = [
						'global_points' => DB::raw("global_points + $points_cow"),
						'global_hits' => DB::raw("global_hits + 1"),
					];

					$update_bull = [
						'global_points' => DB::raw("global_points + $points_bull"),
						'global_exact_hits' => DB::raw("global_exact_hits + 1"),
					];

					if ($game->match_week_id == $league->current_match_week_id) {
						$this->need_to_update_weekly_rank = TRUE;

						$update_cow['week_points'] = DB::raw("week_points + $points_cow");
						$update_cow['week_hits'] = DB::raw("week_hits + 1");

						$update_bull['week_points'] = DB::raw("week_points + $points_bull");
						$update_bull['week_exact_hits'] = DB::raw("week_exact_hits + 1");
					}

					if ($game_bets_hit_cow->count()) {
						// Add winning points to users that won cow
						UserLeagueRank::whereIn('user_id', array_column($game_bets_hit_cow->toArray(), 'user_id'))
							->where('league_id', $league->id)
							->update($update_cow);
					}

					if ($game_bets_hit_bull->count()) {
						// Add winning points to users that won bull
						UserLeagueRank::whereIn('user_id', array_column($game_bets_hit_bull->toArray(), 'user_id'))
							->where('league_id', $league->id)
							->update($update_bull);
					}
				}
			});
		}

		User::updateGlobalRank($league_id);

		if ($this->need_to_update_weekly_rank)
			User::updateWeeklyRank($league_id);

		return redirect()->route('games', ['league_id' => $league_id]);
	}

	public function cancelGameFinished() {
		$game = Game::findOrFail(request()->get('game_id'));
		$match_week = MatchWeek::findOrFail($game->match_week_id);
		$league = League::findOrFail($match_week->league_id);
		$settings = Setting::first();

		DB::transaction(function () use ($settings, $league, $match_week, $game) {

			$points_earned_cow = $league['COW_PTS_LEVEL_' . strtoupper($game->stage)];
			$points_earned_bull = $league['BULL_PTS_LEVEL_' . strtoupper($game->stage)];

			$bull_game_bets_to_cancel = GameBets::where('game_id', $game->id)
				->where('points', '>', 0)
				->where('won_type', 'bull')
				->get();

			$cow_game_bets_to_cancel = GameBets::where('game_id', $game->id)
				->where('points', '>', 0)
				->where('won_type', 'regular')
				->get();

			$update_cow = ['global_points' => DB::raw("global_points - $points_earned_cow")];
			$update_bull = ['global_points' => DB::raw("global_points - $points_earned_bull")];

			if ($game->match_week_id == $league->current_match_week_id) {
				$update_cow['week_points'] = DB::raw("week_points - $points_earned_cow");
				$update_cow['week_hits'] = DB::raw("week_hits - 1");

				$update_bull['week_points'] = DB::raw("week_points - $points_earned_bull");
				$update_bull['week_exact_hits'] = DB::raw("week_exact_hits - 1");

				$this->need_to_update_weekly_rank = TRUE;
			}

			// Revert bull points
			UserLeagueRank::whereIn('user_id', array_column($bull_game_bets_to_cancel->toArray(), 'user_id'))
				->where('league_id', $league->id)
				->update($update_cow);

			// Revert cow points
			UserLeagueRank::whereIn('user_id', array_column($cow_game_bets_to_cancel->toArray(), 'user_id'))
				->where('league_id', $league->id)
				->update($update_cow);


			$game->update([
				'isFinished' => FALSE,
				'team_a_score' => NULL,
				'team_b_score' => NULL,
			]);

			// Revert game bets
			GameBets::where('game_id', $game->id)->update([
				'is_won' => NULL,
				'won_type' => NULL,
				'points' => NULL,
			]);

			Artisan::call('rank', ['league' => $league->id]);
		});

		return redirect()->route('showReviveGame', ['league_id' => $league->id]);
	}

	public function showReviveGame($league_id) {
		$match_week_ids = MatchWeek::where('league_id', $league_id)->get()->pluck('id');
		$games = Game::whereIn('match_week_id', $match_week_ids)->where('isFinished', TRUE)->get();

		return view('admin/revive_game')
			->with('games', $games);
	}

	public function edit_league($league_id) {
		$league = League::findOrFail($league_id);

		return view('admin/league_edit')
			->with('league', $league);
	}

	public function cancelFinishTopPlayer($league_id) {
		$league = League::findOrFail($league_id);
//		$top_score_goals = TopScorePlayer::where('league_id', $league_id)->max('goals');
//		$top_score_player = TopScorePlayer::where('league_id', $league_id)->where('goals', $top_score_goals)->firstOrFail();
//    $points_earned = $league->{$top_score_player->class == 'other' ? 'TOP_SCORER_PTS_OTHER' : 'TOP_SCORER_PTS_CALSS_'.strtoupper($top_score_player->class)};


		$top_score_bets = TopScoreBet::where('points', '>', 0)->get();

		DB::table("user_league_ranks as ulr")
			->join('top_score_bets as tsb', function ($join) {
				$join->on('ulr.user_id', '=', 'tsb.user_id');
			})
			->join("top_score_players as tsp", function ($join) {
				$join->on('tsb.top_score_player_id', '=', 'tsp.id');
				$join->on('ulr.league_id', '=', 'tsp.league_id');

			})
			->whereIn('ulr.user_id', array_column($top_score_bets->toArray(), 'user_id'))
			->where('ulr.league_id', $league_id)
			->update([
				'global_points' => DB::raw("global_points - IFNULL(points, 0)"),
			]);


		DB::table('top_score_bets as tsb')
			->join('top_score_players as tsp', 'tsb.top_score_player_id', '=', 'tsp.id')
			->where('points', '>', 0)
			->update([
				'points' => NULL
			]);


		User::updateGlobalRank($league_id);

		$league->update(['top_player_finished' => FALSE]);

		return redirect()->route('topPlayer', ['league_id' => $league_id]);
	}

	public function finishTopPlayer($league_id) {
		$league = League::findOrFail($league_id);
		$top_score_goals = TopScorePlayer::where('league_id', $league_id)->max('goals');
		$top_score_players = TopScorePlayer::where('league_id', $league_id)->where('goals', $top_score_goals)->get();

		foreach ($top_score_players as $top_score_player) {
			$points_earned = $league->{$top_score_player->class == 'other' ? 'TOP_SCORER_PTS_OTHER' : 'TOP_SCORER_PTS_CALSS_' . strtoupper($top_score_player->class)};

			$top_score_bets = DB::table('top_score_bets as tsb')
				->join('top_score_players as tsp', 'tsb.top_score_player_id', '=', 'tsp.id')
				->where('tsp.league_id', $league_id)
				->where('top_score_player_id', $top_score_player->id)->pluck('user_id');


			if(DB::table('top_score_bets as tsb')
				->join('top_score_players as tsp', 'tsb.top_score_player_id', '=', 'tsp.id')
				->where('top_score_player_id', $top_score_player->id)
				->update([
					'points' => $points_earned,
				])) {

				UserLeagueRank::whereIn('user_id', $top_score_bets)
					->where('league_id', $league_id)
					->update([
						'global_points' => DB::raw("global_points + $points_earned"),
					]);
			}
		}
		User::updateGlobalRank($league_id);
		$league->update(['top_player_finished' => TRUE]);

		return redirect()->route('topPlayer', ['league_id' => $league_id]);
	}

	public function cancelFinishWinningTeam($league_id) {
		$winning_team = WinningTeam::where('league_id', $league_id)->where('isInGame', 1)->firstOrFail();

		$winning_team_bets = WinningTeamBet::where('points', '>', 0)->get();
		$league = League::findOrFail($league_id);
//	      $points_earned = $league->{'WINNING_TEAM_PTS_CALSS_'.strtoupper($winning_team->class)};
		$winning_team_ids_in_league = WinningTeam::where('league_id', $league->id)->get()->pluck('id');

		DB::table("user_league_ranks as ulr")
			->join('winning_team_bets as wtb', function ($join) {
				$join->on('ulr.user_id', '=', 'wtb.user_id');
			})
			->join("winning_teams as wt", function ($join) {
				$join->on('wtb.winning_team_id', '=', 'wt.id');
				$join->on('ulr.league_id', '=', 'wt.league_id');

			})
			->whereIn('ulr.user_id', array_column($winning_team_bets->toArray(), 'user_id'))
			->where('ulr.league_id', $league_id)
			->update([
				'global_points' => DB::raw("global_points - IFNULL(points, 0)"),
			]);


		WinningTeamBet::whereIn('winning_team_id', $winning_team_ids_in_league)->where('points', '>', 0)->update([
			'points' => NULL,
		]);

//        UserLeagueRank::whereIn('user_id', array_column($winning_team_bets->toArray(), 'user_id'))
//            ->where('league_id', $league_id)
//            ->update([
//                'global_points' => DB::raw("global_points - $points_earned"),
//            ]);


		User::updateGlobalRank($league_id);

		$league->update(['winning_team_finished' => FALSE]);

		return redirect()->route('winningTeam', ['league_id' => $league_id]);
	}

	public function finishWinningTeam($league_id) {
		$winning_team = WinningTeam::where('league_id', $league_id)->where('isInGame', 1)->firstOrFail();
		$league = League::findOrFail($league_id);
		$points_earned = $league->{'WINNING_TEAM_PTS_CALSS_' . strtoupper($winning_team->class)};

		$winning_team_bets = WinningTeamBet::where('winning_team_id', $winning_team->id)->get();

		WinningTeamBet::where('winning_team_id', $winning_team->id)
			->update(['points' => $points_earned]);

		UserLeagueRank::whereIn('user_id', array_column($winning_team_bets->toArray(), 'user_id'))
			->where('league_id', $league_id)
			->update([
				'global_points' => DB::raw("global_points + $points_earned"),
			]);

		User::updateGlobalRank($league_id);

		$league->update(['winning_team_finished' => TRUE]);

		return redirect()->route('winningTeam', ['league_id' => $league_id]);
	}

	public function showLeagues() {
		return view('admin.leagues')
			->with('leagues', League::orderBy('is_active', 'desc')->orderBy('id')->get());
	}

	public function exportDb() {
		$filename = 'db-backup-' . date('d_m_y__H_i_s') . '.sql';
		header('Content-Disposition: attachment; filename="' . $filename . '"');
		return $this->backup_tables(config('database.connections.mysql.host'), config('database.connections.mysql.username'), config('database.connections.mysql.password'), config('database.connections.mysql.database'));
	}

	/* backup the db OR just a table */
	private function backup_tables($host, $user, $pass, $name, $tables = '*') {
		$return = '';
		$tables = [
			'admins',
			'games',
			'logs',
			'match_weeks',
			'migrations',
			'pushes',
			'settings',
			'winning_teams',
			'top_score_players',
			'users',
			'users_friends',
			'winning_team_bets',
			'top_score_bets',
			'push_tokens',
			'groups',
			'group_users',
			'game_bets',
			'user_league_ranks',
		];
		//cycle through
		foreach ($tables as $table) {
			$result = DB::select('SELECT * FROM ' . $table);
			$num_fields = $result ? count($result[0]) : 0;

			$return .= 'DROP TABLE IF EXISTS ' . $table . ';';
			$row2 = DB::select('SHOW CREATE TABLE ' . $table);
			$return .= "\n\n" . $row2[0]->{'Create Table'} . ";\n\n";

			for ($i = 0; $i < $num_fields; $i++) {
				foreach ($result as $row) {
					$keys = array_keys((array)$row);
					$row = (array)$row;
					$return .= 'INSERT INTO ' . $table . ' VALUES(';
					foreach ($row as $j => $p) {
						$row[$j] = addslashes($row[$j]);
						$row[$j] = preg_replace("/\n/", "\\n", $row[$j]);
						if (isset($row[$j])) {
							$return .= '"' . $row[$j] . '"';
						} else {
							$return .= '""';
						}
						if ($j != $keys[count($keys) - 1]) {
							$return .= ',';
						}
					}
					$return .= ");\n";
				}
			}
			$return .= "\n\n\n";
		}

		return $return;
	}

	public function jsonGames($league_id) {
    $league = League::findOrFail($league_id);
    if($league) {
	    // Getting all Match weeks for League
      $matchWeeks  = MatchWeek::where('league_id', $league->id)->with('game')->with('game.team_a')->with('game.team_b')->orderBy('id', 'desc')->get();
	    // Getting all Games for Match weeks
	    return response(json_encode($matchWeeks, JSON_PRETTY_PRINT^JSON_UNESCAPED_UNICODE), 200)->header('Content-Type', 'application/json');
//      );
    }
  }
  public function showApi() {
		/*$leagues = SoccerAPI::leagues()->all();
		$seasons = SoccerAPI::seasons()->all();
		$teams = array();
		$rounds = array();
		$games = array();
		foreach($seasons->data as $season){
			if($season->is_current_season==1){
				$teams[] = SoccerAPI::teams()->allBySeasonId($season->id);
				if(SoccerAPI::rounds()->bySeasonId($season->id)->data){
					$rounds = SoccerAPI::rounds()->bySeasonId($season->id);
					foreach($rounds->data as $round){
						$date = date('Y-m-d');
						$to =  date('Y-m-d', strtotime($date. ' + 90 days'));
						//$date1='2013-01-11'; $date2='2015-01-12'; $result=($date1<$date2);
						if(($round->start<$to)){
							$games[]= SoccerAPI::fixtures()->betweenDates($round->start,$round->end);
						}
					}
				}
			}
		}*/
		
		$games=array();
				for($i=0;$i<10;$i++){
							
							$to =  date('Y-m-d', strtotime(date('Y-m-d'). ' + '.$i.' days'));
							//$games[]= SoccerAPI::fixtures()->betweenDates('2019-10-05','2019-10-06');
							$games[]= SoccerAPI::fixtures()->byDate($to);
							
				}
			$data = [ 
			'games' => $games,
			];
			
			
			
		//header('Content-type: application/json');
		echo "<pre>";
		print_r( $data );
			echo "</pre>";
		exit;
	}


    private function round($round)
    {
        $localTeam = WinningTeam::where('api_id', $round->localTeam->data->id)->get();
        $visitorTeam = WinningTeam::where('api_id', $round->visitorTeam->data->id)->get();
        $league_id  = League::where('api_id', $round->round->data->league_id)->value("id");
        $rounds = MatchWeek::where('api_id', $round->round->data->id)->get();
        $games = Game::where('api_id', $round->id)->get();
        if($round->round->data->league_id == 372){
        	// return [
        	// 	'round' => $round
        	// ];
        }
        if($round->localTeam->data->logo_path){$flaga = $round->localTeam->data->logo_path;}else{$flaga = ' ';}
        if($localTeam->count() == 0){
            Group::create(['owner_id' => 1,
                    'name' => $round->localTeam->data->name,
                    'image' => $round->localTeam->data->logo_path,
                    'created_at' => Carbon::now(),
                    'updated_at' => Carbon::now(),
                    'api_id' => $round->localTeam->data->id
            ]);
            WinningTeam::create(['name' => $round->localTeam->data->name,
                                'name_he' => $round->localTeam->data->name,
                                'team_flag' => $flaga,
                                'team_color1' => ' ',
                                'team_color2' => ' ',
                                'isInGame' => '1',
                                'class' => 'a',
                                'created_at' => Carbon::now(),
                                'updated_at' => Carbon::now(),
                                'league_id' => $league_id,
                                'api_id' => $round->localTeam->data->id
            ]);
        }else {
        	if($localTeam->first()->league_id != $league_id && $localTeam->count() < 2){
                WinningTeam::create(['name' => $round->localTeam->data->name,
                    'name_he' => $round->localTeam->data->name,
                    'team_flag' => $flaga,
                    'team_color1' => ' ',
                    'team_color2' => ' ',
                    'isInGame' => '1',
                    'class' => 'a',
                    'created_at' => Carbon::now(),
                    'updated_at' => Carbon::now(),
                    'league_id' => $localTeam->first()->league_id,
                    'api_id' => $round->localTeam->data->id
                ]);
            }elseif($localTeam->first()->league_id != $league_id && $localTeam->count() == 2){
                WinningTeam::where('id', $localTeam->first()->id)->update([
                    'isInGame' => '1',
                    'class' => 'a',
                    'updated_at' => Carbon::now(),
                    'league_id' => $league_id,
                    'api_id' => $round->localTeam->data->id
                ]);
                 return [
                    'lab' => 'localTeam',
                    'DB' => $localTeam->first()->name,
                    'API' =>$league_id,
                ];
            }else{
                WinningTeam::where('api_id', $round->localTeam->data->id)->update([
                    'isInGame' => '1',
                    'class' => 'a',
                    'updated_at' => Carbon::now(),
                    'league_id' => $league_id,
                    'api_id' => $round->localTeam->data->id
                ]);
            }
        }
        if($visitorTeam->count() == 0){
            if($round->visitorTeam->data->logo_path){$flagb = $round->visitorTeam->data->logo_path;}else{$flagb = ' ';}
            Group::create(['owner_id' => 1,
                'name' => $round->visitorTeam->data->name,
                'image' => $round->visitorTeam->data->logo_path,
                'created_at' => Carbon::now(),
                'updated_at' => Carbon::now(),
                'api_id' => $round->visitorTeam->data->id
            ]);
            WinningTeam::create(['name' => $round->visitorTeam->data->name,
                'name_he' => $round->visitorTeam->data->name,
                'team_flag' => $flagb,
                'team_color1' => ' ',
                'team_color2' => ' ',
                'isInGame' => '1',
                'class' => 'a',
                'created_at' => Carbon::now(),
                'updated_at' => Carbon::now(),
                'league_id' => $league_id,
                'api_id' => $round->visitorTeam->data->id
            ]);
        }else{
            if($visitorTeam->first()->league_id != $league_id && $visitorTeam->count() < 2){
                WinningTeam::create(['name' => $round->visitorTeam->data->name,
                    'name_he' => $round->visitorTeam->data->name,
                    'team_flag' => $flaga,
                    'team_color1' => ' ',
                    'team_color2' => ' ',
                    'isInGame' => '1',
                    'class' => 'a',
                    'created_at' => Carbon::now(),
                    'updated_at' => Carbon::now(),
                    'league_id' => $visitorTeam->first()->league_id,
                    'api_id' => $round->visitorTeam->data->id
                ]);
                return [
                    'lab' => 'VisitorTeam',
                    'DB' => $visitorTeam->count(),
                    'API' => $round->visitorTeam->data->name,
                ];
            }elseif ($visitorTeam->first()->league_id != $league_id && $visitorTeam->count() == 2){
                WinningTeam::where('id', $visitorTeam->first()->id)->update([
                    'isInGame' => '1',
                    'class' => 'a',
                    'updated_at' => Carbon::now(),
                    'league_id' => $visitorTeam->first()->league_id,
                    'api_id' => $round->visitorTeam->data->id
                ]);
                return [
                    'lab' => 'visitorTeam',
                    'DB' => $visitorTeam->first()->name,
                    'API' =>$league_id,
                ];
            }else{
                WinningTeam::where('api_id', $round->visitorTeam->data->id)->update([
                    'isInGame' => '1',
                    'class' => 'a',
                    'updated_at' => Carbon::now(),
                    'league_id' => $league_id,
                    'api_id' => $round->visitorTeam->data->id
                ]);
            }

        }
        if($rounds->count() == 0){
            MatchWeek::create(['title_he' => 'מחזור '.$round->round->data->name,
                                'title_en' => 'week '.$round->round->data->name,
                                'created_at' => date('Y-m-d H:i:s'),
                                'updated_at' => date('Y-m-d H:i:s'),
                                'league_id' => $league_id,
                                'start' => $round->round->data->start,
                                'end' => $round->round->data->end,
                                'api_id' => $round->round->data->id
            ]);
        }else{
            MatchWeek::where('api_id', $round->round->data->id)->update([
                            'created_at' => date('Y-m-d H:i:s'),
                            'updated_at' => date('Y-m-d H:i:s'),
                            'league_id' => $league_id,
                            'start' => $round->round->data->start,
                            'end' => $round->round->data->end,
                            'api_id' => $round->round->data->id
            ]);
        }
        $rounds_id = MatchWeek::where('api_id', $round->round->data->id)->value("id");
        $localTeam_id = WinningTeam::where('api_id', $round->localTeam->data->id)->value("id");
        $visitorTeam_id = WinningTeam::where('api_id', $round->visitorTeam->data->id)->value("id");

        if($round->winner_team_id){$win = 1;}else{$win = 0;}
        if($games->count() == 0){
            Game::create(['team_a_id' => $localTeam_id,
                'team_b_id' => $visitorTeam_id,
                'game_date' => $round->time->starting_at->date_time,
                'team_a_score' => $round->scores->localteam_score,
                'team_b_score' => $round->scores->visitorteam_score,
                'stage' => 'a',
                'isFinished' => $win,
                'hide' => '0',
                'created_at' => date('Y-m-d H:i:s'),
                'updated_at' => date('Y-m-d H:i:s'),
                'match_week_id' => $rounds_id,
                'link_button_text_he' => ' ',
                'link_button_text_en' => ' ',
                'link_video' => ' ',
                'link_text_info_he' => ' ',
                'link_text_info_en' => ' ',
                'show_in_date' => $round->time->starting_at->date_time,
                'api_id' => $round->id,
            ]);
        }else{
            Game::where('api_id', $round->id)->update([
                'game_date' => $round->time->starting_at->date_time,
                'team_a_score' => $round->scores->localteam_score,
                'team_b_score' => $round->scores->visitorteam_score,
                'isFinished' => $win,
                'updated_at' => date('Y-m-d H:i:s'),
                'show_in_date' => $round->time->starting_at->date_time,
            ]);
        }
    }

	private function stage($stage)
    {
        $localTeam = WinningTeam::where('api_id', $stage->localTeam->data->id)->get();
        $visitorTeam = WinningTeam::where('api_id', $stage->visitorTeam->data->id)->get();
        $league_id  = League::where('api_id', $stage->stage->data->league_id)->value("id");
        $rounds = MatchWeek::where('api_id', $stage->stage->data->id)->get();
        $games = Game::where('api_id', $stage->id)->get();
        $rounds_id = MatchWeek::where('api_id', $stage->stage->data->id)->value("id");
        if($stage->localTeam->data->logo_path){$flaga = $stage->localTeam->data->logo_path;}else{$flaga = ' ';}

		if($stage->stage->data->league_id == 372){
        	return [
        		'stage' => $stage
        	];
        }
        if($localTeam->count() == 0){
            Group::create(['owner_id' => 1,
                'name' => $stage->localTeam->data->name,
                'image' => $stage->localTeam->data->logo_path,
                'created_at' => Carbon::now(),
                'updated_at' => Carbon::now(),
                'api_id' => $stage->localTeam->data->id
            ]);
            WinningTeam::create(['name' => $stage->localTeam->data->name,
                'name_he' => $stage->localTeam->data->name,
                'team_flag' => $flaga,
                'team_color1' => ' ',
                'team_color2' => ' ',
                'isInGame' => '1',
                'class' => 'a',
                'created_at' => Carbon::now(),
                'updated_at' => Carbon::now(),
                'league_id' => $league_id,
                'api_id' => $stage->localTeam->data->id
            ]);
        }else {
            WinningTeam::where('api_id', $stage->localTeam->data->id)->update([
                'isInGame' => '1',
                'class' => 'b',
                'updated_at' => Carbon::now(),
                'league_id' => $league_id,
                'api_id' => $stage->localTeam->data->id
            ]);
        }
        if($visitorTeam->count() == 0){
            if($stage->visitorTeam->data->logo_path){$flagb = $stage->visitorTeam->data->logo_path;}else{$flagb = ' ';}
            Group::create(['owner_id' => 1,
                'name' => $stage->visitorTeam->data->name,
                'image' => $stage->visitorTeam->data->logo_path,
                'created_at' => Carbon::now(),
                'updated_at' => Carbon::now(),
                'api_id' => $stage->visitorTeam->data->id
            ]);
            WinningTeam::create(['name' => $stage->visitorTeam->data->name,
                'name_he' => $stage->visitorTeam->data->name,
                'team_flag' => $flagb,
                'team_color1' => ' ',
                'team_color2' => ' ',
                'isInGame' => '1',
                'class' => 'a',
                'created_at' => Carbon::now(),
                'updated_at' => Carbon::now(),
                'league_id' => $league_id,
                'api_id' => $stage->visitorTeam->data->id
            ]);
        }else{
            WinningTeam::where('api_id', $stage->visitorTeam->data->id)->update([
                'isInGame' => '1',
                'class' => 'b',
                'updated_at' => Carbon::now(),
                'league_id' => $league_id,
                'api_id' => $stage->visitorTeam->data->id
            ]);
        }
        if($rounds->count() == 0){
            MatchWeek::create(['title_he' => 'מחזור '.$stage->stage->data->name,
                'title_en' => 'week '.$stage->stage->data->name,
                'created_at' => date('Y-m-d H:i:s'),
                'updated_at' => date('Y-m-d H:i:s'),
                'league_id' => $league_id,
                'start' => "",
                'end' => "",
                'api_id' => $stage->stage->data->id
            ]);
        }else{
            MatchWeek::where('api_id', $stage->stage->data->id)->update([
                'created_at' => date('Y-m-d H:i:s'),
                'updated_at' => date('Y-m-d H:i:s'),
                'league_id' => $league_id,
                'start' => "",
                'end' => "",
                'api_id' => $stage->stage->data->id
            ]);
        }
        if($stage->winner_team_id){$win = 1;}else{$win = 0;}
        if(!$localTeam->isEmpty() && !$visitorTeam->isEmpty()){
            if($games->count() == 0){
                Game::create(['team_a_id' => $localTeam->first()->id,
                    'team_b_id' => $visitorTeam->first()->id,
                    'game_date' => $stage->time->starting_at->date_time,
                    'team_a_score' => $stage->scores->localteam_score,
                    'team_b_score' => $stage->scores->visitorteam_score,
                    'stage' => 'a',
                    'isFinished' => $win,
                    'hide' => '0',
                    'created_at' => date('Y-m-d H:i:s'),
                    'updated_at' => date('Y-m-d H:i:s'),
                    'match_week_id' => $rounds_id,
                    'link_button_text_he' => ' ',
                    'link_button_text_en' => ' ',
                    'link_video' => ' ',
                    'link_text_info_he' => ' ',
                    'link_text_info_en' => ' ',
                    'show_in_date' => $stage->time->starting_at->date_time,
                    'api_id' => $stage->id,
                ]);
            }else{
                Game::where('api_id', $stage->id)->update([
                    'game_date' => $stage->time->starting_at->date_time,
                    'team_a_score' => $stage->scores->localteam_score,
                    'team_b_score' => $stage->scores->visitorteam_score,
                    'isFinished' => $win,
                    'updated_at' => date('Y-m-d H:i:s'),
                    'show_in_date' => $stage->time->starting_at->date_time,
                ]);
            }
        }

    }


	public function crongames($from=0,$to=20 ,$plus='+')
    {
		$games=array();
        ini_set ( 'max_execution_time', 1200);
		for($i=$from;$i<$to;$i++){
		    $tot =  date('Y-m-d', strtotime(date('Y-m-d'). ' '.$plus.' '.$i.' days'));
		    $include = 'localTeam ,visitorTeam ,round,stage';
		    $games[]= SoccerAPI::fixtures()->setInclude($include)->byDate($tot);
        }
        foreach($games as $game){
            foreach($game->data as $g){
                if(isset($g->round)){
                	echo "<pre>";
                    print_r($this->round($g));
                    echo "</pre>";
                }else{
                    echo "<pre>";
                    print_r($this->stage($g));
                    echo "</pre>";
                }
            }
        }
        exit();
	}
	public function cronone(){
		$this->crongames(0,10);
	}
	public function crontwo(){
		$this->crongames(10,20);
	}
	public function cronthree(){
		$this->crongames(20,30);
	}
	public function crontfour(){
		$this->crongames(30,40);
	}
	public function crontfive(){
		$this->crongames(40,50);
	}
	public function crontsix(){
		$this->crongames(50,55);
	}
	public function crontsix2(){
		$this->crongames(55,60);
	}
	public function crontseven(){
		$this->crongames(60,70);
	}
	public function cronteit(){
		$this->crongames(70,80);
	}
	public function crontnain(){
		$this->crongames(80,90);
	}
	public function crononeminus(){
		$this->crongames(0,10,'-');
	}
	public function crontwominus(){
		$this->crongames(10,20,'-');
	}
	public function cronthreeminus(){
		$this->crongames(20,30,'-');
	}
	public function crontfourminus(){
		$this->crongames(30,40,'-');
	}
	public function crontfiveminus(){
		$this->crongames(40,50,'-');
	}
	public function crontsixminus(){
		$this->crongames(50,60,'-');
	}
	public function crontsevenminus(){
		$this->crongames(60,70,'-');
	}
	public function cronteitminus(){
		$this->crongames(70,80,'-');
	}
	public function crontnainminus(){
		$this->crongames(80,90,'-');
	}
	public function cronrounds(){
		/*
			"id": 169652,
				"name": 1,
				"league_id": 8,
				"season_id": 16036,
				"stage_id": 77443862,
				"start": "2019-08-09",
				"end": "2019-08-11"
		*/
		$seasons = SoccerAPI::seasons()->all();
		$rounds = array();
		foreach($seasons->data as $season){
			if($season->is_current_season==1){
					$rounds = SoccerAPI::rounds()->bySeasonId($season->id)->data;
					if(count($rounds)>0){
						foreach($rounds as $round){
							$date = date('Y-m-d');
							$to =  date('Y-m-d', strtotime($date. ' + 90 days'));
							if(($round->start<$to)){
								$issetrounds = DB::select('SELECT * FROM match_weeks WHERE api_id="'.$round->id.'"');
								if(!$issetrounds){
									
									DB::table('match_weeks')->insert([
										[
										'title_he' => 'מחזור '.$round->name,
										'title_en' => 'week '.$round->name,
										'created_at' => date('Y-m-d H:i:s'),
										'updated_at' => date('Y-m-d H:i:s'),
										'league_id' => $round->league_id,
										'start' => $round->start,
										'end' => $round->end,
										'api_id' => $round->id
										]
									]);
								}
								
							}
						}
					}
			}
		}
		exit;
	}
	public function crongroups(){
		/*
		{
					"id": 53,
					"legacy_id": 152,
					"name": "Celtic",
					"short_code": "CEL",
					"twitter": null,
					"country_id": 1161,
					"national_team": false,
					"founded": 1888,
					"logo_path": "https:\/\/cdn.sportmonks.com\/images\/\/soccer\/teams\/21\/53.png",
					"venue_id": 8909,
					"current_season_id": 16222
				}
		*/
		$teams = array();
		$seasons = SoccerAPI::seasons()->all();
		foreach($seasons->data as $season){
			if($season->is_current_season==1){
				$teams = SoccerAPI::teams()->allBySeasonId($season->id);
				foreach($teams as $team){ 
					foreach($team as $group){
						
						if(isset($group->id)){
							$issetgroup = DB::select('SELECT * FROM groups WHERE api_id="'.$group->id.'"');
							if(!$issetgroup){
								DB::table('groups')->insert([
									[
									'owner_id' => '0', 
									'name' => $group->name,
									'image' => $group->logo_path,
									'created_at' => date('Y-m-d H:i:s'),
									'updated_at' => date('Y-m-d H:i:s'),
									'api_id' => $group->id
									]
								]);
							}
						}
					}
					
				}
				
			}
		}
		exit;
	}
	
	public function cronleagues(){
		
		$win_teams = DB::table("winning_teams")->where('api_id','!=','NULL')->where('team_flag','=','')->get();
		foreach($win_teams as $teams){
			$team = SoccerAPI::teams()->byId($teams->api_id)->data;
			DB::table('winning_teams')->where('api_id', $team->id)->update([
				'team_flag' => $team->logo_path
			]);
		}
		/*
		{
				"id": 2,
				"active": true,
				"type": "cup_international",
				"legacy_id": 11,
				"country_id": 41,
				"logo_path": "https:\/\/cdn.sportmonks.com\/images\/soccer\/leagues\/2.png",
				"name": "Champions League",
				"is_cup": true,
				"current_season_id": 16029,
				"current_round_id": null,
				"current_stage_id": 77443832,
				"live_standings": true,
				"coverage": {
					"predictions": 0,
					"topscorer_goals": true,
					"topscorer_assists": true,
					"topscorer_cards": true
				}
			}
		*/
		$leagues = SoccerAPI::leagues()->all();
		
		foreach($leagues->data as $league){
			$issetleague = DB::select('SELECT * FROM leagues WHERE api_id="'.$league->id.'"');
			if(!$issetleague){
				DB::table('leagues')->insert([
					[
					'is_active' => '0', 
					'is_default' => '0',
					'name_he' => $league->name,
					'name_en' => $league->name,
					'top_player_finished' => '',
					'winning_team_finished' => '',
					'end_bet_top_score_player' => '',
					'allow_bet_top_score_player' => '',
					'allow_bet_winning_team' => '',
					'current_match_week_id' => '',
					'end_bet_winning_team' => '',
					'created_at' => date('Y-m-d H:i:s'),
					'updated_at' => date('Y-m-d H:i:s'),
					'is_turnir' => '0',
					'global_rank_title_en' => '',
					'global_rank_title_he' => '',
					'week_rank_title_en' => '',
					'week_rank_title_he' => '',
					'show_league_board' => '1',
					'show_global_rank' => '1',
					'show_week_rank' => '1',
					'api_id' => $league->id
					]
				]);
			}else{
				$leaguestart = DB::table("match_weeks as mw")
			->join('leagues as leag', function ($join) {
				$join->on('mw.league_id', '=', 'leag.id');
			})
			->join('games as game', function ($join) {
				$join->on('mw.id', '=', 'game.match_week_id');
			})
			
			->where('mw.start','!=','NULL')->groupBy('game.match_week_id')->orderBy('game.game_date','ASC')->get();
			//var_dump($leaguestart); exit;
			
			foreach($leaguestart as $start){
				DB::table('leagues')->where('id', $start->league_id)->update([
					'end_bet_top_score_player'=> $start->game_date,
					'end_bet_winning_team'=> $start->game_date
				]);
				//var_dump($leaguestart);
				//exit;
			}
			}
		}
		exit;
	}

}
