<?php

namespace App\Packages\Gougousis\Transformers;

use League\Fractal;
use League\Fractal\Manager;
use League\Fractal\Resource\Collection as FractalCollection;

class InfoTransformer extends Fractal\TransformerAbstract
{
    public function transform($info)
    {
        $fractalManager = new Manager();
        $item = [];

        if (isset($info->service)) {
            $fractalCollection = new FractalCollection($info->service, new ServiceTypeTransformer());
            $namespacedArray = $fractalManager->createData($fractalCollection)->toArray();
            $item['service'] = $namespacedArray['data'];
        }
        if (isset($info->webapp)) {
            $fractalCollection = new FractalCollection($info->webapp, new WebappTypeTransformer());
            $namespacedArray = $fractalManager->createData($fractalCollection)->toArray();
            $item['webapp'] = $namespacedArray['data'];
        }
        if (isset($info->database)) {
            $fractalCollection = new FractalCollection($info->database, new DatabaseTypeTransformer());
            $namespacedArray = $fractalManager->createData($fractalCollection)->toArray();
            $item['database'] = $namespacedArray['data'];
        }

        return $item;
    }
}
