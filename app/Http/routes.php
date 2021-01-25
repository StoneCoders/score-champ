<?php

Route::group(['prefix' => 'public/index.php/api'], function () {
    Route::post('login',                  'AuthController@login_from_application');
    Route::post('content/rules',          'ContentController@rules');
    Route::post('content/empty_group',    'ContentController@emptyGroup');
    Route::post('settings/show_ads',      'SettingsController@show_ads');

    Route::group(['middleware' => [ 'verify_access_token']], function () {

        // leagues
        Route::post('get_all_leagues',                      'LeagueController@getAllLeagues');
        Route::post('get_league_board',                     'LeagueController@getLeagueBoard');

        // game
	      Route::post('get_game',                             'GameInfoController@getGameInfo');
        // bets
        Route::post('get_bets',                             'BetController@get_bets');
        Route::post('get_statistic_bets',                   'BetController@getStatisticGameBets');

        // bet
        Route::post('bet/winning_team',                     'BetController@winning_team');
        Route::post('bet/top_score',                        'BetController@top_score');
        Route::post('bet/game',                             'BetController@game');
        Route::post('bet/get_reminders',                    'BetController@get_reminders');

        // groups
        Route::post('group/get',                            'GroupsController@getGroups');
        Route::post('group/create',                         'GroupsController@createGroup');
        Route::post('group/edit',                           'GroupsController@updateGroup');
        Route::post('group/delete',                         'GroupsController@deleteGroup');
        Route::post('group/leave',                          'GroupsController@leaveGroup');
        Route::post('group/remove_users',                   'GroupsController@removeFromGroup');
        Route::post('group/add_users',                      'GroupsController@addToGroup');;
        Route::post('group/invite',                         'GroupsController@inviteToGroup');
	      Route::post('group/join/{token}',                   'GroupsController@joinGroup')->name('group_join');
        Route::post('group/friends_bets',                   'GroupsController@friendsBets');
        Route::post('group/friends_winning_team',           'GroupsController@friendsWinningTeam');
        Route::post('group/friends_top_score_player',       'GroupsController@friendsTopScorePlayer');

        Route::post('group/search_users',                   'GroupsController@searchUsers');

        // Stats
        Route::post('stats/me',                             'StatsController@get');
        Route::post('stats/get',                            'StatsController@byFacebookID');

        //Settings
        Route::post('settings/get',                         'SettingsController@get');
        Route::post('settings/change_lang',                 'SettingsController@changeLang');
        Route::post('settings/change_push_active',          'SettingsController@changePushActive');
        Route::post('settings/change_push_reminder_active', 'SettingsController@changePushReminderActive');
        Route::post('settings/update_token',                'SettingsController@updatePushToken');
        Route::post('settings/hide_leagues',                'SettingsController@hideLeagues');

        // Leader board
        Route::post('leaderboard/get',                      'LeaderBoardController@get');
        Route::post('leaderboard/get_top_three',            'LeaderBoardController@getTopThree');

    });
});

Route::get('invite/{token}', 'GroupsController@showInvite');

Route::get('link', function() {
    return view('app_link');
});

Route::get('public/index.php/link', function() {
    return view('app_link');
});

Route::group(['middleware' => ['web']], function () {
    Route::get('login',  'AuthController@showLogin')->name('login');
    Route::post('login', 'AuthController@doLogin');
    Route::get('logout', 'AuthController@getLogout')->name('logout');
//API
	Route::get('showapi',                            'AdminController@showApi')            ->name('showApi');
	Route::get('cronleagues',                            'AdminController@cronleagues')            ->name('cronleagues');
	Route::get('crongroups',                            'AdminController@crongroups')            ->name('crongroups');
	Route::get('cronrounds',                            'AdminController@cronrounds')            ->name('cronrounds');
	Route::get('cronone',                            'AdminController@cronone')            ->name('cronone');
	Route::get('crontwo',                            'AdminController@crontwo')            ->name('crontwo');
	Route::get('cronthree',                            'AdminController@cronthree')            ->name('cronthree');
	Route::get('crontfive',                            'AdminController@crontfive')            ->name('crontfive');
	Route::get('crontfour',                            'AdminController@crontfour')            ->name('crontfour');
	Route::get('crontwominus',                            'AdminController@crontwominus')            ->name('crontwominus');
	Route::get('cronthreeminus',                            'AdminController@cronthreeminus')            ->name('cronthreeminus');
	Route::get('crontfourminus',                            'AdminController@crontfourminus')            ->name('crontfourminus');
	Route::get('crononeminus',                            'AdminController@crononeminus')            ->name('crononeminus');
	Route::get('crontsix',                            'AdminController@crontsix')            ->name('crontsix');
	Route::get('crontseven',                            'AdminController@crontseven')            ->name('crontseven');
	Route::get('crontfiveminus',                            'AdminController@crontfiveminus')            ->name('crontfiveminus');
	Route::get('crontsixminus',                            'AdminController@crontsixminus')            ->name('crontsixminus');
	Route::get('crontsevenminus',                            'AdminController@crontsevenminus')            ->name('crontsevenminus');
	Route::get('cronteit',                            'AdminController@cronteit')            ->name('cronteit');
	Route::get('cronteitminus',                            'AdminController@cronteitminus')            ->name('cronteitminus');
	Route::get('crontnain',                            'AdminController@crontnain')            ->name('crontnain');
	Route::get('crontnainminus',                            'AdminController@crontnainminus')            ->name('crontnainminus');
	Route::get('crontsix2',                            'AdminController@crontsix2')            ->name('crontsix2');
	//API
    Route::group(['middleware' => ['login']], function () {
	    Route::get('get_game/{game_id?}',                             'GameInfoController@getGameInfo')->name('get_game');
        Route::get('leagues',                            'AdminController@showLeagues')            ->name('showLeagues');
		
        Route::get('add_league',                         'AdminController@add_league')             ->name('add_league');
        Route::get('add_match_week/{league_id}',         'AdminController@add_match_week')         ->name('add_match_week');
        Route::get('edit_league/{league_id}',            'AdminController@edit_league')            ->name('leagueEdit');
        Route::get('delete_match_week/{match_week_id}',  'AdminController@delete_match_week')      ->name('delete_match_week');
        Route::get('copy_match_week/{match_week_id}',    'AdminController@copy_match_week')        ->name('copy_match_week');
        Route::get('delete_game/{game_id}',              'AdminController@delete_game')            ->name('delete_game');
        Route::get('add_game/{match_week_id}',           'AdminController@add_game')               ->name('add_game');

        Route::get('toggle_active_league/{league_id}',   'AdminController@toggleActiveLeague')     ->name('toggleActiveLeague');
        Route::get('settings',                           'AdminController@showSettings')           ->name('settings');
        Route::post('settings',                          'AdminController@updateSettings')         ->name('updateSettings');
        Route::post('league_settings/{league_id}',       'AdminController@updateLeagueSettings')   ->name('updateLeagueSettings');

        Route::get('players/{league_id}',                'AdminController@showTopPlayer')          ->name('topPlayer');
        Route::post('players/{league_id}',               'AdminController@updateTopPlayer')        ->name('updateTopPlayer');
        Route::get('players/{league_id}/create',         'AdminController@createTopPlayer')        ->name('createTopPlayer');
        Route::get('players/{player_id}/delete',         'AdminController@deleteTopPlayer')        ->name('deleteTopPlayer');
        Route::get('teams/{league_id}',                  'AdminController@showWinningTeam')        ->name('winningTeam');
        Route::post('teams/{league_id}',                 'AdminController@updateWinningTeam')      ->name('updateWinningTeam');
        Route::get('teams/{league_id}/create',           'AdminController@createTeam')             ->name('createTeam');
        Route::get('teams/{team_id}/delete',             'AdminController@deleteTeam')             ->name('deleteTeam');
        Route::get('games/{league_id}',                  'AdminController@showGames')              ->name('games');
        Route::post('games/{league_id}',                 'AdminController@updateGames')            ->name('updateGames');
        Route::get('push/{sent?}',                       'PushController@showPush')                ->name('push');
        Route::post('push/delete',                       'PushController@deletePush')              ->name('deletePush');
        Route::post('push',                              'PushController@sendPush')                ->name('sendPush');
        Route::get('/',                                  'AdminController@welcome')                ->name('welcome');
        Route::get('cancelFinishTopPlayer/{league_id}',  'AdminController@cancelFinishTopPlayer')  ->name('cancelFinishTopPlayer');
        Route::get('finishTopPlayer/{league_id}',        'AdminController@finishTopPlayer')        ->name('finishTopPlayer');
        Route::get('cancelFinishWinningTeam/{league_id}',            'AdminController@cancelFinishWinningTeam')->name('cancelFinishWinningTeam');
        Route::get('finishWinningTeam/{league_id}',                  'AdminController@finishWinningTeam')      ->name('finishWinningTeam');
        Route::get('revive_game/{league_id}',            'AdminController@showReviveGame')         ->name('showReviveGame');
        Route::post('cancel_game_finished',              'AdminController@cancelGameFinished')     ->name('cancelGameFinished');
        Route::get('export_db',                          'AdminController@exportDb')               ->name('exportDb');

	      Route::get('json_games/{league_id}',             'AdminController@jsonGames')               ->name('jsonGames');
    });
});

Route::any('{any}', function () {
    die("Doesn't exist.");
});