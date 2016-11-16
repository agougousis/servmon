<?php

namespace App\Packages\Gougousis\Transformers;

use League\Fractal;

class DatabaseTransformer extends Fractal\TransformerAbstract
{
    public function transform($database)
    {
        return [
            'id'            =>  $database->id,
            'dbname'        =>  $database->dbname,
            'server'        =>  $database->server,
            'type'          =>  $database->type,
            'related_webapp'=>  $database->related_webapp
        ];
    }
}
