<?php
/**
 * Created by PhpStorm.
 * User: shurik
 * Date: 7/9/18
 * Time: 2:07 PM
 */

namespace App\Http\Controllers;

use App\App;
use App\Models\Game;
use App\Models\WinningTeam;

class GameInfoController extends Controller {

	public function getGameInfo() {
		$game_id = request('game_id');
		$game = Game::where('id', $game_id)->with('team_a')->with('team_b')->first();
//		return response(print_r($game, true), 200)->header("Content-type", "text/plain");
		if($game) {
			$game->team_a->team_flag = asset($game->team_a->team_flag);
			$game->team_b->team_flag = asset($game->team_b->team_flag);
		}
		return ['game_info' => $game];
	}

}