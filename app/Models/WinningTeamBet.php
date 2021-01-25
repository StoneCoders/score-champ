<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class WinningTeamBet extends Model
{
    protected $fillable = ['user_id', 'winning_team_id'];
}
