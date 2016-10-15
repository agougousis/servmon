<?php

namespace App;

use Illuminate\Foundation\Auth\User as Authenticatable;

class User extends Authenticatable
{
    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'firstname','lastname', 'email', 'password'
    ];

    /**
     * The attributes excluded from the model's JSON form.
     *
     * @var array
     */
    protected $hidden = [
        'password', 'remember_token',
    ];

    public static function getList()
    {
        return User::select('id','firstname','lastname','email','activated','superuser','last_login','created_at')->orderBy('lastname','ASC')->get();
    }

    public static function getBasicInfoList()
    {
        return User::select('email','firstname','lastname')->get();
    }

    public static function findByEmail($email)
    {
        return User::where('email',$email)->first();
    }

}
