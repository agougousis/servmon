<?php

namespace App\Packages\Gougousis\Transformers;

use App\Models\Server;
use League\Fractal;

class DomainListTransformer extends Fractal\TransformerAbstract
{
    public function transform($domain)
    {
        return [
            'full_name' =>  $domain->full_name,
            'depth'     =>  $domain->depth,
            'servers'   =>  Server::getByDomain($domain->id)
        ];
    }
}
