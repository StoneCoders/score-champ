<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Group extends Model
{

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'owner_id',
        'name',
        'image',
        'api_id',
        'created_at',
        'updated_at',
    ];
    public function owner() {
        return $this->belongsTo(User::class, 'owner_id', 'id');
    }

    public function users() {
        return $this->belongsToMany(User::class, 'group_users', 'group_id', 'user_id');
    }
}
