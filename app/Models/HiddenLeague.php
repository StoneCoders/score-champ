<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class HiddenLeague extends Model
{
    protected $hidden = [ 'user_id' ];
    protected $fillable = [ 'league_id', 'user_id', 'updated_at', 'created_at' ];
}
