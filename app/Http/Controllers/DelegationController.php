<?php

namespace App\Http\Controllers;

use DB;
use Auth;
use Input;
use Monitor;
use Illuminate\Http\Request;
use App\User;
use App\Models\Domain;
use App\Models\Server;
use App\Models\DomainDelegation;
use App\Models\ServerDelegation;
use App\Http\Controllers\RootController;
use App\Packages\Gougousis\Transformers\Transformer;

/**
 * Implements functionality related to administration delegations
 *
 * @license MIT
 * @author Alexandros Gougousis
 */
class DelegationController extends RootController
{
    protected $transformer;

    public function __construct()
    {
        $this->transformer = new Transformer('DelegationTransformer');
    }

    /**
     * Returns information delegations
     *
     * The response may contains information about the delegations themselves
     * or even the status of each delegated item (for server delegations).
     *
     * @uses $_GET['mode']
     * @return Response
     */
    public function search()
    {
        $mode = Input::get('mode') ?: 'all';

        // Access Control
        if ((!$this->isSuperuser())&&($mode != 'my_servers')) {
            return response()->json([])->setStatusCode(403, 'Unauthorized Access!');
        }

        switch ($mode) {
            case 'all':
                $domain_delegations = DomainDelegation::getAllFullInfo();
                $server_delegations = ServerDelegation::getAllWithUser();
                $responseArray['domain_delegations'] = turnToAssoc('full_name', $domain_delegations);
                $responseArray['server_delegations'] = turnToAssoc('server_id', $server_delegations);
                break;
            case 'my_servers':
                // Get server delegations for this user
                $server_delegations = ServerDelegation::getUserDelegatedIds(Auth::user()->id);
                // Get information about the delegated servers
                $delegated_servers = Server::getServersInfoByIds(array_flatten($server_delegations));
                // Add server status information for each server
                $server_list = array_map(array($this,'enrichServerInfo'), $delegated_servers);
                // Refine the data output
                $responseArray = $this->transformer->transform($server_list, 'ServerTransformer');
                break;
            default:
                return response()->json(['errors' => array()])->setStatusCode(400, 'Invalid search mode!');
                break;
        }

        return response()->json($responseArray)->setStatusCode(200, '');
    }

    /**
     * Add information on a server item, especially about its current status.
     *
     * @param Server $server
     * @return array
     */
    protected function enrichServerInfo($server)
    {
        $pingResult = Monitor::ping($server->ip);
        $server->status = ($pingResult['status']) ? 'on' : 'off';
        $server->response_time = $pingResult['time'];
        return $server;
    }

    /**
     * Deletes a specific domain delegation
     *
     * @param int $delegationId
     * @return Response
     */
    public function deleteDomainDelegation($delegationId)
    {
        // Check if delegation ID is valid
        $delegation = DomainDelegation::find($delegationId);
        if (empty($delegation)) {
            return response()->json([])->setStatusCode(404, 'Invalid delegation ID');
        }

        // Revoke the delegation
        $delegation->delete();

        return response()->json([])->setStatusCode(200, "User's delegation revoked!");
    }

    /**
     * Deletes a specific server delegation
     *
     * @param int $delegationId
     * @return Response
     */
    public function deleteServerDelegation($delegationId)
    {
        // Check if delegation ID is valid
        $delegation = ServerDelegation::find($delegationId);
        if (empty($delegation)) {
            return response()->json(['errors' => $errors])->setStatusCode(404, 'Invalid delegation ID');
        }

        // Revoke the delegation
        $delegation->delete();

        return response()->json(array())->setStatusCode(200, "User's delegation revoked!");
    }

    /**
     * Creates new delegations
     *
     * @return Response
     */
    public function create(Request $request)
    {
        // Retrieve nodes array from JSON
        $delegations = $request->input('delegations');

        // Validate the data for each node
        $createdList = array();
        $index = 0;
        DB::beginTransaction();
        foreach ($delegations as $delegation) {
            $result = $this->createDelegationItem($delegation, $index, $createdList);

            if($result['status'] != 200){
                DB::rollBack();
                return response()->json(['errors' => $result['errors']])->setStatusCode($result['status'], $result['message']);
            }

            $index++;
        }

        DB::commit();
        $responseArray = $this->transformer->transform($createdList);
        return response()->json($responseArray)->setStatusCode(200, count($delegations).' delegation(s) created!');
    }

    /**
     * Creates a single delegation
     *
     * @param array $delegation
     * @param int $index
     * @param array $createdList
     * @return array
     */
    protected function createDelegationItem($delegation, $index, &$createdList)
    {
        try {
            // Form validation
            switch ($delegation['dtype']) {
                case 'domain':
                    $errors = $this->loadValidationErrors('validation.domain_delegation_create', $delegation, [], $index);
                    break;
                case 'server':
                    $errors = $this->loadValidationErrors('validation.server_delegation_create', $delegation, [[]], $index);
                    break;
            }
            if (!empty($errors)) {
                return ['status' => 400, 'message' => 'Delegation request validation failed', 'errors' => $errors];
            }

            switch ($delegation['dtype']) {
                case 'domain':
                    $createdList[] = $this->saveDomainDelegation($delegation);
                    break;
                case 'server':
                    $createdList[] = $this->saveServerDelegation($delegation);
                    break;
            }
        } catch (Exception $ex) {
            $this->logEvent('Delegation creation failed! Error: '.$ex->getMessage(), 'error');
            return ['status' => 500, 'message' => 'Delegation creation failed. Check system logs.', 'errors' => []];
        }

        return ['status' => 200, 'message' => '', 'errors' => []];
    }

    /**
     * Saves a server delegation
     *
     * @param array $delegationInfo
     * @return ServerDelegation
     * @throws \Exception
     */
    private function saveServerDelegation($delegationInfo)
    {
        $user = User::findByEmail($delegationInfo['duser']);

        // If the server belongs to a domain delegated to the user, cancel the delegation
        $server = Server::find($delegationInfo['ditem']);
        if ($this->canManageDomain($user->id, $server->domain)) {
            throw new \Exception('Server with ID = '.$server->id.' belongs to a domain already delegated to this user.');
        }

        // Save the delegation
        $newDelegation = new ServerDelegation();
        $newDelegation->user_id = $user->id;
        $newDelegation->server_id = $delegationInfo['ditem'];
        $newDelegation->save();

        return $newDelegation;
    }

    /**
     * Saves a domain delegation
     *
     * @param array $delegationInfo
     * @return DomainDelegation
     */
    private function saveDomainDelegation($delegationInfo)
    {
        $user = User::findByEmail($delegationInfo['duser']);
        $domain = Domain::findByFullname($delegationInfo['ditem']);

        // If the user has been delegated servers under this domain, remove these server delegations
        $descendantDomains = array_flatten($domain->descendantsAndSelf()->select('id')->get()->ToArray());
        ServerDelegation::removeUserDelegationsInDomains($user->id, $descendantDomains);
        $newDelegation = new DomainDelegation();
        $newDelegation->user_id = $user->id;
        $newDelegation->domain_id = $domain->id;
        $newDelegation->save();

        return $newDelegation;
    }
}
