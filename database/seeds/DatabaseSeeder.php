<?php

use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $teams = [
            ['title_he' => 'ביתר ירושלים', 'title_en' => 'Beitar Jerusalem', 'flag' => '1'],
            ['title_he' => 'בני יהודה', 'title_en' => 'Bnei Yehuda', 'flag' => '2'],
            ['title_he' => 'סכנין', 'title_en' => 'Sakhnin', 'flag' => '3'],
            ['title_he' => 'הפועל אשקלון', 'title_en' => 'Hapoel Ashkelon', 'flag' => '4'],
            ['title_he' => 'הפועל באר שבע', 'title_en' => 'Hapoel Beer Sheva', 'flag' => '5'],
            ['title_he' => 'הפועל חיפה', 'title_en' => 'Hapoel Haifa', 'flag' => '6'],
            ['title_he' => 'הפועל כפר סבא', 'title_en' => 'Hapoel Kfar Saba', 'flag' => '7'],
            ['title_he' => 'הפועל רעננה', 'title_en' => 'Hapoel Raanana', 'flag' => '8'],
            ['title_he' => 'הפועל תל אביב', 'title_en' => 'Hapoel Tel Aviv', 'flag' => '9'],
            ['title_he' => 'מכבי חיפה', 'title_en' => 'Maccabi Haifa', 'flag' => '10'],
            ['title_he' => 'מכבי פתח תקווה', 'title_en' => 'Maccabi Petah Tikva', 'flag' => '11'],
            ['title_he' => 'מכבי תל אביב', 'title_en' => 'Maccabi Tel Aviv', 'flag' => '12'],
            ['title_he' => 'מס אשדוד', 'title_en' => 'FC Ashdod', 'flag' => '13'],
            ['title_he' => 'קריית שמונה', 'title_en' => 'Kiryat Shmona', 'flag' => '14'],
        ];
        
        $match_week = 1;
        foreach (range(1,3) as $league_id)
        {
            factory(App\Models\League::class, 1)->create(['id' => $league_id, 'current_match_week_id' => (($league_id-1) * 36) + 1, 'is_active' => TRUE]);

            foreach ($teams as $team) {
                factory(App\Models\WinningTeam::class, 1)->create([
                    'name' => $team['title_en'],
                    'name_he' => $team['title_he'],
                    'league_id' => $league_id,
                ]);
            }

            foreach (range(1, 36) as $match_week_loop)
            {
                factory(App\Models\MatchWeek::class, 1)->create([
                    'id'        => $match_week,
                    'title_he' => 'מחזור ' . $match_week_loop,
                    'title_en' => 'Match week ' . $match_week_loop,
                    'league_id' => $league_id,
                ]);

                factory(App\Models\Game::class, 7)->create([
                    'stage' => rand(0, 1) ? 'a' : 'b',
                    'match_week_id' => $match_week,
                ]);

                $match_week++;
            }

            factory(App\Models\TopScorePlayer::class, 12)->create([
                'league_id' => $league_id,
            ]);
        }


        factory(App\Models\User::class, 200)->create();
        factory(App\Models\Admin::class, 1)->create([ 'email' => 'ron@compie.co.il' ]);
        factory(App\Models\Admin::class, 1)->create([ 'email' => 'gil.nathan@gmail.com' ]);
        factory(App\Models\Admin::class, 1)->create([ 'email' => 'shlomi_laufer@hotmail.com' ]);
        factory(App\Models\Setting::class, 1)->create();
        $games = \App\Models\Game::all();
        foreach (\App\Models\User::all() as $user)
        {
            foreach (range(1, 20) as $i) {
                \App\Models\GameBets::updateOrCreate([
                    'user_id' => $user->id,
                    'game_id' => $games[rand(0, count($games)-1)]->id,
                ], [
                    'team_a_score' => rand(0, 4),
                    'team_b_score' => rand(0, 4)
                ]);
            }
        }

        foreach (\App\Models\League::all() as $league) {
            \App\Models\User::updateGlobalRank($league->id);
            \App\Models\User::updateWeeklyRank($league->id);
        }

        \App\Models\User::find(1)->update(['facebook_id' => '10204149624162875']);
        \App\Models\User::find(2)->update(['facebook_id' => '168376290325007']);
    }
}
