<?php

namespace App\Http\Controllers;

use App\App;
use App\Models\GameBets;
use App\Models\Group;
use App\Models\TopScoreBet;
use App\Models\TopScorePlayer;
use App\Models\User;
use App\Models\WinningTeam;
use App\Models\WinningTeamBet;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\URL;

class GroupsController extends Controller {

	private $invite_salt = "euro2018";

	public function getGroups() {
		$userGroups = [];
		$groups = App::get_user()->groups;
		foreach ($groups as $group) {
			$userGroups[] = [
				'id' => $group->id,
				'name' => $group->name,
				'image' => $group->image != '' ? URL::asset('images/' . $group->image) : '',
				'isAdmin' => $group->owner_id == App::get_user()->id,
				'admin_id' => $group->owner->facebook_id,
			];
		}

		return $userGroups ? response(['status' => '1', 'groups' => $userGroups]) : response(['status' => '0', 'error' => 'NO_GROUPS']);
	}

	public function createGroup() {

		$name = $this->request->get('name', false);
		$image = $this->request->get('image', false);
		if (!$name) {
			return response(['status' => '0', 'error' => 'MISSING_FIELDS'], 404);
		}

		if ($image && base64_encode(base64_decode($image, true)) !== $image) {
			return response(['status' => '0', 'error' => 'MISSING_IMAGE'], 404);
		}

		$group = App::get_user()->owned_groups()->create([
			'name' => $name
		]);
		$groupID = $group->id;


		if ($image) {
			$imageName = App::get_user()->id . '_' . $groupID . '_' . time() . '.jpg';
			$data = base64_decode($image);
			file_put_contents($this->imagesPath . DIRECTORY_SEPARATOR . $imageName, $data);
		}

		$group->users()->attach(App::get_user()->id);

		if ($image) {
			$group->update(['image' => $imageName]);
		}

		return response(['status' => '1', 'group_id' => $groupID]);
	}

	public function updateGroup() {
		$groupID = $this->request->get('group_id', false);
		if (!$groupID) {
			return response(['status' => '0', 'error' => 'MISSING_FIELDS'], 404);
		}

		$group = Group::find($groupID);
		if (!$group) {
			return response(['status' => '0', 'error' => 'GROUP_NOT_EXISTS'], 404);
		}

		if ($group->owner_id != App::get_user()->id) {
			return response(['status' => '0', 'error' => 'YOU_ARE_NOT_A_GROUP_OWNER'], 404);
		}

		$image = $this->request->get('image', false);
		if ($image) {
			if ($group->image != '') {
				File::delete($this->imagesPath . DIRECTORY_SEPARATOR . $group->image);
			}

			$imageName = App::get_user()->id . '_' . $groupID . '_' . time() . '.jpg';
			$data = base64_decode($image);
			file_put_contents($this->imagesPath . DIRECTORY_SEPARATOR . $imageName, $data);
			$group->image = $imageName;
		}

		$name = $this->request->get('name', false);
		if ($name) {
			$group->name = $this->request->get('name');
		}

		$group->save();
		return response(['status' => '1']);
	}

	public function addToGroup() {
		$groupID = $this->request->get('group_id', false);
		$userID = $this->request->get('user_id', false);

		if (!$groupID || !$userID) {
			return response(['status' => '0', 'error' => 'MISSING_FIELDS'], 404);
		}

		$group = Group::find($groupID);
		if (!$group) {
			return response(['status' => '0', 'error' => 'GROUP_NOT_EXISTS'], 404);
		}

		if ($group->owner_id != App::get_user()->id) {
			return response(['status' => '0', 'error' => 'YOU_ARE_NOT_A_GROUP_OWNER'], 404);
		}

		$user = User::find($userID);
		if (!$user) {
			return response(['status' => '0', 'error' => 'USER_NOT_EXISTS'], 404);
		}

		if ($userID == App::get_user()->id) {
			return response(['status' => '0', 'error' => 'YOU_ALREADY_IN_GROUP'], 404);
		}

		if ($group->users()->find($userID)) {
			return response(['status' => '0', 'error' => 'USER_ALREADY_IN_GROUP'], 404);
		}

		$group->users()->attach($userID);
		return response(['status' => '1']);
	}



	public function removeFromGroup() {
		$groupID = $this->request->get('group_id', false);
		$userID = $this->request->get('user_id', false);

		if (!$groupID || !$userID) {
			return response(['status' => '0', 'error' => 'MISSING_FIELDS'], 404);
		}

		$group = Group::find($groupID);
		if (!$group) {
			return response(['status' => '0', 'error' => 'GROUP_NOT_EXISTS'], 404);
		}

		if ($group->owner_id != App::get_user()->id) {
			return response(['status' => '0', 'error' => 'YOU_ARE_NOT_A_GROUP_OWNER'], 404);
		}

		$user = User::find($userID);
		if (!$user) {
			return response(['status' => '0', 'error' => 'USER_NOT_EXISTS'], 404);
		}

		if ($userID == App::get_user()->id) {
			return response(['status' => '0', 'error' => 'YOU_CANNOT_REMOVE_YOURSELF'], 404);
		}

		if (!$group->users()->find($userID)) {
			return response(['status' => '0', 'error' => 'USER_NOT_IN_GROUP'], 404);
		}

		$group->users()->detach($userID);
		return response(['status' => '1']);
	}

	public function leaveGroup() {
		$groupID = $this->request->get('group_id', false);

		if (!$groupID) {
			return response(['status' => '0', 'error' => 'MISSING_FIELDS'], 404);
		}

		$group = Group::find($groupID);
		if (!$group) {
			return response(['status' => '0', 'error' => 'GROUP_NOT_EXISTS'], 404);
		}

		if ($group->owner_id == App::get_user()->id) {
			return response(['status' => '0', 'error' => 'OWNER_CANNOT_LEAVE_GROUP_ONLY_DELETE'], 404);
		}

		if (!$group->users()->find(App::get_user()->id)) {
			return response(['status' => '0', 'error' => 'YOU_ARE_NOT_IN_GROUP'], 404);
		}

		$group->users()->detach(App::get_user()->id);
		return response(['status' => '1']);
	}

	public function deleteGroup() {
		$groupID = $this->request->get('group_id', false);
		if (!$groupID) {
			return response(['status' => '0', 'error' => 'MISSING_FIELDS'], 404);
		}

		$group = Group::find($groupID);
		if (!$group) {
			return response(['status' => '0', 'error' => 'GROUP_NOT_EXISTS'], 404);
		}

		if ($group->owner_id != App::get_user()->id) {
			return response(['status' => '0', 'error' => 'YOU_ARE_NOT_A_GROUP_OWNER'], 404);
		}

		if ($group->image != '') {
			File::delete($this->imagesPath . DIRECTORY_SEPARATOR . $group->image);
		}

		$group->delete();
		return response(['status' => '1']);
	}

	public function searchUsers() {
		$groupID = $this->request->get('group_id', false);
		$userName = $this->request->get('search', false);
		$offset = $this->request->get('offset', false);

		if (!$groupID || !$userName || $offset === false) {
			return response(['status' => '0', 'error' => 'MISSING_FIELDS'], 404);
		}

		$group = Group::find($groupID);
		if (!$group) {
			return response(['status' => '0', 'error' => 'GROUP_NOT_EXISTS'], 404);
		}

		if ($group->owner_id != App::get_user()->id) {
			return response(['status' => '0', 'error' => 'YOU_ARE_NOT_A_GROUP_OWNER'], 404);
		}

		$_users = User::where(DB::raw('CONCAT(first_name, " ", last_name)'), 'LIKE', "%$userName%")
			->limit(20)
			->offset($offset * 20)
			->get();

		if (!$_users->count()) {
			return response(['status' => '0', 'error' => 'NO_USERS_FOUND'], 404);
		}

		$users = [];
		foreach ($_users as $user) {
			if ($user->id == App::get_user()->id)
				continue;

			$users[] = [
				'id' => $user->id,
				'facebook_id' => $user->facebook_id,
				'first_name' => $user->first_name,
				'last_name' => $user->last_name,
				'isInGroup' => $user->groups()->find($groupID) ? 1 : 0,
			];
		}

		return count($users) ? response(['status' => '1', 'users' => $users]) : response(['status' => '0', 'error' => 'NO_USERS_FOUND'], 404);
	}


	public function inviteToGroup() {
		$groupID = $this->request->get('group_id', false);
		if (!$groupID) {
			return response(['status' => '0', 'error' => 'MISSING_FIELDS'], 404);
		}
		$group = Group::find($groupID);
		if (!$group) {
			return response(['status' => '0', 'error' => 'GROUP_NOT_EXISTS'], 404);
		}
		if ($group->owner_id != App::get_user()->id) {
			return response(['status' => '0', 'error' => 'YOU_ARE_NOT_A_GROUP_OWNER'], 404);
		}
		//TODO token expiration and cancellation
		$invite_token = md5($this->invite_salt . $groupID);
		return response(['status' => '1', 'invite_link' => "http://$_SERVER[HTTP_HOST]/invite/$invite_token"]);
	}

	public function showInvite($token) {
		$group = Group::whereRaw("md5(CONCAT('{$this->invite_salt}', id)) = ?", array($token))->first();
		return view('invite_link', [
			'group' => $group,
			'img_src' => ($group && $group->image != '') ? URL::asset('images/' . $group->image) : URL::asset('images/logo.png'),
			'deep_link' => $group ? 'eurochamp://app/seasonalLeaderboard/groups/'.$token : null
		]);
	}

	public function joinGroup($token) {

		$group = Group::whereRaw("md5(CONCAT('{$this->invite_salt}', id)) = ?", array($token))->first();
		if (!$group) {
			return response(['status' => '0', 'error' => 'GROUP_NOT_EXISTS'], 404);
		}
		if ($group->users()->find(App::get_user()->id)) {
			return response(['status' => '0', 'error' => 'YOU_ALREADY_IN_GROUP'], 404);
		}

		$group->users()->attach(App::get_user()->id);
		return response(['status' => '1']);
	}

	public function friendsBets() {
		$group_id = request()->get('group_id');
		$game_id = request()->get('game_id');

		$group = Group::findOrFail($group_id);

		if (!$group->users()->find(App::get_user()->id)) {
			return response(['status' => '0', 'error' => 'YOU_ARE_NOT_IN_GROUP'], 404);
		}

		//

		$users_with_bet = $group
			->users()
			->get()
			->map(function ($user) use ($game_id) {
				$game_bet = GameBets::where('user_id', $user->id)
					->where('game_id', $game_id)
					->first();

				return [
					'user_id' => $user->id,
					'team_a_score' => $game_bet ? (int)($game_bet->team_a_score) : null,
					'team_b_score' => $game_bet ? (int)($game_bet->team_b_score) : null,
					'first_name' => $user->first_name,
					'last_name' => $user->last_name,
					'is_current_user' => App::get_user()->id == $user->id,
				];
			});

		return [
			'status' => '1',
			'users' => $users_with_bet
		];
	}

	public function friendsWinningTeam() {
		$league_id = (int)request()->get('league_id');
		$group = Group::findOrFail(request()->get('group_id'));

		if (!$group->users()->find(App::get_user()->id)) {
			return response(['status' => '0', 'error' => 'YOU_ARE_NOT_IN_GROUP'], 404);
		}

//        $match_week_ids = MatchWeek::where('league_id', $league_id)->get()->pluck('id');
//        $game_ids = Game::whereIn('match_week_id', $match_week_ids)->get()->pluck('id');
		$winning_team_ids = WinningTeam::where('league_id', $league_id)->get()->pluck('id');

		// filter out users that did not bet in this league
		$users = $group->users
//            ->filter(function ($user) use ($game_ids) {
//                return GameBets::whereIn('game_id', $game_ids)->where('user_id', $user->id)->count();
//            })
			->map(function ($user) use ($winning_team_ids) {
				$winning_team = WinningTeamBet::whereIn('winning_team_id', $winning_team_ids)
					->join('winning_teams', 'winning_team_id', '=', 'id')
					->where('user_id', $user->id)
					->first();

				return [
					'winning_team_name' => $winning_team ? $winning_team->{App::get_user()->lang == 'en' ? 'name' : 'name_he'} : '---',
					'first_name' => $user->first_name,
					'last_name' => $user->last_name,
					'is_current_user' => App::get_user()->id == $user->id,
				];
			});

		return [
			'status' => '1',
			'users' => $users
		];
	}

	public function friendsTopScorePlayer() {
		$league_id = (int)request()->get('league_id');
		$group = Group::findOrFail(request()->get('group_id'));

		if (!$group->users()->find(App::get_user()->id)) {
			return response(['status' => '0', 'error' => 'YOU_ARE_NOT_IN_GROUP'], 404);
		}

//        $match_week_ids = MatchWeek::where('league_id', $league_id)->get()->pluck('id');
//        $game_ids = Game::whereIn('match_week_id', $match_week_ids)->get()->pluck('id');
		$player_ids = TopScorePlayer::where('league_id', $league_id)->get()->pluck('id');

		// filter out users that did not bet in this league
		$users = $group->users
//            ->filter(function ($user) use ($game_ids) {
//                return GameBets::whereIn('game_id', $game_ids)->where('user_id', $user->id)->count();
//            })
			->map(function ($user) use ($player_ids) {
				$score_player = TopScoreBet::whereIn('top_score_player_id', $player_ids)
					->join('top_score_players', 'top_score_player_id', '=', 'id')
					->where('user_id', $user->id)
					->first();

				return [
					'top_player_name' => $score_player ? $score_player->{App::get_user()->lang == 'en' ? 'name' : 'name_he'} : '---',
					'first_name' => $user->first_name,
					'last_name' => $user->last_name,
					'is_current_user' => App::get_user()->id == $user->id,
				];
			});

		return [
			'status' => '1',
			'users' => $users
		];
	}
}