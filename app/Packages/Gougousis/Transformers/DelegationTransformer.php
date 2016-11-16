<?php

namespace App\Packages\Gougousis\Transformers;

use League\Fractal;

class DelegationTransformer extends Fractal\TransformerAbstract
{
    public function transform($delegation)
    {
        if (get_class($delegation) == 'App\Models\ServerDelegation') {
            $item = [
                'user_id'    =>  $delegation->user_id,
                'server_id'  =>  $delegation->server_id
            ];
        } else if (get_class($delegation) == 'App\Models\DomainDelegation') {
            $item = [
                'user_id'    =>  $delegation->user_id,
                'domain_id'  =>  $delegation->domain_id
            ];
        }
        return $item;
    }
}
