<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Push extends Model
{
    protected $fillable = [ 'type', 'already_started', 'finished', 'title_he', 'title', 'msg_he', 'msg', 'route' ];
}
