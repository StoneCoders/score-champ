<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class TopScorePlayer extends Model
{
    protected $fillable = ['name', 'name_he', 'goals', 'class', 'league_id'];
    protected $guarded = ['id','name'];
    protected $hidden = ['updated_at', 'created_at'];

    public function users() {
        return $this->belongsToMany(User::class, 'top_score_bets', 'top_score_player_id', 'user_id');
    }

    public function league()
    {
        return $this->belongsTo(League::class);
    }
}
