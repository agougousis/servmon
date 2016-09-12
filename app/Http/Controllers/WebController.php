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
    
}