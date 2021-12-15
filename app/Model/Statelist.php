<?php

namespace App\Model;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Statelist extends Model
{   
	use SoftDeletes;
    protected $guarded=[];
    protected $dates = ['deleted_at'];
    protected $table = 'state_list';
}



