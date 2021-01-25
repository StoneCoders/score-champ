<?php

/*
|--------------------------------------------------------------------------
| Model Factories
|--------------------------------------------------------------------------
|
| Here you may define all of your model factories. Model factories give
| you a convenient way to create models for testing and seeding your
| database. Just tell the factory how a default model should look.
|
*/

$factory->define(App\Models\Game::class, function (Faker\Generator $faker, $data) {

    $match_week_start_week = time() + ($data['match_week_id'] - 10) * 60*60*24*7;

    
    $return = [
        'team_a_id'       => \App\Models\WinningTeam::where('league_id', \App\Models\MatchWeek::find($data['match_week_id'])->league_id)->get()->random(1)->id,
        'team_b_id'       => \App\Models\WinningTeam::where('league_id', \App\Models\MatchWeek::find($data['match_week_id'])->league_id)->get()->random(1)->id,
        'game_date'       => date('Y-m-d H:i:s', rand($match_week_start_week, $match_week_start_week + 60*60*24*7)),
        'stage'           => $data['stage'],
        'match_week_id'   => $data['match_week_id'],
    ];

    if (strtotime($return['game_date']) < time())
    {
        // passed
        $return['team_a_score'] = rand(0,15);
        $return['team_b_score'] = rand(0,15);
        if(rand(0,1))
            $return['isFinished'] = 1;
    }

    return $return;
});

$factory->define(App\Models\TopScorePlayer::class, function (Faker\Generator $faker, $data) {
    $return = [
        'league_id' => $data['league_id'],
        'name' => $faker->name,
        'name_he' => $faker->name,
    ];
    switch(rand(0,2)) {
        case 0:
            $return['class'] = 'a';
            break;
        case 1:
            $return['class'] = 'b';
            break;
        case 2:
            $return['class'] = 'other';
            break;
    }
    return $return;
});


$factory->define(App\Models\MatchWeek::class, function (Faker\Generator $faker, $data) {
    $return = [
        'id'        => $data['id'],
        'league_id' => $data['league_id'],
    ];

    return $return;
});
$factory->define(App\Models\League::class, function (Faker\Generator $faker, $data) {
    $return = [
        'id'                         => $data['id'],
        'allow_bet_winning_team'     => 1,
        'is_active'                  => TRUE,
        'end_bet_top_score_player'   => $faker->dateTimeBetween('now', '+ 30 days'),
        'allow_bet_top_score_player' => 1,
        'end_bet_winning_team'       => $faker->dateTimeBetween('now', '+ 30 days'),
        'is_default'                 => $data['id'] == 1,
        'name_he'                    => 'ליגה '.$data['id'],
        'name_en'                    => 'League '.$data['id'],
        'COW_PTS_LEVEL_A'            =>  rand(5, 30),
        'COW_PTS_LEVEL_B'            =>  rand(5, 30),
        'BULL_PTS_LEVEL_A'           =>  rand(5, 30),
        'BULL_PTS_LEVEL_B'           =>  rand(5, 30),
        'WINNING_TEAM_PTS_CALSS_A'   =>  rand(5, 30),
        'WINNING_TEAM_PTS_CALSS_B'   =>  rand(5, 30),
        'WINNING_TEAM_PTS_CALSS_C'   =>  rand(5, 30),
        'TOP_SCORER_PTS_CALSS_A'     =>  rand(5, 30),
        'TOP_SCORER_PTS_CALSS_B'     =>  rand(5, 30),
        'TOP_SCORER_PTS_OTHER'       =>  rand(5, 30),
    ];

    return $return;
});

$factory->define(App\Models\WinningTeam::class, function (Faker\Generator $faker, $data) {
    $is_color = rand(0, 1);
    $return = [
        'name'      => $data['name'],
        'name_he'   => $data['name_he'],
        'league_id' => $data['league_id'],
        'team_color1' => $is_color ? $faker->hexcolor() : '',
        'team_color2' => $is_color ? $faker->hexcolor() : '',
        'team_flag'  => $is_color ? '' : 'team_flags/' . rand(1, 15) . '.png',
    ];
    switch(rand(0,2)) {
        case 0:
            $return['class'] = 'a';
            break;
        case 1:
            $return['class'] = 'b';
            break;
        case 2:
            $return['class'] = 'c';
            break;
    }
    return $return;
});

$factory->define(App\Models\Setting::class, function (Faker\Generator $faker) {
    $return = [
        'prevent_bet_minutes_before_game' =>  rand(5, 12),
        'html_rules_he'                   =>  'חוקי המשחק',
        'html_rules_en'                   =>  'GAME RULES',
        'IOS_RATE_URL'                    =>  'https://itunes.apple.com/il/app/eurochamp/id1102589642',
        'ANDROID_RATE_URL'                =>  'https://play.google.com/store/apps/details?id=il.co.compie.eurochamp',
        'html_empty_bets_open_en'         => $faker->paragraph(),
        'html_empty_bets_open_he'         => $faker->paragraph(),
        'html_empty_bets_closed_en'       => $faker->paragraph(),
        'html_empty_bets_closed_he'       => $faker->paragraph(),
    ];
    return $return;
});


$factory->define(App\Models\User::class, function (Faker\Generator $faker) {
    $return = [
        'first_name'        => $faker->firstName,
        'last_name'         => $faker->lastName,
        'gender'            => rand(0,1) ? 'male' : 'female',
        'email'             => $faker->email,
        'lang'              => rand(0,1) ? 'he' : 'en',
        'isPushActive'      => 0,
        'facebook_id'       => rand(111, 999),
    ];

    return $return;
});


$factory->define(App\Models\Admin::class, function (Faker\Generator $faker, $data) {
    $return = [
        'first_name'        => $faker->firstName,
        'last_name'         => $faker->lastName,
        'email'             => $data['email'],
        'password'          => bcrypt('q1w2e3r4'),
    ];

    return $return;
});