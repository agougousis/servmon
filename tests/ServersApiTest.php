<?php

use Illuminate\Foundation\Testing\WithoutMiddleware;
use Illuminate\Foundation\Testing\DatabaseMigrations;
use Illuminate\Foundation\Testing\DatabaseTransactions;

use App\User;
use App\Models\Domain;
use App\Models\Server;

class ServersApiTest extends TestCase
{   
    
    protected $admin;
    protected $non_admin;     

    protected function setUp(){
        parent::setUp();                 
        $this->artisan("migrate:refresh");
        $this->artisan("db:seed");
        
        $this->add_sample_users();
        $this->add_sample_domains();
        $this->add_sample_servers();
    }    
    
    protected function add_sample_users(){
        // An admin user already exists from seeding. We also need
        // a non-admin user
        factory(User::class,1)->create();        
        
        $this->non_admin = User::where('superuser',0)->first();
        $this->admin = User::where('superuser',1)->first();
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
    
    protected function add_sample_servers(){
        // Add servers a admin
        $this->be($this->admin);
        $post_data = array(
            'servers'   =>  array( 
                array(
                    'hostname' =>  's1',
                    'domain'   =>  'gougousis.gr',
                    'ip'    =>  '62.169.226.30',
                    'os'    =>  'Windows'
                ),
                array(
                    'hostname' =>  's2',
                    'domain'   =>  'dom1.gougousis.gr',
                    'ip'    =>  '148.251.138.169',
                    'os'    =>  'Linux'
                )
            )
        ); 
        
        $this->call('POST', '/api/servers',$post_data,[],[],['HTTP_X-CSRF-Token'=>csrf_token(),'contentType'=>'application/json; charset=utf-8'],[]);
        
        // Add servers as non-admin
        $this->be($this->non_admin);
        $post_data = array(
            'servers'   =>  array(
                array(
                    'hostname' =>  's4',
                    'domain'   =>  'takis.gr',
                    'ip'    =>  '77.235.54.162',
                    'os'    =>  'Windows'
                )                
            )
        ); 
        
        $this->call('POST', '/api/servers',$post_data,[],[],['HTTP_X-CSRF-Token'=>csrf_token(),'contentType'=>'application/json; charset=utf-8'],[]);        
        
    }
    
    /** @test */
    public function get_servers_under_specific_domain(){         
        
        $this->be($this->admin);     
        
        // Get servers under a domain you manage
        $this->call('GET','api/domains/gougousis.gr/all_servers');
        $servers = json_decode($this->response->getContent());
        $this->assertEquals(2,count($servers),'The number of servers under gougousis.gr should be 2.');
        
        // Get servers under a domain you don't manage - you are superuser
        $this->call('GET','api/domains/takis.gr/all_servers');
        $this->assertEquals(200,$this->response->getStatusCode(),'A superuser should be able to retrieve the list of servers for a domain he cannot manage.');
        
        // Get servers under a domain you don't manage - you are NOT superuser
        $this->be($this->non_admin);
        $this->call('GET','api/domains/gougousis.gr/all_servers');
        $this->assertEquals(403,$this->response->getStatusCode(),'Someone who is not superuser should not be able to get the list of servers for a domain he cannot manage.');    

    }
    
    /** @test */
    public function get_server_info(){ 
        
        $this->be($this->admin);     
        
        // Get info for a server you manage
        $this->visit('api/servers/1')->seeJsonEquals([
                'services'  =>  [],
                'webapps'   =>  [],
                'databases' =>  []
        ]);
        
        // Get info for a server you don't manage - you are superuser
        $this->call('GET','api/servers/3');
        $this->assertEquals(200,$this->response->getStatusCode(),'A superuser should be able to read info about a server he cannot manage.');
        
        // Get info for a server you don't manage - you are NOT superuser
        $this->be($this->non_admin);
        $this->call('GET','api/servers/2');
        $this->assertEquals(403,$this->response->getStatusCode(),'A user who is not superuser should not be able to read info about a server he cannot manage');
    }
    
    /** @test */
    public function update_server_info(){ 

        $this->be($this->admin);
        
        // Try to update a server you manage
        $post_data = array(
            'servers'   =>  array(
                array(
                    'serverId'  =>  1,
                    'hostname' =>  'sa',
                    'ip'    =>  '62.169.226.30',
                    'os'    =>  'Windows'
                )                
            )
        ); 
        
        $this->call('PUT', '/api/servers',$post_data,[],[],['HTTP_X-CSRF-Token'=>csrf_token(),'contentType'=>'application/json; charset=utf-8'],[]);
        $servers = json_decode($this->response->getContent());
        $this->assertEquals(1,count($servers),'Updating one server should return one server as response.');
        $this->assertEquals('sa',$servers[0]->hostname,'The name of the updated server in the response should be sa');
        
        // Try to update a server you don't manage
        $post_data = array(
            'servers'   =>  array(
                array(
                    'serverId'  =>  3,
                    'hostname' =>  'sb',
                    'ip'    =>  '77.235.54.162',
                    'os'    =>  'Windows'
                )                
            )
        ); 
        $this->call('PUT', '/api/servers',$post_data,[],[],['HTTP_X-CSRF-Token'=>csrf_token(),'contentType'=>'application/json; charset=utf-8'],[]);
        $this->assertEquals(403,$this->response->getStatusCode(),'A user should not be able to update a server he cannot manage');
        
    }
    
    /** @test */
    public function delete_a_server(){ 
        $this->be($this->admin);
        
        // Admin user deletes a server in one of the domains he manage
        $this->call('DELETE','api/servers/1',[],[],[],['HTTP_X-CSRF-Token'=>csrf_token(),'contentType'=>'application/json; charset=utf-8'],[]);
        $this->assertEquals(200,$this->response->getStatusCode(),'A user should be able to delete a server in a domain he can manage');
        
        // Admin user deletes a server in one of the domains he doesn't manage
        $this->call('DELETE','api/servers/3',[],[],[],['HTTP_X-CSRF-Token'=>csrf_token(),'contentType'=>'application/json; charset=utf-8'],[]);
        $this->assertEquals(403,$this->response->getStatusCode(),'A user should not be able to delete a server in a domain he cannot manage');
    }   
    
    /** @test */
    public function get_list_of_all_servers_the_user_can_nanage(){
        $this->be($this->admin);        
        $this->call('GET','api/servers');
        $servers = json_decode($this->response->getContent());
        $this->assertEquals(2,count($servers),'The user should be able to manage 2 servers.');
        $this->assertEquals('s1',$servers[0]->hostname,'The name of the first server the user can manage should be s1');
        $this->assertEquals('s2',$servers[1]->hostname,'The name of the first server the user can manage should be s2');
    }
    
    /** @test */
    public function get_list_of_servers_on_a_specific_domain(){
        $this->be($this->admin);
        
        // Get servers list of a domain you manage
        $this->call('GET','api/domains/gougousis.gr/servers');
        $servers = json_decode($this->response->getContent());
        $this->assertEquals(1,count($servers),'Only one server should exist in domain gougousis.gr');
        $this->assertEquals('s1',$servers[0]->hostname,'Server name should be s1');
        
        // Get servers list of a domain you don't manage
        $this->call('GET','api/domains/takis.gr/servers');
        $servers = json_decode($this->response->getContent());
        $this->assertEquals(401,$this->response->getStatusCode(),'You should be not able to get a list of server for a domain you cannot manage!');
    }
    
}