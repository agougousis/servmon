<?php

namespace App\Packages\Gougousis\Net\Facades;

use Illuminate\Support\Facades\Facade;

class MonitorFacade extends Facade
{

    /**
     * Get the registered name of the component.
     *
     * @return string
     */
    protected static function getFacadeAccessor()
    {
        return 'monitor';
    }
}
