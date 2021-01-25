<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Models\Setting;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Input;
use Illuminate\Support\Facades\Redirect;
use Illuminate\Support\Facades\Validator;
use Google_Client;
class AuthController extends Controller
{

	private  $passStrengthReqRE = '/^\S{4,}$/';
	public function login_from_application(Request $request)
	{
		$auth_method = $request->get('auth_method', false);

		if ($auth_method == "google") {
			return $this->google_login($request);
		}
		elseif ($auth_method == "password") {
			return $this->password_login($request);
		}

		return $this->facebook_login($request);
//		return response(['status' => '0', 'error' => 'LOGIN_REQUIRED'], 403);
	}


	public function password_login(Request $request) {


		$email          = $request->get('email', false);
		$password       = $request->get('password', false);
		$confirm       = $request->get('confirm', false);
		$first_name     = $request->get('first_name', false);
		$last_name      = $request->get('last_name', false);
		$gender         = $request->get('gender', false);
		$pushToken      = $request->get('push_token', false);
		$device         = $request->get('device', 'android');

		$devices = ['ios', 'android'];
		if(!$device || !in_array($device, $devices)) {
			return response(['status' => '0', 'error' => 'DEVICE_IS_MISSING'], 400);
		}

		if(!$email || !$password) {
			return response(['status' => '0', 'error' => 'LOGIN_FIELD_IS_MISSING'], 400);
		}

		$user = User::where('email', $email)->first();

		if(! $user) {
			if( ! $gender || !$first_name || !$last_name) {
				return response(['status' => '0', 'error' => 'REG_FIELD_IS_MISSING'], 400);
			}
			if(! preg_match($this->passStrengthReqRE, $password)) {
				return response(['status' => '0', 'error' => 'PASSWORD_NOT_SAFE'], 400);
			}
			if(!$confirm) {
				return response(['status' => '0', 'error' => 'PASSWORD_NOT_CONFIRMED'], 403);
			}
			if($confirm !== $password) {
				return response(['status' => '0', 'error' => 'PASSWORDS_NOT_MATCH'], 400);
			}

			$userNewRank = User::count() + 1;
//      	$md5pass = md5($password);
			$user = User::create([
				'first_name'  => $first_name,
				'last_name'   => $last_name,
				'gender'      => $gender,
				'email'       => $email,
				'password'    => Hash::make($password),
				'global_rank' => $userNewRank,
				'week_rank'   => $userNewRank,
				'lang'        => request()->get('language') == 'en' ? 'en' : 'he',
			]);
		}

		else if(! Hash::check($password, $user->password)) {
			return response(['status' => '0', 'error' => 'LOGIN_PASSWORD_FAILED', 'facebook_id_exists' => (bool) $user->facebook_id  ], 403);
		}

		if($pushToken) {
			$userPushTokens = $user->push_tokens()
				->where('device', '=', $device)
				->where('token', '=', $pushToken)->first();

			if(!$userPushTokens) {
				$user->push_tokens()->create([
					'device' => $device,
					'token' => $pushToken,
				]);
			}
		}

		// Reload user data (language default defined by DB)
		$user = User::findOrFail($user->id);
		$access_token = md5('euro') . md5($user->id);
		return response(['status' => '1', 'user_id' => $user->id, 'access_token' => $access_token,  'rate_url' => Setting::first()->{strtoupper($device) . '_RATE_URL'}, 'lang' => $user->lang]);

	}

	public function google_login(Request $request) {
		$CLIENT_ID = array(
			'androidClient' => '339145381410-og324caacn9k6njcjhfji1ir3skutsov.apps.googleusercontent.com',
			'iosClient' => '339145381410-mqq6o2qqdgj0p5scmuepoba1t0uavi1i.apps.googleusercontent.com',
			'androidStandaloneAppClient'=> '339145381410-j8rkia4s5iku5kvv0dsisu2hgp2k3n5o.apps.googleusercontent.com',
			'iosStandaloneAppClient' => '339145381410-96413lm5s0nfiadkug52dtlbp1pt04hj.apps.googleusercontent.com',
		);
		$access_token   = $request->get('access_token', false);
		$client_type    = $request->get("client_type", false);
		$first_name     = $request->get('first_name', false);
		$last_name      = $request->get('last_name', false);
//	    $gender         = $request->get('gender', false);
		$email          = $request->get('email', false);
		$pushToken      = $request->get('push_token', false);
		$device         = $request->get('device', 'android');

		$devices = ['ios', 'android'];
		if(!$device || !in_array($device, $devices)) {
			return response(['status' => '0', 'error' => 'DEVICE_IS_MISSING'], 400);
		}

		if(!$client_type || !array_key_exists($client_type, $CLIENT_ID)) {
			return response(['status' => '0', 'error' => 'CLIENT_TYPE_IS_MISSING'], 400);
		}

		if(!$access_token || !$first_name || !$last_name) {
			return response(['status' => '0', 'error' => 'LOGIN_FIELD_IS_MISSING'], 400);
		}

		$client = new Google_Client(['client_id' => $CLIENT_ID[$client_type]]);  // Specify the CLIENT_ID of the app that accesses the backend
		$payload = $client->verifyIdToken($access_token);
		if (!$payload || $payload['email_verified'] != true) {
			return response(['status' => '0', 'error' => 'GOOGLE_ACCOUNT_NOT_VALID'], 403);
		}

		$google_user_id = $payload['sub'];
		$google_email = $payload['email'];
		$user = User::where('email', $google_email)->first();
		$userNewRank = User::count() + 1;
		if(!$user) {
			$user = User::create([
				'first_name'  => $first_name,
				'last_name'   => $last_name,
				'gender'      => '-',
				'email'       => $email,
				'facebook_id' => null,
				'global_rank' => $userNewRank,
				'week_rank'   => $userNewRank,
				'lang'         => request()->get('language') == 'en' ? 'en' : 'he',
				'image_url'   => $payload['picture']
			]);
		}
		$user->update([
			'first_name'  => $first_name,
			'last_name'   => $last_name,
			'image_url'   => $payload['picture']
		]);

		if($pushToken) {
			$userPushTokens = $user->push_tokens()
				->where('device', '=', $device)
				->where('token', '=', $pushToken)->first();

			if(!$userPushTokens) {
				$user->push_tokens()->create([
					'device' => $device,
					'token' => $pushToken,
				]);
			}
		}

		// Reload user data (language default defined by DB)
		$user = User::findOrFail($user->id);

		return response(['status' => '1', 'user_id' => $user->id, 'rate_url' => Setting::first()->{strtoupper($device) . '_RATE_URL'}, 'lang' => $user->lang]);



	}

	public function facebook_login(Request $request)
	{
		$access_token   = $request->get('access_token', false);
		$first_name     = $request->get('first_name', false);
		$last_name      = $request->get('last_name', false);
		$gender         = $request->get('gender', false);
		$email          = $request->get('email', false);

		$pushToken      = $request->get('push_token', false);
		$device         = $request->get('device', 'android');

		$devices = ['ios', 'android'];
		if(!$device || !in_array($device, $devices)) {
			return response(['status' => '0', 'error' => 'DEVICE_IS_MISSING'], 400);
		}

		if(!$access_token || !$first_name || !$last_name || !$gender) {
			return response(['status' => '0', 'error' => 'LOGIN_FIELD_IS_MISSING'], 400);
		}

		$facebook_user_data = @json_decode(file_get_contents("https://graph.facebook.com/me?access_token=$access_token"), TRUE);
		if(!$facebook_user_data) {
			return response(['status' => '0', 'error' => 'FACEBOOK_ACCOUNT_NOT_VALID'], 403);
		}

		$facebook_user_id = $facebook_user_data['id'];
		$user = User::where('facebook_id', $facebook_user_id)->first();

		$userNewRank = User::count() + 1;
		if(!$user) {
			$user = User::create([
				'first_name'  => $first_name,
				'last_name'   => $last_name,
				'gender'      => $gender,
				'email'       => $email,
				'facebook_id' => $facebook_user_id,
				'global_rank' => $userNewRank,
				'week_rank'   => $userNewRank,
				'lang'         => request()->get('language') == 'en' ? 'en' : 'he',
				'image_url'   => "https://graph.facebook.com/v3.0/$facebook_user_id/picture"
			]);
		}
		$user->update([
			'first_name'  => $first_name,
			'last_name'   => $last_name,
			'gender'      => $gender,
			'email'       => $email,
			'facebook_id' => $facebook_user_id,
			'image_url'   => "https://graph.facebook.com/v3.0/$facebook_user_id/picture"
		]);

		if($pushToken) {
			$userPushTokens = $user->push_tokens()
				->where('device', '=', $device)
				->where('token', '=', $pushToken)->first();

			if(!$userPushTokens) {
				$user->push_tokens()->create([
					'device' => $device,
					'token' => $pushToken,
				]);
			}
		}

		// Reload user data (language default defined by DB)
		$user = User::findOrFail($user->id);

		return response(['status' => '1', 'user_id' => $user->id, 'rate_url' => Setting::first()->{strtoupper($device) . '_RATE_URL'}, 'lang' => $user->lang]);
	}

	public function getLogout()
	{
		Auth::logout();
		return Redirect::route('login');
	}

	public function showLogin()
	{
		if (Auth::user())
			return Redirect::route('welcome');

		// show the form
		return view('auth.login');
	}

	public function doLogin()
	{
		// validate the info, create rules for the inputs
		$rules = array(
			'email'    => 'required|email', // make sure the email is an actual email
			'password' => 'required|alphaNum|min:3' // password can only be alphanumeric and has to be greater than 3 characters
		);

		// run the validation rules on the inputs from the form
		$validator = Validator::make(Input::all(), $rules);

		// if the validator fails, redirect back to the form
		if ($validator->fails()) {
			return Redirect::to('login')
				->withErrors($validator) // send back all errors to the login form
				->withInput(Input::except('password')); // send back the input (not the password) so that we can repopulate the form
		} else {

			// create our user data for the authentication
			$userdata = array(
				'email'     => Input::get('email'),
				'password'  => Input::get('password')
			);

			// attempt to do the login
			if (Auth::attempt($userdata)) {

				// validation successful!
				// redirect them to the secure section or whatever
				// return Redirect::to('secure');
				// for now we'll just echo success (even though echoing in a controller is bad)
				return Redirect::route('welcome');

			} else {

				// validation not successful, send back to form
				return Redirect::route('login');

			}

		}
	}

}
