<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;

use Mail;
use Monitor;
use App\Models\SystemLog;
use App\Models\Server;
use App\Models\Service;
use App\Models\Webapp;
use App\Models\Delay;

class ScheduledMonitoring extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'monitor:scheduled';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Monitor the status of selected items';

    /**
     * Create a new command instance.
     *
     * @return void
     */
    public function __construct()
    {
        parent::__construct();
    }

    /**
     * Execute the console command.
     *
     * @return mixed
     */
    public function handle()
    {
        try {
            $notificationsList = array();

            $start = microtime(true);

            // Monitor all the items that are being watched
            foreach (Server::toWatch() as $server) {
                $this->checkServerStatus($server, $notificationsList);
            }

            $end = microtime(true);

            $this->logMonitoringTime($start,$end);

            // Send all the notifications
            foreach ($notificationsList as $email => $off_events) {
                $this->sendNotificationsToRecipient($email, $off_events);
            }
        } catch (Exception $ex) {
            $log = new SystemLog();
            $log->category = 'error';
            $log->message = "Mail could not be sent! Error message: ".$ex->getMessage();
            $log->when = date("Y-m-d H:i:s");
            $log->save();
        }
    }

    /**
     * Checks server status
     *
     * The status of services and webapps is checked only in the server is up
     *
     * @param array $server
     * @param array $notificationsList
     */
    protected function checkServerStatus($server, &$notificationsList)
    {
        // Check server status with 'ping'
        $pingResult = Monitor::ping($server['ip']);
        if (!$pingResult['status']) {   // Server down! Notify the user.
            $notificationsList[$server->supervisor_email][] =   array(
                'type'  =>  'server',
                'name'  =>  $server->hostname
            );
        } else {    // Server is up! Check the services and webapps.
            $this->checkServicesStatus($server, $notificationsList);
            $this->checkWebappsStatus($server, $notificationsList);
        }
    }

    /**
     * Log the time monitoring took
     *
     * @param int $start
     * @param int $end
     */
    protected function logMonitoringTime($start, $end)
    {
        $delay = new Delay();
        $delay->category = 'scheduled';
        $delay->duration = number_format(($end-$start), 5);
        $delay->when = date("Y-m-d H:i:s");
        $delay->save();
    }

    /**
     * Checks the status of service items that are on a specific server
     * and are being watched/monitored.
     *
     * @param Server $server
     * @param array $notifications Notifications are grouped by recipient.
     */
    protected function checkServicesStatus($server, &$notifications)
    {
        $services = Service::toWatchFromServer($server->id);

        foreach ($services as $service) {
            $result = Monitor::scanPort('tcp', $service->port, $server->ip);
            if ($result['status'] == 'off') {
                $notifications[$server->supervisor_email][] =   array(
                    'type'  =>  'service',
                    'name'  =>  $service->type
                );
            }
        }
    }

    /**
     * Checks the status of webapp items that are on a specific server
     * and are being watched/monitored.
     *
     * @param Server $server
     * @param array $notifications Notifications are grouped by recipient.
     */
    protected function checkWebappsStatus($server, &$notifications)
    {
        $webapps = Webapp::toWatchFromServer($server->id);
        foreach ($webapps as $webapp) {
            $result = Monitor::checkStatus($webapp->url);
            if ($result['status'] == 'off') {
                $notifications[$webapp->supervisor_email][] =   array(
                    'type'  =>  'webapp',
                    'name'  =>  $webapp->url
                );
            }
        }
    }

    /**
     * Sends a single email for all notifications with the same email recipient.
     *
     * @param string $recipientEmail
     * @param array $eventsList
     */
    protected function sendNotificationsToRecipient($recipientEmail, $eventsList)
    {
        // Send the email
        $data['body'] = $this->buildNotificationsMessage($eventsList);

        try {
            Mail::send(['html' => 'emails.monitoring'], $data, function ($message) use ($recipientEmail) {
                $message->to($recipientEmail)->subject('Monitoring report');
            });
        } catch (Exception $ex) {
            $log = new SystemLog();
            $log->category = 'error';
            $log->message = "Mail could not be sent! Error message: ".$ex->getMessage();
            $log->when = date("Y-m-d H:i:s");
            $log->save();
        }
    }

    /**
     * Builds an HTML message from a list of notification events
     *
     * @param array $eventsList
     * @return string
     */
    protected function buildNotificationsMessage($eventsList)
    {
        $body = '';

        foreach ($eventsList as $item) {
            switch ($item['type']) {
                case 'server':
                    $body .= "<span style='color: red'>Server <strong>".$item['name']."</strong> is DOWN!</span><br>";
                    break;
                case 'service':
                    $body .= "<span style='color: blue'>Service <strong>".$item['name']."</strong> is DOWN!</span><br>";
                    break;
                case 'webapp':
                    $body .= "<span style='color: black'>Webapp <strong>".$item['name']."</strong> is DOWN!</span><br>";
                    break;
            }
        }

        return $body;
    }
}
