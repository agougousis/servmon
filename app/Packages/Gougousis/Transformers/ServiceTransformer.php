<?php

namespace App\Packages\Gougousis\Transformers;

use League\Fractal;

class ServiceTransformer extends Fractal\TransformerAbstract
{
    public function transform($service)
    {
        $item = [
            'id'        =>  $service->id,
            'server'    =>  $service->server,
            'stype'     =>  $service->stype,
            'port'      =>  $service->port,
            'version'   =>  $service->version,
            'watch'     =>  $service->watch
        ];
        if (isset($service->status)) {
            $item['status'] = $service->status;
        }
        if (isset($service->time)) {
            $item['time'] = $service->time;
        }
        return $item;
    }
}
