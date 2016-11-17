<?php

namespace App\Http\Controllers;

use DB;
use Auth;
use Input;
use Monitor;
use SshHelper;
use App\Models\Server;
use App\Models\Service;
use App\Models\Webapp;
use App\Models\Database;
use App\Models\ServerDelegation;
use App\Models\Domain;
use Illuminate\Http\Request;
use App\Http\Controllers\RootController;
use phpseclib\Net\SSH2;
use phpseclib\Crypt\RSA;
use App\Packages\Gougousis\Transformers\Transformer;

/**
 * Implements functionality related to server items
 *
 * @license MIT
 * @author Alexandros Gougousis
 */
class ServerController extends RootController
{
    protected $transformer;

    public function __construct()
    {
        $this->transformer = new Transformer('ServerTransformer');
    }

    /**
     * Retrieves system health information for a server
     *
     * @param Request $request
     * @param int $server_id
     * @return Response
     */
    public function snapshot(Request $request, $server_id)
    {
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
            $port = $form['sshport'];
            $username = $form['sshuser'];

            $start = microtime(true);

            /**********
             * Login  *
             **********/
            if ($form['authType'] == 'password') {    // Password authentication
                $password = $form['sshpass'];
                $ssh = new SSH2($full_server_name, $port);
                if (!$ssh->login($username, $password)) {
                    return response()->json(['errors'=>[]])->setStatusCode(500, 'SSH login failed! Please make sure that the server is reachable from this web server and the provided username and password are correct.');
                }
            } else { // RSA authentication
                if (!file_exists($form['sshkey'])) {
                    return response()->json(['errors'=>[]])->setStatusCode(400, "The provided file path '".$form['sshkey']."' for the SSH key does not exist or is not accessible!");
                }
                $ssh = new SSH2($full_server_name, $port);
                $key = new RSA();
                $key->loadKey(file_get_contents($form['sshkey']));
                if (!$ssh->login('root', $key)) {
                    return response()->json(['errors'=>[]])->setStatusCode(500, 'SSH login failed! Please make sure that you provided a valid SSH key file path.');
                }
            }

            /*******************
             * Get disk usage  *
             *******************/

            // Block usage
            $df_blocks_output = $ssh->exec('df -h');
            $df_blocks = SshHelper::extractBlockUsageFromDf($df_blocks_output);

            // Inodes usage
            $df_inodes_output = $ssh->exec('df -h -i');
            $df_inodes = SshHelper::extractInodesUsageFromDf($df_inodes_output);

            /*********************
             * Count processors  *
             *********************/
            $count_processors = trim($ssh->exec("grep 'model name' /proc/cpuinfo | wc -l"));

            /**********************
             * Get server uptime and CPU load *
             **********************/
            $uptime_output = $ssh->exec('uptime');
            $uptime_info = SshHelper::extractInfoFromUptime($uptime_output, $count_processors);
            $uptime = $uptime_info['uptime'];
            $last5min_load = $uptime_info['last5min_load'];
            $last10min_load = $uptime_info['last10min_load'];

            /*********************
             * Get memory usage  *
             *********************/
            $free_output = $ssh->exec('free');
            $mem_info = SshHelper::extractInfoFromFree($free_output);
            $total_memory = $mem_info['total'];
            $total_memory_text = SshHelper::addMemoryUnits($total_memory);
            $free_memory = $mem_info['free'];
            $free_memory_text = SshHelper::addMemoryUnits($free_memory);

            /*********************************
             * Get list of network services  *
             *********************************/
            if (isset($form['sshpass'])&&($username != 'root')) {
                $ssh->write("sudo lsof -i -n -P\n");
                $template = "/password\sfor\s$username:|".$username."@".$server->hostname.":~\$/";
                // Read until you see the password prompt or the normal prompt
                $sudo_response = $ssh->read($template, 2);
                if (preg_match("/password\sfor/", $sudo_response)) {
                    // In case of password prompt, give the password
                    $ssh->write("$password\n");
                    // Take into account the 3 sec default delay of linux
                    // (it is there to prevent brute-force attacks)
                    $ssh->setTimeout(3);
                }

                // Read until you see another prompt. We expect to see a normal prompt
                // but, if the password is wrong, it can be a password prompt.
                $lsof_output = $ssh->read($template, 2);
            } else {
                $lsof_output = $ssh->exec('lsof -i -n -P');
            }
            $services = SshHelper::extractServicesFromLsof($lsof_output);

            $end = microtime(true);

            /************************
             * Build HTTP response  *
             ************************/
            $response = compact(
                'uptime',
                'count_processors',
                'last5min_load',
                'last10min_load',
                'total_memory',
                'total_memory_text',
                'free_memory',
                'free_memory_text',
                'df_blocks',
                'df_inodes',
                'services'
            );

            return response()->json($response)->setStatusCode(200, 'ok');
        } catch (\RuntimeException $ex) {
            $reason = SshHelper::identifySshError($ex->getMessage());
            return response()->json(['errors'=>[]])->setStatusCode(500, "SSH login failed! ".$reason);
        } catch (\ErrorException $ex) {
            $reason = SshHelper::identifySshError($ex->getMessage());
            return response()->json(['errors'=>[]])->setStatusCode(500, "SSH login failed! ".$reason);
        } catch (Exception $ex) {
            $reason = SshHelper::identifySshError($ex->getMessage());
            return response()->json(['errors'=>[]])->setStatusCode(500, "SSH login failed! ".$reason);
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
        $mode = Input::get('mode') ?: 'mine';

        switch ($mode) {
            case 'mine':
                $servers = Server::allUserServers(Auth::user()->id);
                break;
            default:
                return response()->json(['errors' => array()])->setStatusCode(400, 'Invalid search mode!');
                break;
        }

        $responseArray = $this->transformer->transform($servers);
        return response()->json($responseArray)->setStatusCode(200, '');
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
        $responseArray = $this->transformer->transform($updated);
        return response()->json($responseArray)->setStatusCode(200, $server_num.' server(s) updated.');
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
        $responseArray = $this->transformer->transform($created);
        return response()->json($responseArray)->setStatusCode(200, $server_num.' server(s) added.');
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

        // Get services status
        $service_list = array();
        foreach ($services as $serviceObj) {
            $result = Monitor::scanPort('tcp', $serviceObj->port, $server->ip);
            $serviceObj->status = $result['status'];
            $serviceObj->time = $result['time'];
            $service_list[] = $serviceObj;
        }

        // Get webapps status
        $webapp_list = array();
        foreach ($webapps as $webappObj) {
            $result = Monitor::checkStatus($webappObj->url);
            $webappObj->status = $result['status'];
            $webappObj->time = $result['time'];
            $webapp_list[] = $webappObj;
        }

        $database_list = array();
        foreach ($databases as $databaseObj) {
            $database_list[] = $databaseObj;
        }

        $server->services = $service_list;
        $server->webapps = $webapp_list;
        $server->databases = $database_list;

        $responseArray = $this->transformer->transform($server);
        return response()->json($responseArray);
    }
}
