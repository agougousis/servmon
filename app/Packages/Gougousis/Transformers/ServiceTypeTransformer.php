<?php

namespace App\Packages\Gougousis\Transformers;

use App\Models\ServiceType;
use League\Fractal;

class ServiceTypeTransformer extends Fractal\TransformerAbstract
{
    public function transform(ServiceType $service_type)
    {
        $item = [
            'codename'     =>  $service_type->codename,
            'title'        =>  $service_type->title,
            'image'        =>  $service_type->image,
            'default_port' =>  $service_type->default_port
        ];

        return $item;
    }
}
