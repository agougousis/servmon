<?php

namespace App\Http\Controllers;

use DB;
use Auth;
use Input;
use Config;
use App\Models\Server;
use App\Models\Service;
use App\Models\Webapp;
use App\Models\Database;
use App\Models\ServerDelegation;
use App\Models\Domain;
use Illuminate\Http\Request;
use App\Packages\Gougousis\Net\Monitor;
use App\Http\Controllers\RootController;

/**
 * Implements functionality related to server items
 *
 * @license MIT
 * @author Alexandros Gougousis
 */
class ServerController extends RootController
{

    /**
     * Returns a list of servers
     *
     * @uses $_GET['mode']
     * @return Response
     */
    public function search()
    {
        if (!Input::has('mode')) {
            $mode = "mine";
        } else {
            $mode = Input::get('mode');
        }

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

        // Check if domain exists
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
            ServerDelegation::deleteByServerId($server_id);;

            // Delete the server
            $server->delete();
        } catch (Exception $ex) {
            DB::rollBack();
            return response()->json(['errors'=>[]])->setStatusCode(500, 'Unexpected error happened!');
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
                $errors[] = array(
                    'index'     =>  $index,
                    'field'     =>  $result['error']['field'],
                    'message'   =>  $result['error']['message']
                );
                return response()->json(['errors' => $errors])->setStatusCode(400, 'Server update failed');
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
                $errors[] = array(
                    'index'     =>  $index,
                    'field'     =>  $result['error']['field'],
                    'message'   =>  $result['error']['message']
                );
                return response()->json(['errors' => $errors])->setStatusCode(400, 'Server creation failed');
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

        // Access control
        if (!$this->hasPermission('server', $server->domain, 'read', $server->id)) {
            DB::rollBack();
            return response()->json(['errors' => []])->setStatusCode(403, 'You are not allowed to access information about this server!');
        }

        $curl_timeout = Config::get('network.curl_timeout');
        $portscan_timeout = Config::get('network.portscan_timeout');
        $ping_timeout = Config::get('network.ping_timeout');

        $services = Service::getAllOnServer($serverId);
        $webapps = Webapp::getAllOnServer($serverId);
        $databases = Database::getAllOnServer($serverId);

        // Ping services
        $service_list = array();
        foreach ($services as $serviceObj) {
            $service = (array) $serviceObj;
            $result = Monitor::scanPort('tcp',$service['port'], $server->ip, $portscan_timeout);
            $service['status'] = $result['status'];
            $service['time'] = $result['time'];
            $service_list[] = $service;
        }

        $webapp_list = array();
        foreach ($webapps as $webappObj) {
            $webapp = (array) $webappObj;
            $result = Monitor::checkStatus($webapp['url'], $curl_timeout);
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