<?php

namespace App\Http\Controllers;

use DB;
use Auth;
use Input;
use Config;
use Illuminate\Http\Request;
use Validator;
use App\User;
use App\Models\Domain;
use App\Models\Server;
use App\Models\DomainDelegation;
use App\Models\ServerDelegation;
use App\Packages\Gougousis\Net\Monitor;
use App\Http\Controllers\RootController;

/**
 * Implements functionality related to administration delegations
 *
 * @license MIT
 * @author Alexandros Gougousis
 */
class DelegationController extends RootController {
    
    /**
     * Displays the delegations management page
     * 
     * @return View
     */
    public function delegations_page(){                 
        
        return $this->load_view('delegations','Administration Delegation');
        
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
    public function search(){                
        
        if(!Input::has('mode')){
            $mode = "all";
        } else {
            $mode = Input::get('mode');
        }
        
        // Access Control
        if((!$this->isSuperuser())&&($mode != 'my_servers')){
            return response()->json([])->setStatusCode(403, 'Unauthorized Access!');
        }
                   
        switch($mode){
            case 'all':
                $domain_delegations = DomainDelegation::getAllFullInfo();                
        
                $ddelegations = array();
                foreach($domain_delegations as $delegation){
                    $ddelegations[$delegation['full_name']][] = $delegation;
                }                

                $server_delegations = ServerDelegation::getAllWithUser();              

                $sdelegations = array();
                foreach($server_delegations as $delegation){
                    $sdelegations[$delegation['server_id']][] = $delegation;
                }

                $data['domain_delegations'] = $ddelegations;
                $data['server_delegations'] = $sdelegations;
                
                break;
            case 'my_servers':
                $server_delegations = ServerDelegation::getUserDelegatedIds(Auth::user()->id); 
                $delegated_servers = Server::getServersInfoByIds(array_flatten($server_delegations));

                $data = array();
                $ping_timeout = Config::get('network.ping_timeout');
                foreach($delegated_servers as $server){
                    $pingResult = Monitor::ping($server['ip'], $ping_timeout);
                    $server['status'] = ($pingResult['status']) ? 'on' : 'off';
                    $server['response_time'] = $pingResult['time'];                    
                    $data[] = $server;
                }
                break;
            default:
                return response()->json(['errors' => array()])->setStatusCode(400, 'Invalid search mode!');
                break;
        }
        
        return response()->json($data)->setStatusCode(200,'');         
        
    }
    
    /**
     * Deletes a specific domain delegation
     * 
     * @param int $delegationId
     * @return Response
     */
    public function delete_domain_delegation($delegationId){
        
        // Check if delegation ID is valid
        $delegation = DomainDelegation::find($delegationId);
        if(empty($delegation)){
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
    public function delete_server_delegation($delegationId){
        
        // Check if delegation ID is valid
        $delegation = ServerDelegation::find($delegationId);
        if(empty($delegation)){
            return response()->json(['errors' => $errors])->setStatusCode(404, 'Invalid delegation ID');
        }
        
        // Revoke the delegation
        $delegation->delete();
        
        return response()->json(array())->setStatusCode(200,"User's delegation revoked!");
    }
    
    /**
     * Creates new delegations
     * 
     * @return Response
     */
    public function create(Request $request){                
        
        // Retrieve nodes array from JSON
        $delegations = $request->input('delegations');
        $delegations_num = count($delegations);                
        
        // Validate the data for each node
        $created = array();
        $errors = array();
        $index = 0;
        DB::beginTransaction();
        foreach($delegations as $delegation){
            
            try {
            
                switch($delegation['dtype']){
                    case 'domain':
                        $rules = config('validation.domain_delegation_create');
                        break;
                    case 'server':
                        $rules = config('validation.server_delegation_create');
                        break;
                }
                
                $validator = Validator::make($delegation,$rules);
                if ($validator->fails()){
                    foreach($validator->errors()->getMessages() as $key => $errorMessages){
                        foreach($errorMessages as $msg){
                            $errors[] = array(
                                'index'     =>  $index,
                                'field'     =>  $key,
                                'message'   =>  $msg
                            );
                        }                    
                    }
                    DB::rollBack();
                    return response()->json(['errors' => $errors])->setStatusCode(400, 'Delegation request validation failed');
                } else {
                    switch($delegation['dtype']){
                        case 'domain':                            
                            $user = User::findByEmail($delegation['duser']);                        
                            $domain = Domain::findByFullname($delegation['ditem']);                        

                            // If the user has been delegated servers under this domain, remove these server delegations
                            $descendantDomains = array_flatten($domain->descendantsAndSelf()->select('id')->get()->ToArray());                                
                            ServerDelegation::removeUserDelegationsInDomains($user->id,$descendantDomains);                                          
                            $newDelegation = new DomainDelegation();
                            $newDelegation->user_id = $user->id;
                            $newDelegation->domain_id = $domain->id;
                            $newDelegation->save();
                            $created[] = $newDelegation;
                            break;
                        case 'server':                            
                            $user = User::findByEmail($delegation['duser']); 

                            // If the server belongs to a domain delegated to the user, cancel the delegation                                                                                                                       
                            $server = Server::find($delegation['ditem']);
                            if($this->canManageDomain($user->id,$server->domain)){
                                return response()->json(['errors' => $errors])->setStatusCode(400, 'Delegation failed! Server belongs to a domain already delegated to this user.');
                            } else {
                                $newDelegation = new ServerDelegation();
                                $newDelegation->user_id = $user->id;
                                $newDelegation->server_id = $delegation['ditem'];
                                $newDelegation->save();
                                $created[] = $newDelegation;
                            }
                            break;
                    }
                }                           
            
            } catch (Exception $ex) {
                DB::rollBack();                
                return response()->json(['errors' => $errors])->setStatusCode(400, 'Delegation creation failed');
            }

            $index++;
        }
        
        DB::commit();
        return response()->json($created)->setStatusCode(200, 'Delegation success!');
        
    } 
    
}