<?php

namespace App\Packages\Gougousis\SSH;

use Illuminate\Support\ServiceProvider;

class SshHelperServiceProvider extends ServiceProvider
{
    public function register()
    {
        $this->app->singleton('sshhelper', function ($app) {
            // Instantiate the class
            $sshHelper = new SshHelper();
            return $sshHelper;
        });
    }
}
