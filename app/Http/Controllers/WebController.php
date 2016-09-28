<?php

namespace App\Http\Controllers;

use Auth;
use Config;
use Redirect;

/**
 * Implements functionality related to backups
 *
 * @license MIT
 * @author Alexandros Gougousis
 */
class WebController extends RootController {    
    
    /**
     * Displays the installation page
     * 
     * @return View
     */
    public function installation_page(){
        
        if(config('app.installation') == 'done'){
            return Redirect::to('/');
        } 
        return $this->load_view('installation','Installation Page');
        
    }
    
    /**
     * Displays the Landing Page
     * 
     * @return View
     */
    public function landing_page(){
        
        if(config('app.installation') != 'done'){
            return Redirect::to('/installation_page');
        } 
        
        if(Auth::check()){
            return Redirect::to('/home');
        }
        
        return $this->load_view('landing','Home Page');
        
    }
    
    /**
     * Displays the Home Page
     * 
     * @return View
     */
    public function index(){                             
        
        return $this->load_view('index',"Home Page");
        
    }        
    
    /**
     * Displays user profile page
     * 
     * @return View
     */
    public function profile(){
        return $this->load_view('profile','My Profile');
    }
    
    /*
     * Displays the user management page.
     * 
     * @return View
     */
    public function user_management(){
      
        $title = 'User Management';                
        return $this->load_view('admin.user_management', $title);

    }    
    
    /**
     * Display a user's profile
     * 
     * @param int $user_id
     * @return Response
     */
    public function user_profile_management($user_id){

            $user = User::find($user_id);   
            if(empty($user)){
                return $this->custom_error_message("User not found!");
            }                                   
            
            $data['user_id'] = $user_id;             
            return $this->load_view('admin.user_profile_manage', 'User Profile',$data);            
            
    }
    
    /**
     * Displays the backup administration page
     * 
     * @return View
     */
    public function backup_page(){                            
        
        return $this->load_view('backup',"Backup");
        
    }
    
    /**
     * Displays the monitoring configuration page
     * 
     * @return View
     */
    public function configure(){

        return $this->load_view('configure',"Configuration Page");
        
    }
    
    /**
     * Displays the delegations management page
     * 
     * @return View
     */
    public function delegations_page(){                 
        
        return $this->load_view('delegations','Administration Delegation');
        
    }
    
}