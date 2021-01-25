<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class SchedulerPush extends Model
{
    protected $table='scheduler_pushes';
    protected $fillable = [ 'msg','sent_to_pushes_table','type','title_he','msg_he','title','time_to_send', 'route'];
}
