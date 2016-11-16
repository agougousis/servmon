<?php

namespace App\Packages\Gougousis\Transformers;

use App\Models\WebappType;
use League\Fractal;

class WebappTypeTransformer extends Fractal\TransformerAbstract
{
    public function transform(WebappType $webapp_type)
    {
        $item = [
            'codename'     =>  $webapp_type->codename,
            'title'        =>  $webapp_type->title,
            'image'        =>  $webapp_type->image
        ];

        return $item;
    }
}
