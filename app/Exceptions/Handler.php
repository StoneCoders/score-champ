<?php

namespace App\Exceptions;

use App\App;
use App\Models\Log;
use Exception;
use Illuminate\Support\Facades\Input;
use Illuminate\Support\Facades\Route;
use Illuminate\Validation\ValidationException;
use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Symfony\Component\HttpKernel\Exception\HttpException;
use Illuminate\Foundation\Exceptions\Handler as ExceptionHandler;

class Handler extends ExceptionHandler
{
    /**
     * A list of the exception types that should not be reported.
     *
     * @var array
     */
    protected $dontReport = [
        AuthorizationException::class,
        HttpException::class,
        ModelNotFoundException::class,
        ValidationException::class,
    ];

    /**
     * Report or log an exception.
     *
     * This is a great spot to send exceptions to Sentry, Bugsnag, etc.
     *
     * @param  \Exception  $e
     * @return void
     */
    public function report(Exception $e)
    {
        if ($this->shouldReport($e)) {

            app('sentry')->user_context(array(
                'user_id' => App::get_user() ? App::get_user()->id : NULL,
                'request' => request()->all(),
            ));

            app('sentry')->captureException($e);
        }

        if (Route::getCurrentRoute()) {
//            Log::create([
//                'type'   => 'error',
//                'action' => Route::getCurrentRoute()->getActionName(),
//                'data'   => json_encode([
//                    'input_all'      => Input::all(),
//                    'exception_data' => [
//                        'message' => $e->getMessage(),
//                        'file'    => $e->getFile(),
//                        'line'    => $e->getLine(),
//                        'trace'   => $e->getTraceAsString(),
//                    ]
//                ]),
//            ]);
        }

        parent::report($e);
    }

    /**
     * Render an exception into an HTTP response.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Exception  $e
     * @return \Illuminate\Http\Response
     */
    public function render($request, Exception $e)
    {
        return parent::render($request, $e);
    }
}
