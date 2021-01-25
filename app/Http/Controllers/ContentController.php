<?php

namespace App\Http\Controllers;

use App\Models\Setting;
use Illuminate\Support\Facades\Input;

class ContentController extends Controller
{
    public function rules()
    {
        $rules = Input::get('lang') == 'en'
            ? Setting::first()->html_rules_en
            : Setting::first()->html_rules_he;

        return nl2br($rules);
    }

    public function emptyGroup()
    {
        $rules = Input::get('lang') == 'en'
            ? Setting::first()->html_empty_group_en
            : Setting::first()->html_empty_group_he;

        return nl2br($rules);
    }

}
