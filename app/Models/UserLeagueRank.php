<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class UserLeagueRank extends Model
{
    protected $fillable = [
        'user_id',
        'league_id',
        'global_rank',
        'global_points',
        'global_hits',
        'global_exact_hits',
        'week_points',
        'week_rank',
        'week_hits',
        'week_exact_hits',
    ];
    
    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
