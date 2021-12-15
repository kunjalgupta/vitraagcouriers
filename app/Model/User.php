<?php

namespace App\Model;

use Hash;
use Tymon\JWTAuth\Contracts\JWTSubject;

use Illuminate\Notifications\Notifiable;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Database\Eloquent\SoftDeletes;


class User extends Authenticatable implements JWTSubject
{
    use Notifiable;
    use SoftDeletes;
    protected $guarded=[];
    protected $dates = ['deleted_at'];
    protected $table = 'users';

    protected $hidden = ['password'];

    public function getJWTIdentifier()
    {
        return $this->getKey();
    }

    public function getJWTCustomClaims()
    {
        return [];
    }

    public function setPasswordAttribute($password)
    {
    	$this->attributes['password']=Hash::make($password);
    }

    public function isCompany()
    {
        return $this->role==105 ? true:false; 
    }
    public function isHub()
    {
        return $this->role==104 ? true:false; 
    }
    public function isFranchise()
    {
        return $this->role==103 ? true:false; 
    }
    public function isMiniDealer()
    {
        return $this->role==102 ? true:false; 
    }
}
