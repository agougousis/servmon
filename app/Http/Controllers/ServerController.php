<?php

namespace App\Http\Controllers;

use DB;
use Auth;
use Input;
use Monitor;
use App\Models\Server;
use App\Models\Service;
use App\Models\Webapp;
use App\Models\Database;
use App\Models\ServerDelegation;
use App\Models\Domain;
use Illuminate\Http\Request;
use App\Http\Controllers\RootController;
use phpseclib\Net\SSH2;

/**
 * Implements functionality related to server items
 *
 * @license MIT
 * @author Alexandros Gougousis
 */
class ServerController extends RootController
{

    protected function identifySshError($message){
        if (strpos($message,'getaddrinfo failed') === false){
            if (strpos($message,'Connection refused') === false){
                if (strpos($message,'No route to host') === false){
                    if (strpos($message,'Permission denied') === false){
                        return "The cause of this error could not be identified!";
                    }
                    return "Please check if password authentication is enabled on the server.";
                }
                return "Please check if the port is correct and the server is reachable through this subnet.";
            }
            return "Please check the provided username and password.";
        }
        return "Please check the validity of the hostname.";
    }

    public function snapshot(Request $request, $server_id) {

        $form = $request->input();

        // Form validation
        $errors = $this->loadValidationErrors('validation.server_snapshot', $form, [], null);
        if (!empty($errors)) {
            DB::rollBack();
            return response()->json(['errors' => $errors])->setStatusCode(400, 'Invalid credentials!');
        }

        $server = Server::find($server_id);

        // Check if server ID exists
        if (empty($server)) {
            return response()->json(['errors'=>[]])->setStatusCode(404, 'The specified server was not found!');
        }

        $domain = Domain::find($server->domain);
        $full_server_name = $server->hostname.".".$domain->full_name;

        try {
            $port = 22;
            $username = $form['sshuser'];
            $password = $form['sshpass'];

            $services = [];
            $errors = [];

            $start = microtime(true);

            $ssh = new SSH2($full_server_name);
            if (!$ssh->login($username,$password)) {
                return response()->json(['errors'=>[]])->setStatusCode(500,'SSH login failed! Please make sure that the server is reachable from this machine and the provided username and password are correct.');
            }

            /*******************
             * Get disk usage  *
             *******************/

            // Block usage
            $df_blocks_output = $ssh->exec('df -h');
            $df_bloks_lines = preg_split('/[\n]/',$df_blocks_output);
			$df_blocks = array();
            foreach($df_bloks_lines as $line){
                if(startsWithString($line,'/dev/')){
                    $line_parts = preg_split('/\s+/',$line);
                    $df_blocks[] = array(
                        'disk_name' =>  $line_parts[0],
                        'usage'     =>  trim($line_parts[4],'%'),
                        'mount_point'   =>  $line_parts[5]
                    );
                }
            }

            // Inodes usage
            $df_inodes_output = $ssh->exec('df -h -i');
            $df_inodes_lines = preg_split('/[\n]/',$df_inodes_output);
            $df_inodes = array();
            foreach($df_inodes_lines as $line){
                if(startsWithString($line,'/dev/')){
                    $line_parts = preg_split('/\s+/',$line);
                    $df_inodes[] = array(
                        'disk_name' =>  $line_parts[0],
                        'usage'     =>  trim($line_parts[4],'%'),
                        'mount_point'   =>  $line_parts[5]
                    );
                }
            }

            $count_processors = trim($ssh->exec("grep 'model name' /proc/cpuinfo | wc -l"));

            /**********************
             * Get server uptime  *
             **********************/
            $uptime_output = $ssh->exec('uptime');
            // Get the uptime
            $line_parts = explode(',',$uptime_output);
            $uptime_parts = explode('up',$line_parts[0]);
            $uptime = trim($uptime_parts[1]);
            // Get number of logged in users
            $logged_in = trim($line_parts[2]);

            /*****************
             * Get CPU load  *
             *****************/
            $line_parts = explode('load average:',$uptime_output);
            $cpu_load_parts = explode(',',$line_parts[1]);
            // For load average meaning refer to: http://www.howtogeek.com/194642/understanding-the-load-average-on-linux-and-other-unix-like-systems/
            $last5min_load = number_format(($cpu_load_parts[0]/$count_processors),2);
            $last10min_load = number_format(($cpu_load_parts[1]/$count_processors),2);

            /*********************
             * Get memory usage  *
             *********************/
            $memory = $ssh->exec('free');
            $memory_lines = preg_split('/[\n]/',$memory);
            // Get total RAM from second line
            $line_numbers = explode(':',$memory_lines[1]);
            $numbers = preg_split('/\s+/',trim($line_numbers[1]));
            $total_memory = trim($numbers[0]);
            $total_memory_text = $this->addMemoryUnits($total_memory);
            // Get free memory from third line
            // For free memory calculation refer to: http://www.linuxnix.com/find-ram-size-in-linuxunix/
            $line_numbers = explode(':',$memory_lines[2]);
            $numbers = preg_split('/\s+/',trim($line_numbers[1]));
            $free_memory = trim($numbers[1]);
            $free_memory_text = $this->addMemoryUnits($free_memory);

            /*********************************
             * Get list of network services  *
             *********************************/
            $ssh->write("sudo lsof -i -n -P\n");
            $template = "/password\sfor\s$username:|".$username."@".$server->hostname.":~\$/";
            // Read until you see the password prompt or the normal prompt
            $sudo_response = $ssh->read($template,2);
            if(preg_match("/password\sfor/",$sudo_response)){
                // In case of password prompt, give the password
                $ssh->write("mhdenER0-\n");
                // Take into account the 3 sec default delay of linux
                // (it is there to prevent brute-force attacks)
                $ssh->setTimeout(3);
                // Read until you see another prompt. We expect to see a normal prompt
                // but, if the password is wrong, it can be a password prompt.
                $lsof_output = $ssh->read($template,2);
                $lsof_lines = preg_split('/[\n]/',$lsof_output);
                unset($lsof_lines[count($lsof_lines)-1]); // remove prompt line
                unset($lsof_lines[1]); // remove header line
                unset($lsof_lines[0]); // remove empty line
                foreach($lsof_lines as $line){
                        if(preg_match("/\s\(LISTEN\)/",$line)){
                                $columns = preg_split('/\s+/',trim($line));
                                $command = $columns[0];
                                $user = $columns[2];
                                $ipType = $columns[4];
                                $protocol = $columns[7];
                                $bind = $columns[8];
                                if(preg_match("/([0-9\.]+|\*):([0-9]+)/",$bind,$matches)){
                                        $address = $matches[1];
                                        $port = $matches[2];
                                        $services[] = compact('command','user','ipType','protocol','port','address');
                                }
                        }
                }

                $toJson = function($item){
                        return json_encode($item);
                };
                $fromJson = function($item){
                        return json_decode($item);
                };

                // Remove duplicates. Array items are compared as JSON strings.
                $temp = array_unique(array_map($toJson,$services));
                sort($temp);
                $services = array_map($fromJson,$temp);
            } else {
                    $errors[] = "Password prompt not found after sudo commandn was issued!";
            }

            $end = microtime(true);

            /************************
             * Build HTTP response  *
             ************************/
            $response = compact('uptime',
                                'count_processors',
                                'last5min_load',
                                'last10min_load',
                                'total_memory',
                                'total_memory_text',
                                'free_memory',
                                'free_memory_text',
                                'df_blocks',
                                'df_inodes',
                                'services',
                                'errors');

            return response()->json($response)->setStatusCode(200, 'ok');

        } catch(\RuntimeException $ex) {
            $message = $ex->getMessage();
            $reason = $this->identifySshError($message);
            return response()->json(['errors'=>[]])->setStatusCode(500,"SSH login failed! ".$reason);
        } catch (Exception $ex) {
            return response()->json(['errors'=>[]])->setStatusCode(500,$ex->getMessage());
        }
    }

    /**
     * Adds units to an integer, expressing an amount of memory
     *
     * @param int $memoryInKilobytes
     * @return string
     */
    protected function addMemoryUnits($memoryInKilobytes)
    {
            if ($memoryInKilobytes < 1000) {
                    return number_format($memoryInKilobytes,2)." KB";
            } else if (($memoryInKilobytes/1000) < 1000) {
                    return number_format(($memoryInKilobytes/1000),2)." MB";
            } else {
                    return number_format(($memoryInKilobytes/1000000),2)." GB";
            }
    }

    /**
     * Returns a list of servers
     *
     * @uses $_GET['mode']
     * @return Response
     */
    public function search()
    {
        $mode = (Input::has('mode'))? Input::get('mode') : 'mine';

        switch ($mode) {
            case 'mine':
                $servers = Server::allUserServers(Auth::user()->id);
                break;
            default:
                return response()->json(['errors' => array()])->setStatusCode(400, 'Invalid search mode!');
                break;
        }

        return response()->json($servers)->setStatusCode(200, '');
    }

    /**
     * Deletes a server item
     *
     * All services, webapps and databases on this server, as well as delegations
     * related to this server wil be deleted, too.
     *
     * @param int $server_id
     * @return Response
     */
    public function delete($server_id)
    {
        $server = Server::find($server_id);

        // Check if server ID exists
        if (empty($server)) {
            return response()->json(['errors'=>[]])->setStatusCode(404, 'The specified server was not found!');
        }

        // Access control
        if (!$this->hasPermission('server', $server->domain, 'delete', $server->id)) {
            return response()->json(['errors' => []])->setStatusCode(403, 'You are not allowed to delete this server!');
        }

        DB::beginTransaction();
        try {
            // Delete databases on this server
            Database::deleteByServerId($server_id);

            // Delete webapps on this server
            Webapp::deleteByServerId($server_id);

            // Delete services on this server
            Service::deleteByServerId($server_id);

            // Delete server delegations
            ServerDelegation::deleteByServerId($server_id);

            // Delete the server
            $server->delete();
        } catch (Exception $ex) {
            DB::rollBack();
                $this->logEvent('Server deletion failed! Error: '.$ex->getMessage(), 'error');
                return response()->json(['errors' => []])->setStatusCode(500, 'Server deletion failed. Check system logs.');
        }

        DB::commit();
        return response()->json([])->setStatusCode(200, 'Server(s) deleted successfully');
    }

    /**
     * Updates server items
     *
     * @param Request $request
     * @return Response
     */
    public function update(Request $request)
    {
        // Retrieve nodes array from JSON
        $servers = $request->input('servers');
        $server_num = count($servers);

        // Validate the data for each node
        $errors = array();
        $updated = array();
        $index = 0;
        DB::beginTransaction();
        foreach ($servers as $server) {
            try {
                // Form validation
                $errors = $this->loadValidationErrors('validation.server_update', $server, $errors, $index);
                if (!empty($errors)) {
                    DB::rollBack();
                    return response()->json(['errors' => $errors])->setStatusCode(400, 'Server validation failed');
                }

                $updatedServer = Server::find($server['serverId']);

                // Access control
                if (!$this->hasPermission('server', $updatedServer->domain, 'update', $updatedServer->id)) {
                    DB::rollBack();
                    return response()->json(['errors' => []])->setStatusCode(403, 'You are not allowed to update this server!');
                }

                // Try to store each node
                $updatedServer->hostname = $server['hostname'];
                $updatedServer->ip = $server['ip'];
                $updatedServer->os = $server['os'] ;
                $updatedServer->save();
                $updated[] = $updatedServer;
            } catch (Exception $ex) {
                DB::rollBack();
                $this->logEvent('Server update failed! Error: '.$ex->getMessage(), 'error');
                return response()->json(['errors' => []])->setStatusCode(500, 'Server update failed. Check system logs.');
            }

            $index++;
        }

        DB::commit();
        return response()->json($updated)->setStatusCode(200, $server_num.' server(s) updated.');
    }

    /**
     * Adds new server items to domains
     *
     * @param Request $request
     * @return Response
     */
    public function create(Request $request)
    {
        // Retrieve nodes array from JSON
        $servers = $request->input('servers');
        $server_num = count($servers);

        // Validate the data for each node
        $errors = array();
        $created = array();
        $index = 0;
        DB::beginTransaction();
        foreach ($servers as $server) {
            try {
                // Form validation
                $errors = $this->loadValidationErrors('validation.server_create', $server, $errors, $index);
                if (!empty($errors)) {
                    DB::rollBack();
                    return response()->json(['errors' => $errors])->setStatusCode(400, 'Server validation failed');
                }

                $domain = Domain::findByFullname($server['domain']);

                // Access control
                if (!$this->hasPermission('server', $domain->id, 'create', null)) {
                    DB::rollBack();
                    return response()->json(['errors' => []])->setStatusCode(403, 'You are not allowed to create servers on this domain!');
                }

                // Try to store each node
                $newServer = new Server();
                $newServer->hostname = $server['hostname'];
                $newServer->domain = $domain->id;
                $newServer->ip = $server['ip'];
                $newServer->os = $server['os'] ;
                $newServer->save();

                $created[] = $newServer;
            } catch (Exception $ex) {
                DB::rollBack();
                $this->logEvent('Server creation failed! Error: '.$ex->getMessage(), 'error');
                return response()->json(['errors' => []])->setStatusCode(500, 'Server creation failed. Check system logs.');
            }

            $index++;
        }

        DB::commit();
        return response()->json($created)->setStatusCode(200, $server_num.' server(s) added.');
    }

    /**
     * Returns information about a specific server
     *
     * @param int $serverId
     * @return Response
     */
    public function read($serverId)
    {
        $server = Server::find($serverId);

        // Check if server exists
        if (empty($server)) {
            return response()->json(['errors'=>[]])->setStatusCode(404, 'The specified server was not found!');
        }

        // Access control
        if (!$this->hasPermission('server', $server->domain, 'read', $server->id)) {
            DB::rollBack();
            return response()->json(['errors' => []])->setStatusCode(403, 'You are not allowed to access information about this server!');
        }

        $services = Service::getAllOnServer($serverId);
        $webapps = Webapp::getAllOnServer($serverId);
        $databases = Database::getAllOnServer($serverId);

        // Ping services
        $service_list = array();
        foreach ($services as $serviceObj) {
            $service = (array) $serviceObj;
            $result = Monitor::scanPort('tcp', $service['port'], $server->ip);
            $service['status'] = $result['status'];
            $service['time'] = $result['time'];
            $service_list[] = $service;
        }

        $webapp_list = array();
        foreach ($webapps as $webappObj) {
            $webapp = (array) $webappObj;
            $result = Monitor::checkStatus($webapp['url']);
            $webapp['status'] = $result['status'];
            $webapp['time'] = $result['time'];
            $webapp_list[] = $webapp;
        }

        $database_list = array();
        foreach ($databases as $databaseObj) {
            $database = (array) $databaseObj;
            $database_list[] = $database;
        }

        $response = array(
            'services'  =>  $service_list,
            'webapps'   =>  $webapp_list,
            'databases' =>  $database_list
        );

        return response()->json($response);
    }
}
