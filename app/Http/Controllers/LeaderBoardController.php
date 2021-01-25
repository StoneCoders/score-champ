<?php

namespace App\Http\Controllers;

use App\App;
use App\Models\Group;
use App\Models\League;
use App\Models\User;
use App\Models\UserLeagueRank;
use Illuminate\Http\Request;

class LeaderBoardController extends Controller {
	protected $user;

	public function __construct(Request $request) {
		parent::__construct($request);
		$this->user = App::get_user();
	}

	private function query($limit, $offset, $operator, $orderBy, $whereIn) {
		$_tempLimit = $limit + 1;

		$ranks = UserLeagueRank::where('league_id', request()->get('league_id'))
			->where($this->rank_colomn, $operator, $this->current_user_rank)
			->orderBy($this->rank_colomn, $orderBy)
			->limit($_tempLimit)
			->offset($offset);

		if (is_array($whereIn)) {
			$ranks = $ranks->whereIn($whereIn['whereInColumn'], $whereIn['whereInItems']);
		}

		$users = User::whereIn("id", $ranks->pluck("user_id"))->get()->keyBy('id');

		$ranks = $ranks->get()->map(function ($userRank) use ($users) {
			return [
				'first_name' => $users[$userRank->user_id]->first_name,
				'last_name' => $users[$userRank->user_id]->last_name,
				'global_rank' => $userRank->global_rank,
				'week_rank' => $userRank->week_rank,
				'points' => $userRank->global_points,
				'week_points' => $userRank->week_points,
				'facebook_id' => $users[$userRank->user_id]->facebook_id ? $users[$userRank->user_id]->facebook_id : md5($userRank->user_id),
				'id' => $users[$userRank->user_id]->id,
				'image_url' => $users[$userRank->user_id]->image_url,
			];
		})->toArray();

		if (count($ranks) == $_tempLimit) {
			unset($ranks[$_tempLimit - 1]);
			return ['haveMore' => '1', 'users' => $ranks];
		}

		return ['haveMore' => '0', 'users' => $ranks];

	}

	private function getAfter($limit, $offset, $whereIn) {
		return $this->query($limit, $offset, '>', 'asc', $whereIn);
	}

	private function getBefore($limit, $offset, $whereIn) {
		return $this->query($limit, $offset, '<', 'desc', $whereIn);
	}

	private function getGroup() {
		$groupID = $this->request->get('group_id', false);
		if (!$groupID) {
			return ['error' => 'MISSING_FIELDS3'];
		}

		$group = Group::find($groupID);
		if (!$group) {
			return ['error' => 'GROUP_NOT_EXISTS'];
		}

		if (!$group->users()->find(App::get_user()->id)) {
			return ['error' => 'YOU_ARE_NOT_IN_GROUP'];
		}

		$users_ids_in_group = $group->users->pluck('id')->toArray();

		$users = User::all()->keyBy('id');
		$ranks = UserLeagueRank::with('user')
			->where('league_id', request()->get('league_id'))
			->whereIn('user_id', $users_ids_in_group)
			->orderBy($this->rank_colomn, 'ASC')
			->get()
			->map(function ($userRank) use ($users) {
				return [
					'first_name' => $users[$userRank->user_id]->first_name,
					'last_name' => $users[$userRank->user_id]->last_name,
					'global_rank' => $userRank->global_rank,
					'week_rank' => $userRank->week_rank,
					'points' => $userRank->global_points,
					'week_points' => $userRank->week_points,
					'facebook_id' => $users[$userRank->user_id]->facebook_id ? $users[$userRank->user_id]->facebook_id : md5($userRank->user_id),
					'id' => $users[$userRank->user_id]->id,
					'is_me' => (int)($userRank->user_id == $this->user->id),
					'image_url' => $users[$userRank->user_id]->image_url,
				];
			})
			->toArray();

		$users_not_bet_in_league = User::whereIn('id', $users_ids_in_group)
			->whereNotIn('id', array_column($ranks, 'id'))
			->get();

		foreach ($users_not_bet_in_league as $user) {
			$ranks[] = [
				'first_name' => $user->first_name,
				'last_name' => $user->last_name,
				'global_rank' => 0,
				'week_rank' => 0,
				'points' => 0,
				'week_points' => 0,
				'facebook_id' => $user->facebook_id ? $user->facebook_id : md5($user->id),
				'id' => $user->id,
				'is_me' => (int)($user->id == $this->user->id),
				'image_url' => $user->image_url,
			];
		}

		foreach ($ranks as $k => $user) {
			$ranks[$k]['global_rank'] = $k + 1;
			$ranks[$k]['week_rank'] = $k + 1;
		}

		return $ranks;
	}

	private function getAll() {
		return true;
	}

	private function getFriends() {

		try {
			$fb_response = json_decode(file_get_contents('https://graph.facebook.com/v2.7/me/friends?limit=1000&access_token=' . request()->get('access_token')), TRUE);
			$fb_friends_list = array_column($fb_response['data'], 'id');
			$fb_friends_list[] = $this->user->facebook_id;
		} catch (\Exception $e) {
		}

		$friends = $this->request->get('friends', false);
//        if(!$friends || !is_array(json_decode($friends)))
//            return ['error' => 'MISSING_FIELDS0'];


		$friends = json_decode($friends);
		$friends[] = $this->user->facebook_id;

		$friends_ids = User::whereIn('facebook_id', isset($fb_friends_list) ? $fb_friends_list : $friends)
			->get()
			->pluck('id');

		$friends_ids[] = $this->user->id;

		$users = User::all()->keyBy('id');
		$ranks = UserLeagueRank::with('user')
			->where('league_id', request()->get('league_id'))
			->whereIn('user_id', $friends_ids->toArray())
			->orderBy($this->rank_colomn, 'ASC')
			->get()
			->map(function ($userRank) use ($users) {
				return [
					'first_name' => $users[$userRank->user_id]->first_name,
					'last_name' => $users[$userRank->user_id]->last_name,
					'global_rank' => $userRank->global_rank,
					'week_rank' => $userRank->week_rank,
					'points' => $userRank->global_points,
					'week_points' => $userRank->week_points,
					'facebook_id' => $users[$userRank->user_id]->facebook_id ? $users[$userRank->user_id]->facebook_id : md5($userRank->user_id),
					'id' => $userRank->user_id,
					'is_me' => (int)($userRank->user_id == $this->user->id),
					'image_url' => $users[$userRank->user_id]->image_url,
				];
			})
			->toArray();

		foreach ($ranks as $k => $user) {
			$ranks[$k]['global_rank'] = $k + 1;
			$ranks[$k]['week_rank'] = $k + 1;
		}

		return $ranks;
	}

	protected $rank_colomn;
	protected $user_rank;
	protected $current_user_rank;

	public function get() {
		$league = League::findOrFail(request()->get('league_id'));
		$this->rank_colomn = request()->get('period', 'all') == 'all' ? 'global_rank' : 'week_rank';

		try {
			$this->user_rank = $this->user->league_rank()->where('league_id', $league->id)->firstOrFail();
		} catch (\Exception $e) {
			$rank = UserLeagueRank::where('league_id', $league->id)->count() + 1;

			$this->user_rank = UserLeagueRank::create([
				'user_id' => $this->user->id,
				'league_id' => $league->id,
				'global_rank' => $rank,
				'week_rank' => $rank,
			]);
		}

		$this->current_user_rank = $this->user_rank->{request()->get('period', 'all') == 'all' ? 'global_rank' : 'week_rank'};

		$types = [
			'friends',
			'group',
			'all'
		];
		$type = $this->request->get('type', false);
		if (!$type || !in_array($type, $types)) {
			return response(['status' => '0', 'error' => 'MISSING_FIELDS1'], 404);
		}

		$offset = $this->request->get('offset', false);
		if ($offset === false) {
			return response(['status' => '0', 'error' => 'MISSING_FIELDS2'], 404);
		}


		if ($type == 'group') {
			return response(['status' => '1', 'haveMore' => ['before' => 0, 'after' => 0], 'users' => $this->getGroup()]);
		}


		if ($type == 'friends') {
			return response(['status' => '1', 'haveMore' => ['before' => 0, 'after' => 0], 'users' => $this->getFriends()]);
		}


		$response = $this->getAll();

		if (isset($response['error'])) {
			return response(['status' => '0', 'error' => $response['error']], 404);
		}

		$limit = 50;
		if (App::isTest() && $this->request->has('limit')) {
			$limit = intval($this->request->get('limit'));
		}

		if ($offset == 0) {
			$before = $this->getBefore($limit, 0, $response);
			krsort($before['users']);
			$me = [[
				'first_name' => $this->user->first_name,
				'last_name' => $this->user->last_name,
				'week_rank' => $this->user_rank->week_rank,
				'week_points' => $this->user_rank->week_points,
				'global_rank' => $this->user_rank->global_rank,
				'points' => $this->user_rank->global_points,
				'facebook_id' => $this->user->facebook_id ? $this->user->facebook_id : md5($this->user->id),
				'id' => $this->user->id,
				'is_me' => 1,
				'image_url' => $this->user->image_url,
			]];
			$after = $this->getAfter($limit, 0, $response);

			$users = array_merge($before['users'], $me, $after['users']);
			$haveMore = [
				'before' => $before['haveMore'],
				'after' => $after['haveMore'],
			];
		} else if ($offset > 0) {
			$before = $this->getBefore($limit, $limit * $offset, $response);
			krsort($before['users']);
			$users = array_values($before['users']);
			$haveMore = ['before' => $before['haveMore']];
		} else if ($offset < 0) {
			$offset = abs($offset);
			$after = $this->getAfter($limit, $limit * $offset, $response);
			$users = [];
			foreach ($after['users'] as $v) {
				$users[] = $v;
			}
			$haveMore = ['after' => $after['haveMore']];
		}

		return response(['status' => '1', 'haveMore' => $haveMore, 'users' => $users]);
	}

	public function getTopThree() {
		$period = request()->get('period', 'all');
		$league_id = (int)request()->get('league_id');

		if (!in_array($period, ['week', 'all']))
			return response(['status' => '0', 'error' => 'INVALID_PERIOD'], 404);


		$usersRank = UserLeagueRank::with('user')
			->where('league_id', $league_id)
			->where($period == 'all' ? 'global_rank' : 'week_rank', '<=', 3)
			->orderBy($period == 'all' ? 'global_rank' : 'week_rank')
			->limit(3)
			->get();
		$users = User::whereIn("id", $usersRank->pluck("user_id"))->get()->keyBy('id');
		$ret = [
			'status' => '1',
			'users' => $usersRank->map(function ($userRank) use ($users) {
				return [
					'first_name' => $this->user->id == $userRank->user_id ? ($this->user->lang == 'he' ? 'אתה' : 'You') : $users[$userRank->user_id]->first_name,
					'last_name' => $this->user->id == $userRank->user_id ? '' : $users[$userRank->user_id]->last_name,
					'global_rank' => $userRank->global_rank,
					'week_rank' => $userRank->week_rank,
					'points' => $userRank->global_points,
					'week_points' => $userRank->week_points,
					'facebook_id' => $users[$userRank->user_id]->facebook_id ? $users[$userRank->user_id]->facebook_id : md5($userRank->user_id),
					'id' => $users[$userRank->user_id]->id,
					'image_url' => $users[$userRank->user_id]->image_url,
				];
			})
		];
		return $ret;

	}
}