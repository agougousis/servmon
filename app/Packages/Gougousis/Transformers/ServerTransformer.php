<?php

namespace App\Packages\Gougousis\Transformers;

use League\Fractal;
use League\Fractal\Manager;
use League\Fractal\Resource\Collection as FractalCollection;

class ServerTransformer extends Fractal\TransformerAbstract
{
    public function transform($server)
    {
        $fractalManager = new Manager();

        $item = [
            'id'        =>  $server->id,
            'ip'        =>  $server->ip,
            'os'        =>  $server->os,
            'hostname'  =>  $server->hostname,
            'supervisor_email'   =>  $server->supervisor_email,
            'domain'    =>  $server->domain
        ];
        if (isset($server->full_name)) {
            $item['domain_name'] = $server->full_name;
        }
        if (isset($server->status)) {
            $item['status'] = $server->status;
        }
        if (isset($server->response_time)) {
            $item['response_time'] = $server->response_time;
        }
        if (isset($server->service_types)) {
            $item['service_types'] = $server->service_types;
        }
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
        if (isset($server->databases)) {
            $fractalCollection = new FractalCollection($server->databases, new DatabaseTransformer());
            $namespacedArray = $fractalManager->createData($fractalCollection)->toArray();
            $item['databases'] = $namespacedArray['data'];
        }
        return $item;
    }
}
