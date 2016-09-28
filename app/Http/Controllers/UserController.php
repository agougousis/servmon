<?php

namespace App\Http\Controllers;

use DB;
use Auth;
use Hash;
use Input;
use Config;
use Validator;
use App\User;
use App\Models\DomainDelegation;
use App\Models\ServerDelegation;
use Illuminate\Http\Request;
use App\Http\Controllers\RootController;

/**
 * Implements functionality related to user management
 *
 * @license MIT
 * @author Alexandros Gougousis
 */
class UserController extends RootController {
    
    /**
     * Returns information about a specific user
     * 
     * @param int $user_id
     * @return Response
     */
    public function read($user_id){
        
        $user = User::where('id',$user_id)->select('id','email','firstname','lastname','activated','superuser','last_login')->first();
        if(!empty($user)){
            return response()->json($user)->setStatusCode(200,''); 
        } else {
            return response()->json(['errors' => []])->setStatusCode(400, 'Invalid user ID.');
        }        
        
    }
    
    /**
     * Returns a list of users
     * 
     * @uses $_GET['mode']
     * @return Response
     */
    public function search(){
        
        if(!Input::has('mode')){
            $mode = "normal";
        } else {
            $mode = Input::get('mode');
        }
                   
        switch($mode){
            case 'normal':
                $users = User::getList();
                break;
            case 'basic':
                $user_list = User::getBasicInfoList();
                $users = array();
                foreach($user_list as $user){
                    $users[$user->email] = $user->firstname." ".$user->lastname;
                }
                break;
            default:
                return response()->json(['errors' => array()])->setStatusCode(400, 'Invalid search mode!');
                break;
        }
                
        return response()->json($users)->setStatusCode(200,''); 
        
    }    

    /*
     * Creates new users
     * 
     * @return Response
     */
    public function add_users(Request $request){
        
        $users = $request->input('users');
        $users_num = count($users);                
        
        // Validate the data for each node
        $errors = array();
        $created = array();
        $index = 0;
        DB::beginTransaction();
        foreach($users as $user){
            try {
                $rules = Config::get('validation.add_user');
                $validator = Validator::make($user,$rules);
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
                    return response()->json(['errors' => $errors])->setStatusCode(400, 'User validation failed');
                } else {
                    // Create the user in the database
                    $new_user = new User();
                    $new_user->firstname = $user['firstname'];
                    $new_user->lastname = $user['lastname'];
                    $new_user->email = $user['email'];
                    $new_user->password = Hash::make($user['password']);            
                    $new_user->activated = 0;
                    $new_user->save();    
                    
                    $new_user->password = '';
                    $created[] = $new_user;
                }
            } catch (Exception $ex) {
                DB::rollBack();
                $errors[] = array(
                    'index'     =>  $index,
                    'field'     =>  $result['error']['field'],
                    'message'   =>  $result['error']['message']
                );
                return response()->json(['errors' => $errors])->setStatusCode(400, 'User creation failed');
            }
            
            $index++;
        }
        
        DB::commit();       
        return response()->json($created)->setStatusCode(200,$users_num.' user(s) added.'); 
        
    }

    /**
     * Deletes a specific user
     * 
     * @param int $user_id
     * @return Response
     */
    public function delete_user($user_id){                             
        
        if(Auth::user()->id == $user_id){
            return response()->json(['errors' => []])->setStatusCode(400, 'You cannot delete your own account!');
        }

        $user = User::find($user_id);
        if(empty($user)){
            return response()->json(['errors' => []])->setStatusCode(400, 'Invalid user ID!');
        }

        DB::beginTransaction();
        try {
            // Delete the user delegations
            DomainDelegation::deleteUserDelegations($user_id);
            ServerDelegation::deleteUserDelegations($user_id);
            // Delete the user
            $user->delete();             
        } catch (Exception $ex) {
            DB::rollBack();
            $this->log_event('User deletion failed! ERROR: '.$ex->getMessage(),'error');
            return response()->json([])->setStatusCode(500, 'User deleation failed! Please, contact the administrator.');
        }
        DB::commit();                          

        return response()->json([])->setStatusCode(200, 'The user has been deleted!');     

    }
    

    /**
     * Disables a specific user account
     * 
     * @param int $user_id
     * @return Response
     */
    public function disable_user($user_id){

        if(Auth::user()->id == $user_id){
            return response()->json(['errors' => []])->setStatusCode(400, 'A user cannot deactivate himself!');
        }
        $user = User::find($user_id);
        if(empty($user)){
            return response()->json(['errors' => []])->setStatusCode(400, 'Illegal user ID.');
        } else {
            $user->activated = 0;
            $user->save();
            $user->password = '';
            return response()->json($user)->setStatusCode(200, 'User deactivated!'); 
        }

    }
    
    /**
     * Enables a specific user account
     * 
     * @param int $user_id
     * @return Response
     */
    public function enable_user($user_id){

            $user = User::find($user_id);
            if(empty($user)){
                return response()->json(['errors' => array()])->setStatusCode(400, 'Illegal user ID.');
            } else {
                $user->activated = 1;
                $user->save();
                $user->password = '';
                return response()->json($user)->setStatusCode(200, 'User activated!'); 
            }

    }    
    
    /**
     * Grants superuser privileges to a user
     * 
     * @param int $user_id
     * @return Response
     */
    public function make_superuser($user_id){        
        $user = User::find($user_id);
        if(empty($user)){
            return response()->json(['errors' => array()])->setStatusCode(400, 'Illegal user ID.');
        } else {
            $user->superuser = 1;
            $user->save();
            $user->password = '';
            return response()->json($user)->setStatusCode(200, 'User activated!'); 
        }
    }
    
    /**
     * Revokes superuser privileges from a user
     * 
     * @param int $user_id
     * @return Response
     */
    public function unmake_superuser($user_id){
        if(Auth::user()->id == $user_id){
            return response()->json(['errors' => array()])->setStatusCode(400, 'You cannot revoke superuser privilege from yourself!');
        }
        $user = User::find($user_id);
        if(empty($user)){
            return response()->json(['errors' => array()])->setStatusCode(400, 'Illegal user ID.');
        } else {
            $user->superuser = 0;
            $user->save();
            $user->password = '';
            return response()->json($user)->setStatusCode(200, 'User activated!'); 
        }
    }
    
    
}
