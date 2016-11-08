<?php

namespace App\Packages\Gougousis\SSH\Facades;

use Illuminate\Support\Facades\Facade;

class SshHelperFacade extends Facade
{

    /**
     * Get the registered name of the component.
     *
     * @return string
     */
    protected static function getFacadeAccessor()
    {
        return 'sshhelper';
    }
}
