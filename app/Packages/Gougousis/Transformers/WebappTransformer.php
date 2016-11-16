<?php

namespace App\Packages\Gougousis\Transformers;

use League\Fractal;

class WebappTransformer extends Fractal\TransformerAbstract
{
    public function transform($webapp)
    {
        $item =  [
            'id'        =>  $webapp->id,
            'url'       =>  $webapp->url,
            'language'  =>  $webapp->language,
            'developer' =>  $webapp->developer,
            'server'    =>  $webapp->server,
            'contact'   =>  $webapp->contact,
            'watch'     =>  $webapp->watch
        ];
        if (isset($webapp->image)) {
            $item['image'] = $webapp->image;
        }
        if (isset($webapp->status)) {
            $item['status'] = $webapp->status;
        }
        if (isset($webapp->time)) {
            $item['time'] = $webapp->time;
        }
        return $item;
    }
}
