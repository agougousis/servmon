<?php

use Illuminate\Foundation\Testing\WithoutMiddleware;
use Illuminate\Foundation\Testing\DatabaseMigrations;
use Illuminate\Foundation\Testing\DatabaseTransactions;

use App\User;
use App\Models\Domain;
use App\Models\Server;
use App\Models\DomainDelegation;

class UsersApiTest extends TestCase
{
    
    protected $admin;
    protected $admin2;
    protected $non_admin;
    protected $non_admin2;
    
    public function setUp(){
        parent::setUp();                   
        $this->artisan("migrate:refresh");
        $this->artisan("db:seed");
        
        $this->add_sample_users();        
    }
    
    protected function add_sample_users(){
        factory(User::class,1)->create();   
        factory(User::class,1)->create(['activated'=>0]); 
        factory(User::class,1)->create(['superuser'=>1]);

        $users = User::where('superuser',0)->get();
        $this->non_admin = $users[0];
        $this->non_admin2 = $users[1];
        $users = User::where('superuser',1)->get();
        $this->admin = $users[0];
        $this->admin2 = $users[1];
    }
        
    protected function add_sample_domains(){        
        
        // Add domains as admin
        $this->be($this->admin);        
        $post_data = array(
            'domains'   =>  array(
                array(
                    'node_name'       =>  'gougousis.gr',
                    'parent_domain'   =>  '',
                    'fake_domain'     =>  0
                ),
                array(
                    'node_name'       =>  'dom1',
                    'parent_domain'   =>  'gougousis.gr',
                    'fake_domain'     =>  0
                ),
                array(
                    'node_name'       =>  'dom2',
                    'parent_domain'   =>  'gougousis.gr',
                    'fake_domain'     =>  0
                )
            )
        ); 
        
        $this->call('POST', '/api/domains',$post_data,[],[],['HTTP_X-CSRF-Token'=>csrf_token(),'contentType'=>'application/json; charset=utf-8'],[]);
        
        // Add domains as non-admin
        $this->be($this->non_admin);        
        $post_data = array(
            'domains'   =>  array(
                array(
                    'node_name'       =>  'takis.gr',
                    'parent_domain'   =>  '',
                    'fake_domain'     =>  0
                )
            )
        ); 
        
        $this->call('POST', '/api/domains',$post_data,[],[],['HTTP_X-CSRF-Token'=>csrf_token(),'contentType'=>'application/json; charset=utf-8'],[]);
        
    }
    
    protected function add_sample_domain_delegations(){
        
        $domain = Domain::where('node_name','dom2')->first();                               
        $delegation = new DomainDelegation([
            'user_id'   =>  $this->admin2->id,
            'domain_id' =>  $domain->id
        ]);
        $delegation->save();
                                      
        $delegation = new DomainDelegation([
            'user_id'   =>  $this->non_admin2->id,
            'domain_id' =>  $domain->id
        ]);
        $delegation->save();
        
    }        
    
    /** @test */
    public function search_users(){
        $this->be($this->admin);
        $this->call('GET','api/users');
        $users = json_decode($this->response->getContent());
        $this->assertEquals(4,count($users));
    }
    
    /** @test */
    public function read_user_info(){
        $this->be($this->admin);
        $this->visit('api/users/1')->seeJsonEquals([
            'id'        =>  $this->admin->id,
            'email'     =>  $this->admin->email,
            'firstname' =>  $this->admin->firstname,
            'lastname'  =>  $this->admin->lastname,
            'activated' =>  $this->admin->activated,
            'superuser' =>  $this->admin->superuser,
            'last_login'=>  $this->admin->last_login
        ]);

    }
    
    /** @test */
    public function create_users(){
        $this->be($this->admin);  
        
        // A super user adds users
        $post_data = array(
            'users'   =>  array( 
                array(
                    'firstname'     =>  'Tomas',
                    'lastname'      =>  'Crown',
                    'email'         =>  'tomas@mail.com',
                    'password'      =>  'portlet2@1',
                    'verify_password'   =>  'portlet2@1'
                ),
                array(
                    'firstname'     =>  'Andrew',
                    'lastname'      =>  'Somerfield',
                    'email'         =>  'andrew@mail.com',
                    'password'      =>  '-45TTTpp',
                    'verify_password'   =>  '-45TTTpp'
                )
            )
        );   
        
        $this->call('POST', '/api/users',$post_data,[],[],['HTTP_X-CSRF-Token'=>csrf_token(),'contentType'=>'application/json; charset=utf-8'],[]);        
        $this->assertEquals(200,$this->response->getStatusCode(),'A superuser should be able to add new users!');
        $users_added = json_decode($this->response->getContent());
        $this->assertEquals(2,count($users_added),'Two users should be included in the response!');
        $this->assertEquals(0,$users_added[0]->activated,'A new user should be de-activated by default!');
        
        // A non-superuser tries to add users
        $this->be($this->non_admin);
        $post_data = array(
            'users'   =>  array( 
                array(
                    'firstname'     =>  'Tomas1',
                    'lastname'      =>  'Crown1',
                    'email'         =>  'toma1s@mail.com',
                    'password'      =>  'portlet2@1',
                    'verify_password'   =>  'portlet2@1'
                )               
            )
        );  
        $this->call('POST', '/api/users',$post_data,[],[],['HTTP_X-CSRF-Token'=>csrf_token(),'contentType'=>'application/json; charset=utf-8'],[]);        
        $this->assertEquals(401,$this->response->getStatusCode(),'A non-superuser should not be able to add new users!');
    }
    
    /** @test */
    public function activate_deactivate_users(){
        
        $this->be($this->admin);
        
        // Admin activates a user        
        $this->call('PUT', '/api/users/'.$this->non_admin2->id.'/enable',[],[],[],['HTTP_X-CSRF-Token'=>csrf_token(),'contentType'=>'application/json; charset=utf-8'],[]);        
        $this->assertEquals(200,$this->response->getStatusCode(),'A superuser should be able to activate users!');
        $activated_user = User::find($this->non_admin2->id);
        $this->assertEquals(1,$activated_user->activated);
        
        // Admin deactivates a user
        $this->call('PUT', '/api/users/'.$this->non_admin2->id.'/disable',[],[],[],['HTTP_X-CSRF-Token'=>csrf_token(),'contentType'=>'application/json; charset=utf-8'],[]);        
        $this->assertEquals(200,$this->response->getStatusCode(),'A superuser should be able to de-activate users!');
        $deactivated_user = User::find($this->non_admin2->id);
        $this->assertEquals(0,$deactivated_user->activated);
        
        $this->be($this->non_admin);
        
        // Non-admin tries to activate a user        
        $this->call('PUT', '/api/users/'.$this->non_admin2->id.'/enable',[],[],[],['HTTP_X-CSRF-Token'=>csrf_token(),'contentType'=>'application/json; charset=utf-8'],[]);        
        $this->assertEquals(401,$this->response->getStatusCode(),'A non-superuser should not be able to activate users!');
        
        // Non-admin tries to de-activate a user
        $this->call('PUT', '/api/users/'.$this->admin2->id.'/enable',[],[],[],['HTTP_X-CSRF-Token'=>csrf_token(),'contentType'=>'application/json; charset=utf-8'],[]);        
        $this->assertEquals(401,$this->response->getStatusCode(),'A non-superuser should not be able to de-activate users!');
        
        // You cannot deactivated yourself
        $this->call('PUT', '/api/users/'.$this->non_admin->id.'/disable',[],[],[],['HTTP_X-CSRF-Token'=>csrf_token(),'contentType'=>'application/json; charset=utf-8'],[]);        
        $this->assertEquals(401,$this->response->getStatusCode(),'You cannot deactivate yourself!');
    } 
    
    /** @test */
    public function make_unmake_superusers(){
        
        $this->be($this->admin);
        
        // Admin makes a superuser        
        $this->call('PUT', '/api/users/'.$this->non_admin->id.'/make_superuser',[],[],[],['HTTP_X-CSRF-Token'=>csrf_token(),'contentType'=>'application/json; charset=utf-8'],[]);        
        $this->assertEquals(200,$this->response->getStatusCode(),'A superuser should be able to make superusers!');
        $new_superuser = User::find($this->non_admin->id);
        $this->assertEquals(1,$new_superuser->superuser);
        
        // Admin deactivates a user
        $this->call('PUT', '/api/users/'.$this->non_admin->id.'/unmake_superuser',[],[],[],['HTTP_X-CSRF-Token'=>csrf_token(),'contentType'=>'application/json; charset=utf-8'],[]);        
        $this->assertEquals(200,$this->response->getStatusCode(),'A superuser should be able to unmake superusers!');
        $old_superuser = User::find($this->non_admin->id);
        $this->assertEquals(0,$old_superuser->superuser);

        // You cannot unmake youeself from being superuser
        $this->call('PUT', '/api/users/'.$this->admin->id.'/unmake_superuser',[],[],[],['HTTP_X-CSRF-Token'=>csrf_token(),'contentType'=>'application/json; charset=utf-8'],[]);        
        $this->assertEquals(401,$this->response->getStatusCode(),'A non-superuser should not be able to unmake superusers!');
        
        $this->be($this->non_admin);
        
        // Non-admin tries to make a superuser        
        $this->call('PUT', '/api/users/'.$this->non_admin2->id.'/make_superuser',[],[],[],['HTTP_X-CSRF-Token'=>csrf_token(),'contentType'=>'application/json; charset=utf-8'],[]);        
        $this->assertEquals(401,$this->response->getStatusCode(),'A non-superuser should not be able to make superusers!');
        
        // Non-admin tries to unmake a superuser
        $this->call('PUT', '/api/users/'.$this->admin->id.'/unmake_superuser',[],[],[],['HTTP_X-CSRF-Token'=>csrf_token(),'contentType'=>'application/json; charset=utf-8'],[]);        
        $this->assertEquals(401,$this->response->getStatusCode(),'A non-superuser should not be able to unmake superusers!');
                
    }
    
    /** @test */
    public function delete_users(){
        
        // A non-superuser tries to delete a user
        $this->be($this->non_admin);        
        $this->call('DELETE', '/api/users/'.$this->non_admin2->id,[],[],[],['HTTP_X-CSRF-Token'=>csrf_token(),'contentType'=>'application/json; charset=utf-8'],[]);        
        $this->assertEquals(401,$this->response->getStatusCode(),'A non-superuser should not be able to delete a user!');
        
        // A superuser deletes a user        
        $this->add_sample_domains();
        $this->add_sample_domain_delegations();             
        $this->be($this->admin);
        $this->call('DELETE', 'api/users/'.$this->non_admin->id,[],[],[],['HTTP_X-CSRF-Token'=>csrf_token(),'contentType'=>'application/json; charset=utf-8'],[]);        
        $this->assertEquals(200,$this->response->getStatusCode(),'A superuser should be able to delete a user!');
        
        // Check that delegations were removed after user deletion
        $count_delegations = DomainDelegation::where('user_id',$this->non_admin->id)->get()->count();
        $this->assertEquals(0,$count_delegations,'No delegations should exist for the deleted user.');
        
    }
    
}