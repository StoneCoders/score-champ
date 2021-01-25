<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class WinningTeam extends Model
{
    protected $fillable = ['name', 'name_he', 'isInGame', 'class', 'league_id', 'team_color1', 'team_color2', 'team_flag', 'api_id'];
    protected $guarded = ['id','name'];
    protected $hidden = ['updated_at', 'created_at'];

    public function users()
    {
        return $this->belongsToMany(User::class, 'winning_team_bets', 'winning_team_id', 'user_id');
    }

    public function league()
    {
        return $this->belongsTo(League::class);
    }
}
