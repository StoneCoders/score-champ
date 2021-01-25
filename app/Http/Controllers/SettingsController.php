<?php

namespace App\Http\Controllers;

use App\App;
use App\Models\HiddenLeague;
use App\Models\Setting;
use Carbon\Carbon;
use Illuminate\Http\Request;

class SettingsController extends Controller
{
    protected $user;
    public function __construct(Request $request)
    {
        parent::__construct($request);
        $this->user = App::get_user();
    }

    public function get() {
        $settings = [
            'lang'                 => $this->user->lang,
            'isPushActive'         => $this->user->isPushActive,
            'isPushReminderActive' => $this->user->isPushReminderActive,
	          'interstitialCount'   => Setting::first()->adsplash_counter
        ];

        return response(['status' => '1', 'settings' => $settings]) ;
    }

    public function changeLang() {
        $lang = $this->request->get('lang', false);
        $langs = ['he', 'en'];
        if(!$lang || !in_array($lang, $langs)) {
            return response(['status' => '0', 'error' => 'MISSING_FIELDS'], 404);
        }

        $this->user->lang = $lang;
        $this->user->save();

        return response(['status' => '1']) ;
    }

    public function changePushActive () {
         $isActive = $this->request->get('is_active', false);
         if($isActive === false) {
             return response(['status' => '0', 'error' => 'MISSING_FIELDS'], 404);
         }

         $this->user->update([ 'isPushActive' => (int) $isActive ]);

         return response(['status' => '1']) ;
     }

    public function changePushReminderActive () {
         $isActive = $this->request->get('is_active', false);
         if($isActive === false) {
             return response(['status' => '0', 'error' => 'MISSING_FIELDS'], 404);
         }

         $this->user->update([ 'isPushReminderActive' => (int) $isActive ]);

         return response(['status' => '1']) ;
     }

    public function show_ads() {
         return [
             'show' => (boolean) Setting::first()->show_ads,
         ];
     }

    public function updatePushToken() {
        $pushToken      = $this->request->get('push_token', false);
        $device         = $this->request->get('device', 'android');

        $devices = ['ios', 'android'];
        if(!$device || !in_array($device, $devices)) {
            return response(['status' => '0', 'error' => 'DEVICE_IS_MISSING'], 404);
        }

        if(!$pushToken) {
            return response(['status' => '0', 'error' => 'PUSH_TOKEN_MISSING'], 404);
        }

        $userPushTokens = $this->user->push_tokens()
            ->where('device', '=', $device)
            ->where('token', '=', $pushToken)->first();

        if(!$userPushTokens) {
            $this->user->push_tokens()->create([
                'device' => $device,
                'token' => $pushToken,
            ]);
        } else {
            $userPushTokens->touch();
        }
        return response(['status' => '1']) ;
    }

    public function hideLeagues(Request $request)
    {
        $leaguesId = $request->get('leagues_id');

        if (is_null($leaguesId))
            return response(['error' => 'No leagues id'], 404);

        HiddenLeague::where('user_id', $this->user->id)->delete();

        $response = [];
        foreach ($leaguesId as $leagueId) {
            $insert = [
                [
                    'user_id' => $this->user->id,
                    'league_id' => $leagueId
                ],
                [
                    'user_id' => $this->user->id,
                    'league_id' => $leagueId
                ]
            ];
            $response[] = HiddenLeague::updateOrCreate($insert[0], $insert[1]);
        }

        return response($response);
    }
}
