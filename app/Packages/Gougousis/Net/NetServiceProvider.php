<?php

namespace App\Packages\Gougousis\Net;

use Config;
use Illuminate\Support\ServiceProvider;

class NetServiceProvider extends ServiceProvider
{
    public function register()
    {
        $this->app->singleton('monitor', function ($app) {
            
            // Use the timeout defined in the /config/network.php for default values
            $ping_timeout = Config::get('network.ping_timeout');
            $portscan_timeout = Config::get('network.portscan_timeout');
            $curl_timeout = Config::get('network.curl_timeout');

            // Instantiate the class
            $monitor = new Monitor($ping_timeout, $portscan_timeout, $curl_timeout);
            return $monitor;
        });
    }
}
