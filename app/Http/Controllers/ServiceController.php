<?php

namespace App\Http\Controllers;

use DB;
use Config;
use Validator;
use App\Models\Service;
use Illuminate\Http\Request;
use App\Http\Controllers\RootController;

/**
 * Implements functionality related to services
 *
 * @license MIT
 * @author Alexandros Gougousis
 */
class ServiceController extends RootController {
    
    /**
     * Adds new services to servers
     * 
     * @param Request $request
     * @return Response
     */
    public function create(Request $request){
        
        $services = $request->input('services');
        $services_num = count($services);                
        
        // Validate the data for each node
        $errors = array();
        $index = 0;
        $created = array();
        DB::beginTransaction();
        foreach($services as $service){
            try {
                $rules = Config::get('validation.create_service');
                $validator = Validator::make($service,$rules);
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
                    return response()->json(['errors' => $errors])->setStatusCode(400, 'Service validation failed');
                } else {
                    
                    // Access control
                    if(!$this->hasPermission('service',$service['server'],'create',null)){
                        DB::rollBack();
                        return response()->json(['errors' => []])->setStatusCode(403, 'You are not allowed to create services on this server!');
                    }
                    
                    $serv = new Service();
                    $serv->fill($service)->save(); 
                    $created[] = $serv;
                }
            } catch (Exception $ex) {
                DB::rollBack();
                $errors[] = array(
                    'index'     =>  $index,
                    'field'     =>  $result['error']['field'],
                    'message'   =>  $result['error']['message']
                );
                return response()->json(['errors' => $errors])->setStatusCode(400, 'Service creation failed');
            }
            
            $index++;
        }
        
        DB::commit();       
        return response()->json($created)->setStatusCode(200,$services_num.' service(s) added.'); 

    }
    
    /**
     * Returns information about a specific service item
     * 
     * @param int $serviceId
     * @return Response
     */
    public function read($serviceId){                
        
        // Check if $serviceId is a positive integer
        if($serviceId <= 0){
            return response()->json(['errors' => array()])->setStatusCode(400, 'Invalid service ID');
        }
        
        // Check if a service with such an ID exists    
        $service = Service::find($serviceId);
        if(empty($service)){
            return response()->json(['errors' => array()])->setStatusCode(400, 'Invalid service ID');
        }
        
        $service = Service::select('id','server','stype','port','version','watch')->where('id',$serviceId)->first();
        
        // Access control
        if(!$this->hasPermission('service',$service->server,'read',$serviceId)){
            DB::rollBack();
            return response()->json(['errors' => []])->setStatusCode(403, 'You are not allowed to read services on this server!');
        }
        
        $result = new \stdClass();
        $result->data = $service;
        
        // Send back the node info
        return response()->json($result)->setStatusCode(200, '');
        
    }
    
    /**
     * Updates service items
     * 
     * @param Request $request
     * @return Response
     */
    public function update(Request $request){
        
        $services = $request->input('services');
        $services_num = count($services);                
        
        // Validate the data for each node
        $errors = array();
        $index = 0;
        $updated = array();
        DB::beginTransaction();
        foreach($services as $service){
            try {
                $rules = Config::get('validation.update_service');
                $validator = Validator::make($service,$rules);
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
                    return response()->json(['errors' => $errors])->setStatusCode(400, 'Service validation failed');
                } else {
                    $serv = Service::find($service['id']);
                    
                    // Access control
                    if(!$this->hasPermission('service',$serv->server,'create',$service['id'])){
                        DB::rollBack();
                        return response()->json(['errors' => []])->setStatusCode(403, 'You are not allowed to update services on this server!');
                    }
                    
                    $serv->fill($service)->save();
                    $updated[] = $serv;
                }
            } catch (Exception $ex) {
                DB::rollBack();
                $errors[] = array(
                    'index'     =>  $index,
                    'field'     =>  $result['error']['field'],
                    'message'   =>  $result['error']['message']
                );
                return response()->json(['errors' => $errors])->setStatusCode(400, 'Service update failed');
            }
            
            $index++;
        }
        
        DB::commit();       
        return response()->json($updated)->setStatusCode(200,$services_num.' service(s) updated.');         
        
    }
    
    /**
     * Deletes a specific service item
     * 
     * @param int $serviceId
     * @return Response
     */
    public function delete($serviceId){
        
        // Check if $appId is a positive integer
        if($serviceId <= 0){
            return response()->json(['errors' => array()])->setStatusCode(400, 'Invalid service ID');
        }
        
        // Check if a node with ID equal to $nid exists
        $service = Service::find($serviceId);
        if(empty($service)){
            return response()->json(['errors' => array()])->setStatusCode(400, 'Invalid service ID');
        }
        
        // Access control
        if(!$this->hasPermission('service',$service->server,'delete',$service->id)){
            DB::rollBack();
            return response()->json(['errors' => []])->setStatusCode(403, 'You are not allowed to delete services on this server!');
        }
        
        $service->delete();
        
    }
    
}