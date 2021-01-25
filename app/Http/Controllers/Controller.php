<?php

namespace App\Http\Controllers;

use App\App;
use Illuminate\Http\Request;
use Illuminate\Foundation\Bus\DispatchesJobs;
use Illuminate\Routing\Controller as BaseController;
use Illuminate\Foundation\Validation\ValidatesRequests;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;

class Controller extends BaseController
{
    use AuthorizesRequests, DispatchesJobs, ValidatesRequests;

    protected $request;
    protected $imagesPath;
    public function __construct(Request $request) {
        $this->request= $request;
        if($this->request->get('test', false)) {
            App::set_test();
        }

        $this->imagesPath = public_path('images');
    }
}