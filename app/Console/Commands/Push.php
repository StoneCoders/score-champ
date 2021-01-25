<?php

namespace App\Console\Commands;

use App\Models\Game;
use App\Models\Push as PushModel;
use App\Models\PushToken;
use App\Models\Setting;
use App\Models\User;
use Illuminate\Console\Command;
use Log;

class Push extends Command {
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'push';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Send push notifications';

    /**
     * Execute the console command.
     *
     * @return mixed
     */
	public function handle() {
        Log::useDailyFiles(storage_path().'/logs/push.log');

        $push = PushModel::where('already_started', false)->first();

        if ( ! $push)
            return;

        $push->update([ 'already_started' => TRUE ]);

        if ($push->type == 'all') {
            $route = json_decode($push->route);
            $users = User::where('isPushActive', TRUE)->get();
        } else {
            $route = json_decode($push->route);

            $games_today_count = Game::where('game_date', '<', date('Y-m-d H:i:s', strtotime('tomorrow')))->where('game_date', '>', date('Y-m-d H:i:s', time() + (Setting::first()->prevent_bet_minutes_before_game * 60)))->count();
            if ($games_today_count) {
                // there is a game today
                $users = User::whereRaw("id NOT IN (
                SELECT game_bets.user_id FROM games INNER JOIN game_bets ON game_bets.game_id = games.id WHERE games.game_date BETWEEN '".date('Y-M-D H:i:s', time() + (Setting::first()->prevent_bet_minutes_before_game * 60))."' and '".date('Y-M-D H:i:s', strtotime('tomorrow'))."')")
                    ->where('isPushActive', TRUE)
                    ->get();
            }
            else {
                $users = [];
            }
        }

        $android_tokens = [ 'he' => [], 'en' => [] ];
        $ios_tokens     = [ 'he' => [], 'en' => [] ];
        $expo_tokens    = [ 'he' => [], 'en' => [] ];

		foreach ($users as $user) {
			foreach ($user->push_tokens as $push_token) {
                if (!$push_token->active) continue;
			if (preg_match("/ExponentPushToken\[.*?\]/", $push_token->token)) {
				$expo_tokens[$user->lang][] = $push_token->token;
				} else if ($push_token->device == 'android') {
	                	$android_tokens[$user->lang][] = $push_token->token;
				} else {// ios
					$ios_tokens[$user->lang][] = $push_token->token;
				}
			}
        }

        $this->sendPushNotificationExpo($expo_tokens['he'], $route, $push->msg_he, $push->title_he);
        $this->sendPushNotificationExpo($expo_tokens['en'], $route, $push->msg, $push->title_he);
        $this->sendPushNotificationIos($ios_tokens['he'], $route, $push->msg_he);
        $this->sendPushNotificationIos($ios_tokens['en'], $route, $push->msg);
        $this->sendPushNotificationAndroid($android_tokens['he'], $route, $push->msg_he, $push->title_he);
        $this->sendPushNotificationAndroid($android_tokens['en'], $route, $push->msg, $push->title);

        $push->update([ 'finished' => TRUE ]);
    }

	private function sendPushNotificationExpo(array $pushTokens, $route, $msg, $title) {
        Log::info('Push Android started '.date('d-m-Y H:i:s'));

        foreach(array_chunk($pushTokens, 100) as $chunk) {
            $this->sendPushNotificationExpoChunk($chunk, $route, $msg, $title);
        }

        Log::info('Push Android finished '.date('d-m-Y H:i:s'));
    }

	private function sendPushNotificationAndroid(array $pushTokens, $route, $msg, $title) {
        Log::info('Push Android started '.date('d-m-Y H:i:s'));

        foreach(array_chunk($pushTokens, 1000) as $chunk) {
            $this->sendPushNotificationAndroidChunk($chunk, $route, $msg, $title);
        }

        Log::info('Push Android finished '.date('d-m-Y H:i:s'));
    }

	private function sendPushNotificationIos(array $tokens, $route, $msg) {
        foreach(array_chunk($tokens, 10) as $chunk) {
            $this->sendPushNotificationIosChunk($chunk, $route, $msg);
        }
    }


	private function sendPushNotificationExpoChunk(array $pushTokensChunk, $route, $msg, $title) {


        $push = array();
        foreach ($pushTokensChunk as $token) {
        	$push[] = array(
		        'to' => $token,
		        'title' => $title,
		        'body' => $msg,
		        'data' => ['route' => $route],
		        'sound' => 'default',
	        );
        }

        $headers = [
	        'accept: application/json',
	        'accept-encoding: gzip, deflate',
	        'Content-Type: application/json'
        ];

        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, 'https://exp.host/--/api/v2/push/send ');
        curl_setopt($ch, CURLOPT_POST, true);
//	      curl_setopt($ch, CURLOPT_HEADER, true);
        curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
        curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($push));
        $output = curl_exec($ch);
        curl_close($ch);
        return $output;
    }


	private function sendPushNotificationAndroidChunk(array $pushTokensChunk, $route, $msg, $title) {
        // API access key from Google API's Console
        $API_ACCESS_KEY = 'AIzaSyDalHnSo3myLxagVaBMwMJig0PTtn0OiGg';

        $fields = [
            'registration_ids' => $pushTokensChunk,
            'data' => [
                'message'   => $msg,
                'title'     => $title,
                'vibrate'   => 1,
                'sound'     => 1,
                'route' => $route,
            ]
        ];

        $headers = [
            'Authorization: key='.$API_ACCESS_KEY,
            'Content-Type: application/json'
        ];

        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, 'https://android.googleapis.com/gcm/send');
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
        curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($fields));
        $output = curl_exec($ch);
        curl_close($ch);

        return $output;
    }

	private function sendPushNotificationIosChunk(array $tokens, $route, $msg) {
        $initial_count = count($tokens);
        $send_failed_count = 0;

        Log::info("Push IOS started ".date('d-m-Y H:i:s').". Tokens to send: $initial_count");

        // Now we need to create JSON which can be sent to APNS
        $payload = json_encode([
            'aps' => [
                'alert' => $msg,
                'badge' => 1,
                'sound' => 'default'
            ],
            "routeName" => 'bets.opened',
        ]);

        do {
            // Check if last run stopped by a problematic token
			if (isset($problematic_token_key)) {
                $send_failed_count++;

                PushToken::where('token', array_get($tokens, $problematic_token_key))
                    ->update([ 'active' => FALSE ]);

                // Remove all sent tokens including the problematic one
                $tokens = array_slice($tokens, $problematic_token_key+1);

                // Close the connection
                if (isset($apns))
                    fclose($apns);
            }

            // Create a stream to the server
            $streamContext = stream_context_create();
            stream_context_set_option($streamContext, 'ssl', 'local_cert', base_path('push-ios-eurochamp-v3-prod.pem'));

            $apns = stream_socket_client(
                'ssl://gateway.push.apple.com:2195',
                $error,
                $errorString,
                60,
                STREAM_CLIENT_CONNECT, $streamContext);

            // Prevent blocking checkAppleErrorResponse
            stream_set_blocking($apns, 0);

			foreach ($tokens as $token_key => $push_token_string) {
                $apple_expiry = time() + (90 * 24 * 60 * 60); //Keep push alive (waiting for delivery) for 90 days
                $apnsMessage = pack("C", 1) . pack("N", $token_key) . pack("N", $apple_expiry) . pack("n", 32) . pack('H*', str_replace(' ', '', $push_token_string)) . pack("n", strlen($payload)) . $payload;

                Log::info($push_token_string);

                // Write the payload to the APNS
                fwrite($apns, $apnsMessage);
            }

            // Need to check: maybe 1 second sleep will also work
            sleep(5); // Sleep before checkAppleErrorResponse()

        } while (($problematic_token_key = $this->checkAppleErrorResponse($tokens, $apns)) !== FALSE);

        // Close the connection
        fclose($apns);

        Log::info('Push IOS finished '.date('d-m-Y H:i:s').'. Tokens success: '.($initial_count - $send_failed_count));
    }


    /**
     * FUNCTION to check if there is an error response from Apple
     * Returns the key if there was and FALSE if there was not
     *
     * @param array $tokens
     * @param $apns
     * @return bool|integer
     */
	private function checkAppleErrorResponse(array $tokens, $apns) {

        //byte1=always 8, byte2=StatusCode, bytes3,4,5,6=identifier(rowID). Should return nothing if OK.
        $apple_error_response = fread($apns, 6);
        //NOTE: Make sure you set stream_set_blocking($apns, 0) or else fread will pause your script and wait forever when there is no response to be sent.

        if ($apple_error_response) {
            //unpack the error response (first byte 'command" should always be 8)
            $error_response = unpack('Ccommand/Cstatus_code/Nidentifier', $apple_error_response);

            if ($error_response['status_code'] == '0')
                $error_response['status_code'] = '0-No errors encountered';
            else if ($error_response['status_code'] == '1')
                $error_response['status_code'] = '1-Processing error';
            else if ($error_response['status_code'] == '2')
                $error_response['status_code'] = '2-Missing device token';
            else if ($error_response['status_code'] == '3')
                $error_response['status_code'] = '3-Missing topic';
            else if ($error_response['status_code'] == '4')
                $error_response['status_code'] = '4-Missing payload';
            else if ($error_response['status_code'] == '5')
                $error_response['status_code'] = '5-Invalid token size';
            else if ($error_response['status_code'] == '6')
                $error_response['status_code'] = '6-Invalid topic size';
            else if ($error_response['status_code'] == '7')
                $error_response['status_code'] = '7-Invalid payload size';
            else if ($error_response['status_code'] == '8')
                $error_response['status_code'] = '8-Invalid token';
            else if ($error_response['status_code'] == '255')
                $error_response['status_code'] = '255-None (unknown)';
            else
                $error_response['status_code'] = $error_response['status_code'] . '-Not listed';

            Log::warning('Token: '.array_get($tokens, $error_response['identifier']).' '.$error_response['status_code']);

            return $error_response['identifier'];
        }

        return FALSE;
    }
}
