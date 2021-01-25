<?php

namespace App\Http\Middleware;

use App\App;
use Log;
use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Input;
use Illuminate\Support\Facades\Route;

class LogRequest
{
    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure  $next
     * @return mixed
     */
    public function handle(Request $request, Closure $next)
    {
        $response = $next($request);

        $user = App::get_user();
        Log::useDailyFiles(storage_path().'/logs/log_requests.log');
        Log::info(json_encode([
            'type'   => 'request',
            'action' => Route::getCurrentRoute()->getActionName(),
            'data'   => json_encode(Input::all()),
            'user_id' => $user ? $user->id : NULL,
        ]));

        return $response;
    }
}
