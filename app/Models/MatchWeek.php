<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class MatchWeek extends Model
{
    protected $fillable = [ 'title_en', 'title_he', 'league_id','api_id', 'start', 'end', 'created_at', 'updated_at' ];
    
    public function game()
    {
        return $this->hasMany(Game::class);
    }

    public function league()
    {
        return $this->belongsTo(League::class);
    }
}
