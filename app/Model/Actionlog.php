<?php

namespace App\Model;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Actionlog extends Model
{   
	use SoftDeletes;
    protected $guarded=[];
    protected $dates = ['deleted_at'];
    protected $table = 'action_logs';
}



