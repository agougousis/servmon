<?php

namespace App\Packages\Gougousis\Transformers;

use App\User;
use League\Fractal;

class UserBasicTransformer extends Fractal\TransformerAbstract
{
    public function transform(User $user)
    {
        return [
            'email'     =>  $user->email,
            'fullname' =>  $user->firstname." ".$user->lastname
        ];
    }
}
