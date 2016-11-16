<?php

namespace App\Packages\Gougousis\Transformers;

use League\Fractal;
use League\Fractal\Manager;
use League\Fractal\Resource\Collection as FractalCollection;

class MonitorableServerTransformer extends Fractal\TransformerAbstract
{
    public function transform($server)
    {
        $fractalManager = new Manager();

        $item = [
            'id'        =>  $server->id,
            'hostname'  =>  $server->hostname,
            'watch'     =>  $server->watch
        ];
        if (isset($server->services)) {
            $fractalCollection = new FractalCollection($server->services, new ServiceTransformer());
            $namespacedArray = $fractalManager->createData($fractalCollection)->toArray();
            $item['services'] = $namespacedArray['data'];
        }
        if (isset($server->webapps)) {
            $fractalCollection = new FractalCollection($server->webapps, new WebappTransformer());
            $namespacedArray = $fractalManager->createData($fractalCollection)->toArray();
            $item['webapps'] = $namespacedArray['data'];
        }
        return $item;
    }
}
