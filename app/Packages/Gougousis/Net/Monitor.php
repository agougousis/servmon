<?php

namespace App\Packages\Gougousis\Net;

class Monitor {
	
    public static function ping($ip,$timeout){

        // Ping only once with 3 sec time limit
        exec("ping -c 1 -W $timeout $ip",$out,$status);
        
        // Extract info
        $outString = implode('',$out);
        if(stripos($outString,'0 received') === false){
            $timeParts = explode('=',$out[count($out)-1]);
            $timeValues = explode('/',$timeParts[1]);
            
            return array(
                'status'    =>  'on',
                'time'      =>  $timeValues[1]
            );
        } else {
            return array(
                'status'    =>  'off',
                'time'      =>  ''
            );            
        }
    }
    
    public static function scanPort($protocol,$port,$ip,$timeout){
        
        $start = microtime(true);
        try {
            $client = stream_socket_client("$protocol://$ip:$port", $errno, $errorMessage,$timeout);

            $end = microtime(true);
            if ($client === false) {
                $status = 'off';
            } else {        
                $status = 'on';            
            }
        } catch(\Exception $ex){
            $end = microtime(true);
            $status = 'off';
        }                

        return array(
            'status'    =>  $status,
            'time' =>  ($end-$start)
        );
    }
    
    public static function checkStatus($url,$timeout){
        
        $start = microtime(true);
        
        // initializes curl session
        $curl=curl_init();
        // sets the URL to fetch
        curl_setopt ($curl, CURLOPT_URL,$url );
        // sets the content of the User-Agent header
        //curl_setopt($ch, CURLOPT_USERAGENT, $agent);
        // We don't really want the headers
        curl_setopt($curl, CURLOPT_HEADER, false);
        curl_setopt($curl,CURLOPT_NOBODY,true);
        // return the transfer as a string
        curl_setopt ($curl, CURLOPT_RETURNTRANSFER,true);
        // disable output verbose information
        curl_setopt ($curl,CURLOPT_VERBOSE,false);
        // max number of seconds to allow cURL function to execute
        curl_setopt($curl, CURLOPT_TIMEOUT, $timeout);
        curl_exec($curl); // if we want the returned header we can assign the result value of this command
        // get HTTP response code
        $httpcode = curl_getinfo($curl, CURLINFO_HTTP_CODE);
        curl_close($curl);
        $end = microtime(true);

        if(($httpcode >=200)&&($httpcode<=399)){
            $status = 'on';
        } else {
            $status = 'off';
        }
        
       return array(
           'status' =>  $status,
           'time'   =>  ($end-$start)   // in sec
       );

    }
    
    public static function curl_multi(array $curl_sites, array $opts = []){
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
    } while($running > 0);
    // cycle through requests
    foreach ($chs as $key => $ch) {
        // handle error
        if (curl_errno($ch)) {
            $responses[$key] = ['status' => 'off', 'time' => ''];
        } else {
            // save successful response
            $info = curl_getinfo($ch);
            if(($info['http_code'] >=200)&&($info['http_code']<=399)){
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