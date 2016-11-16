<?php

namespace App\Packages\Gougousis\Transformers;

use App\Models\DatabaseType;
use League\Fractal;

class DatabaseTypeTransformer extends Fractal\TransformerAbstract
{
    public function transform(DatabaseType $database_type)
    {
        $item = [
            'codename'     =>  $database_type->codename,
            'title'        =>  $database_type->title,
            'image'        =>  $database_type->image
        ];

        return $item;
    }
}
