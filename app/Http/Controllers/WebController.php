<?php

namespace App\Http\Controllers;

use Auth;
use DateTime;
use Redirect;
use App\User;
use App\Models\PasswordResetLink;

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
    
    /**
     * Displays password reset request form
     * 
     * @return View
     */
    public function password_reset_request(){          
        return $this->load_view('password_reset_request','Password reset request');
    }
    
    /**
     * Displays a message about the password reset link sent to the user
     * 
     * @return View
     */
    public function reset_link_sent(){
        return $this->load_view('reset_link_sent','Password reset requested');
    } 
    
    /**
     * Displays a form to set a new password
     * 
     * @param string $code
     * @return View
     */
    public function set_password_page($code){
        $linkInfo = PasswordResetLink::where('code','=',$code)->first();

        // Check for invalid link
        if(empty($linkInfo)){
            $this->log_event("Illegal reset link.",'authentication');
            return $this->load_view('errors.illegal','');
        }               
        
        // Check for expired link
        $now = new DateTime();
        $valid_until = new DateTime($linkInfo->valid_until);
        if($now > $valid_until){
            $this->log_event("Expired reset link.",'authnetication');
            return $this->load_view('errors.expired_link','Invalid link');
        } 
        
        // Load the password setting page
        $data = ['code' => $code];
        return $this->load_view('set_password_page','Password reset page',$data);
    }
    
}