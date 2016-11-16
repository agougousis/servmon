<?php

namespace App\Packages\Gougousis\Transformers;

use League\Fractal;

class DomainTransformer extends Fractal\TransformerAbstract
{
    public function transform($domain)
    {
        return [
            'id'        =>  $domain->id,
            'parent_id' =>  $domain->parent_id,
            'node_name' =>  $domain->node_name,
            'full_name' =>  $domain->full_name,
            'fake'      =>  $domain->fake
        ];
    }
}
