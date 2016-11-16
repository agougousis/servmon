<?php

namespace App\Packages\Gougousis\Transformers;

use App\User;
use League\Fractal;

class UserTransformer extends Fractal\TransformerAbstract
{
    public function transform(User $user)
    {
        return [
            'id'        =>  $user->id,
            'email'     =>  $user->email,
            'firstname' =>  $user->firstname,
            'lastname'  =>  $user->lastname,
            'activated' =>  $user->activated,
            'superuser' =>  $user->superuser,
            'last_login'=>  $user->last_login
        ];
    }
}
