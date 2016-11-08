<?php

namespace App\Packages\Gougousis\Net;

/**
 * Implements monitoring functionality
 *
 * Extracts information about the status of servers, services or web applications.
 *
 * @author Alexandros Gougousis
 */
class Monitor
{
    /**
     * Timeout (is seconds) to be used with 'ping' command
     *
     * @var int
     */
    protected $ping_timeout;

    /**
     * Timeout (in seconds) to be used when opening a socket connection
     *
     * @var int
     */
    protected $portscan_timeout;

    /**
     * Timeout (in seconds) to be used when making an HTTP request with cURL
     *
     * @var int
     */
    protected $curl_timeout;

    public function __construct($ping_timeout = null, $portscan_timeout = null, $curl_timeout = null)
    {
        $this->ping_timeout = (empty($ping_timeout))? 2 : $ping_timeout;
        $this->portscan_timeout = (empty($portscan_timeout))? 3 : $portscan_timeout;
        $this->curl_timeout = (empty($curl_timeout))? 3 : $curl_timeout;
    }

    /**
     * Ping an IP
     *
     * @param string $ip
     * @param int $timeout
     * @return array
     */
    public function ping($ip, $timeout = null)
    {
        // Check if we need to override the default timeout
        $useTimeout = (empty($timeout))? $this->ping_timeout : $timeout ;

        // Ping only once with 3 sec time limit
        exec("ping -c 1 -W $useTimeout $ip", $out, $status);

        // In case response in empty
        $outString = implode('', $out);
        if (empty($outString)) {
            return array(
                'status'    =>  'off',
                'time'      =>  ''
            );
        }

        // In case the server is up
        if (stripos($outString, '0 received') === false) {
            $timeParts = explode('=', $out[count($out)-1]);
            $timeValues = explode('/', $timeParts[1]);

            return array(
                'status'    =>  'on',
                'time'      =>  $timeValues[1]
            );
        }

        // In case the server is down
        return array(
            'status'    =>  'off',
            'time'      =>  ''
        );
    }

    /**
     * Tries to open a socket connection
     *
     * @param string $protocol
     * @param int $port
     * @param string $ip
     * @param int $timeout
     * @return array
     */
    public function scanPort($protocol, $port, $ip, $timeout = null)
    {
        // Check if we need to override the default timeout
        $useTimeout = (empty($timeout))? $this->portscan_timeout : $timeout ;

        $start = microtime(true);
        try {
            $client = stream_socket_client("$protocol://$ip:$port", $errno, $errorMessage, $useTimeout);

            $end = microtime(true);
            if ($client === false) {
                $status = 'off';
            } else {
                $status = 'on';
            }
        } catch (\Exception $ex) {
            $end = microtime(true);
            $status = 'off';
        }

        return array(
            'status'    =>  $status,
            'time' =>  ($end-$start)
        );
    }

    /**
     * Checks the status of a URL by making a HEAD request
     *
     * @param string $url
     * @param int $timeout
     * @return array
     */
    public function checkStatus($url, $timeout = null)
    {
        // Check if we need to override the default timeout
        $useTimeout = (empty($timeout))? $this->curl_timeout : $timeout ;

        $start = microtime(true);

        // initializes curl session
        $curl=curl_init();
        // sets the URL to fetch
        curl_setopt($curl, CURLOPT_URL, $url);
        // sets the content of the User-Agent header
        //curl_setopt($ch, CURLOPT_USERAGENT, $agent);
        // We don't really want the headers
        curl_setopt($curl, CURLOPT_HEADER, false);
        curl_setopt($curl, CURLOPT_NOBODY, true);
        // return the transfer as a string
        curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
        // disable output verbose information
        curl_setopt($curl, CURLOPT_VERBOSE, false);
        // max number of seconds to allow cURL function to execute
        curl_setopt($curl, CURLOPT_TIMEOUT, $useTimeout);
        curl_exec($curl); // if we want the returned header we can assign the result value of this command
        // get HTTP response code
        $httpcode = curl_getinfo($curl, CURLINFO_HTTP_CODE);
        curl_close($curl);
        $end = microtime(true);

        if (($httpcode >=200)&&($httpcode<=399)) {
            $status = 'on';
        } else {
            $status = 'off';
        }

        return array(
            'status' =>  $status,
            'time'   =>  ($end-$start)   // in sec
        );
    }

    /**
     *
     * @param array $curl_sites
     * @param array $opts
     * @return string
     */
    public function curlMulti(array $curl_sites, array $opts = [])
    {
        // create array for curl handles
        $chs = [];
        // merge general curl options args with defaults
        $opts += [CURLOPT_CONNECTTIMEOUT => 3, CURLOPT_TIMEOUT => 3, CURLOPT_RETURNTRANSFER => 1];
        // create array for responses
        $responses = [];
        // init curl multi handle
        $mh = curl_multi_init();
        // create running flag
        $running = null;

        // cycle through requests and set up
        foreach ($curl_sites as $key => $request) {
            // init individual curl handle
            $chs[$key] = curl_init();
            // set url
            curl_setopt($chs[$key], CURLOPT_URL, $request['url']);
            // check for post data and handle if present
            if (isset($request['post_data'])) {
                curl_setopt($chs[$key], CURLOPT_POST, 1);
                curl_setopt($chs[$key], CURLOPT_POSTFIELDS, $request['post_array']);
            }
            // set opts
            curl_setopt_array($chs[$key], (isset($request['opts']) ? $request['opts'] + $opts : $opts));
            curl_multi_add_handle($mh, $chs[$key]);
        }

        do {
            // execute curl requests
            curl_multi_exec($mh, $running);
            // block to avoid needless cycling until change in status
            // (Blocks until there is activity on any of the curl_multi connections)
            curl_multi_select($mh);
        // check flag to see if we're done
        } while ($running > 0);

        // cycle through requests
        foreach ($chs as $key => $ch) {
            // handle error
            if (curl_errno($ch)) {
                $responses[$key] = ['status' => 'off', 'time' => ''];
            } else {
                // save successful response
                $info = curl_getinfo($ch);
                if (($info['http_code'] >=200)&&($info['http_code']<=399)) {
                    $status = 'on';
                } else {
                    $status = 'off';
                }
                $responses[$key] = ['status' => $status, 'time' => $info['total_time']];
            }
            // close individual handle
            curl_multi_remove_handle($mh, $ch);
        }
        // close multi handle
        curl_multi_close($mh);
        // return respones
        return $responses;
    }
}
