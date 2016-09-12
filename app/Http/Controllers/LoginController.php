<?php

namespace App\Http\Controllers;

use Auth;
use Input;
use Session;
use Validator;
use App\User;

/**
 * Implements functionality related to authentication
 *
 * @license MIT
 * @author Alexandros Gougousis
 */
class LoginController extends RootController {
    
    /**
     * Logs a user in
     * 
     * @return Response
     */
    protected function login(){
        
        $form = Input::all();            
        $rules = config('validation.login');
        $validation = Validator::make($form,$rules);

        if ($validation->fails()){            
            $this->log_event("Validation failed!",'login');
            return response()->json(['errors' => []])->setStatusCode(400, 'Wrong username or password!');
        } else {                                   
            // If the validation didn't fail, an account with such email exists
            $check_user = User::findByEmail($form['inputEmail']);             
            
            // Don't let diactivated accounts to login 
            if($check_user->activated == 0){
                $this->log_event("Account is deactivated!",'login');
                return response()->json(['errors' => []])->setStatusCode(403, 'Your account is not active!');
            }
            
            // (Try to) Login officially
            $authenticated = Auth::attempt(array(
                'email'     => $form['inputEmail'],
                'password'  => $form['inputPassword'],
            ));
            
            if($authenticated){                    
                    $user = User::find(Auth::user()->id);
                    $user->last_login = date("Y-m-d H:i:s");
                    $user->save();
                    
                    return response()->json([])->setStatusCode(200, 'Logged-in successfully!');             
                         
            } else {
                $ip = getenv('HTTP_CLIENT_IP')?:
                getenv('HTTP_X_FORWARDED_FOR')?:
                getenv('HTTP_X_FORWARDED')?:
                getenv('HTTP_FORWARDED_FOR')?:
                getenv('HTTP_FORWARDED')?:
                getenv('REMOTE_ADDR');
                
                $this->log_event("Wrong username or password! (".$ip.")",'security');
                return response()->json(['errors' => []])->setStatusCode(400, 'Wrong username or password!');
            }    
        }   
        
    }
    
    /*
     * Logs a user out
     * 
     * @return Redirect
     */
    public function logout(){
        
        try {
            Auth::logout();
            Session::flush();
            return response()->json([])->setStatusCode(200, 'Logged out!');
        } catch (Exception $ex) {
            return response()->json(['errors' => []])->setStatusCode(400, 'Logout failed!');
        }
                
    }
    
}