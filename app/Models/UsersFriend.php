<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class UsersFriend extends Model
{
    protected $fillable = ['user_id1', 'user_id2'];
}
