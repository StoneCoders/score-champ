<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class PushToken extends Model
{
    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'device',
        'token',
        'active',
    ];
    protected $table = 'push_tokens';

    public function user() {
        return $this->belongsTo(User::class, 'user_id', 'id');
    }
}
