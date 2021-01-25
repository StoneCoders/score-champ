<?php

namespace App\Http\Middleware;

use App\App;
use App\Models\User;
use Closure;
use Illuminate\Http\Request;
use Google_Client;

class VerifyAccessToken {
	/**
	 * Handle an incoming request.
	 *
	 * @param  \Illuminate\Http\Request $request
	 * @param  \Closure $next
	 * @return mixed
	 */
	public function handle(Request $request, Closure $next) {
		$access_token = $request->get('access_token', false);
		$auth_method = $request->get("auth_method");
		if (!$access_token) {
			return response(['status' => '0', 'error' => 'ACCESS_TOKEN_MISSING'], 403);
		}

		$salt = md5('euro');

		// PASSWORD LOGIN
		if ($auth_method == 'password' && preg_match("/^$salt(.*)/", $access_token, $m)) {
			return $this->handle_uid_hash_login($m[1], $request, $next);
		}
//		elseif ($auth_method == 'facebook') {
//
//		}
		elseif ($auth_method == 'google') {
			//GOOGLE_LOGIN
			$CLIENT_ID = array(
				'androidClient' => '339145381410-og324caacn9k6njcjhfji1ir3skutsov.apps.googleusercontent.com',
				'iosClient' => '339145381410-mqq6o2qqdgj0p5scmuepoba1t0uavi1i.apps.googleusercontent.com',
				'androidStandaloneAppClient'=> '339145381410-j8rkia4s5iku5kvv0dsisu2hgp2k3n5o.apps.googleusercontent.com',
				'iosStandaloneAppClient' => '339145381410-96413lm5s0nfiadkug52dtlbp1pt04hj.apps.googleusercontent.com',
			);
			$access_token = $request->get('access_token', false);
			$client_type = $request->get("client_type", false);
			$client = new Google_Client(['client_id' => $CLIENT_ID[$client_type]]);  // Specify the CLIENT_ID of the app that accesses the backend
			$payload = $client->verifyIdToken($access_token);
			if (!$payload || $payload['email_verified'] != true) {
				return response(['status' => '0', 'error' => 'GOOGLE_ACCOUNT_NOT_VALID'], 403);
			}

			$google_user_id = $payload['sub'];
			$google_email = $payload['email'];
			$user = User::where('email', $google_email)->first();
			if (!$user) {
				return response(['status' => '0', 'error' => 'USER_NOT_FOUND'], 403);
			}

		}
		else {
			// FACEBOOK LOGIN
			$facebook_user_data = @json_decode(file_get_contents("https://graph.facebook.com/me?access_token=$access_token"), TRUE);
			if (!$facebook_user_data) {
				return response(['status' => '0', 'error' => 'FACEBOOK_ACCOUNT_NOT_VALID'], 403);
			}
			$facebook_user_id = $facebook_user_data['id'];
			$user = User::where('facebook_id', $facebook_user_id)->first();
			if (!$user) {
				return response(['status' => '0', 'error' => 'USER_NOT_FOUND'], 403);
			}
//			return response(['status' => '0', 'error' => 'AUTH_METHOD_NOT_VALID'], 403);
		}


		App::set_user($user);

		return $next($request);
	}

	private function handle_uid_hash_login($md5uid, Request $request, Closure $next) {
		$user = User::whereRaw('md5(id) = ?', array($md5uid))->first();
		if (!$user) {
			return response(['status' => '0', 'error' => 'USER_NOT_FOUND'], 403);
		}

		App::set_user($user);

		return $next($request);
	}
}
