<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class TopScoreBet extends Model
{
    protected $fillable = ['top_score_player_id', 'user_id'];
}
