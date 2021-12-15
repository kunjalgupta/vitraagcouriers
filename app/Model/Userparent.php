<?php

namespace App\Model;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Userparent extends Model
{   
	use SoftDeletes;
    protected $guarded=[];
    protected $dates = ['deleted_at'];
    protected $table = 'user_parent';
}
