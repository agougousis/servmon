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
use App\Packages\Gougousis\Transformers\Transformer;
use App\Packages\Gougousis\SSH\SshManager;

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
        // Form validation
        $errors = $this->loadValidationErrors('validation.server_snapshot', $request->input(), [], null);
        if (!empty($errors)) {
            DB::rollBack();
            return response()->json(['errors' => $errors])->setStatusCode(400, 'Invalid credentials!');
        }

        // Check if server ID exists
        $server = Server::find($server_id);
        if (empty($server)) {
            return response()->json(['errors'=>[]])->setStatusCode(404, 'The specified server was not found!');
        }

        // Retrieve the fully qualified server name
        $domain = Domain::find($server->domain);
        $full_server_name = $server->hostname.".".$domain->full_name;

        // Retrieve server health information
        $sshManager = new SshManager($full_server_name,$request->sshport,$request->sshuser,$request->sshpass,$request->sshkey);
        if (!$sshManager->connectAndAuthenticate()) {
            return response()->json(['errors'=>[]])->setStatusCode(500, 'SSH login failed! Please make sure that you provided a valid SSH key file path.');
        }
        $serverInfo = $sshManager->getServerInfo();

        return response()->json($serverInfo)->setStatusCode(200, 'ok');
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
