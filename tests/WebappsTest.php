<?php

use Illuminate\Foundation\Testing\WithoutMiddleware;
use Illuminate\Foundation\Testing\DatabaseMigrations;
use Illuminate\Foundation\Testing\DatabaseTransactions;

use App\User;
use App\Models\Domain;
use App\Models\Server;
use App\Models\Webapp;

class WebappsTest extends TestCase
{
    
    protected $admin;
    protected $non_admin;
    
    public function setUp(){
        parent::setUp();                   
        $this->artisan("migrate:refresh");
        $this->artisan("db:seed");
        
        $this->add_sample_users();
        $this->add_sample_domains();
        $this->add_sample_servers();
        $this->add_sample_webapps();
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
    
    protected function add_sample_webapps(){
        $domain = Domain::where('node_name','gougousis.gr')->first();
        $server = Server::where('domain',$domain->id)->first();                
        
        $webapp = new Webapp([
            'server'    =>  $server->id,
            'url'       =>  'http://www.iefimerida.gr',
            'language'  =>  'php',
            'developer' =>  'Aris Tomas',
            'contact'   =>  'aris@tomas.com'
        ]);
        $webapp->owner = $this->admin->id;
        $webapp->save();
        
        $domain = Domain::where('node_name','takis.gr')->first();
        $server = Server::where('domain',$domain->id)->first();
        
        $webapp = new Webapp([
            'server'    =>  $server->id,
            'url'       =>  'http://www.protagon.gr',
            'language'  =>  'java',
            'developer' =>  'Michael Jordan',
            'contact'   =>  'mic@gmail.com'          
        ]);
        $webapp->owner = $this->non_admin->id;
        $webapp->save();
    }
    
    /** @test */
    public function add_webapps(){    
        
        $this->be($this->non_admin);  
        
        // Add webapps to a server you can manage
        $domain = Domain::where('full_name','takis.gr')->first();
        $server = Server::where('domain',$domain->id)->first();
        
        $post_data = array(
            'webapps'   =>  array( 
                array(
                    'server'    =>  $server->id,
                    'url'       =>  'http://www.newsbeast.gr',
                    'language'  =>  'php',
                    'developer' =>  'Michael Jordan',
                    'contact'   =>  'mic@gmail.com'       
                ),
                array(
                    'server'    =>  $server->id,
                    'url'       =>  'http://www.newsbomb.gr',
                    'language'  =>  'java',
                    'developer' =>  'Michael Jordan',
                    'contact'   =>  'mic@gmail.com'       
                )
            )
        );         
        $this->call('POST', '/api/webapps',$post_data,[],[],['HTTP_X-CSRF-Token'=>csrf_token(),'contentType'=>'application/json; charset=utf-8'],[]);        
        $this->assertEquals(200,$this->response->getStatusCode(),'Adding webapps to a server you can manage failed!');
        $webapps = json_decode($this->response->getContent());
        $this->assertEquals(2,count($webapps),'When adding 2 webapps to a server, the response should contain 2 webapps.');
        $this->assertEquals('http://www.newsbeast.gr',$webapps[0]->url);
        
        // Add webapps to a server you cannot manage
        $domain = Domain::where('full_name','gougousis.gr')->first();
        $server = Server::where('domain',$domain->id)->first();
        $post_data = array(
            'webapps'   =>  array( 
                array(
                    'server'    =>  $server->id,
                    'url'       =>  'http://www.in.gr',
                    'language'  =>  'java',
                    'developer' =>  'Michael Jordan',
                    'contact'   =>  'mic@gmail.com'  
                )
            )
        );         
        $this->call('POST', '/api/webapps',$post_data,[],[],['HTTP_X-CSRF-Token'=>csrf_token(),'contentType'=>'application/json; charset=utf-8'],[]);        
        $this->assertEquals(403,$this->response->getStatusCode(),'You should not be able to add webapps to a server you cannot manage!');
        
    }
    
    /** @test */
    public function update_webapps(){    
        
        $this->be($this->admin);  
        
        // Try to update a webapp you manage
        $webapp = Webapp::where('url','http://www.iefimerida.gr')->first();
        $post_data = array(
            'webapps'   =>  array(
                array(
                    'id'        =>  $webapp->id,
                    'server'    =>  $webapp->server,
                    'url'       =>  'http://www.iefimerida2.gr',
                    'language'  =>  'php',
                    'developer' =>  'Aris Tomas',
                    'contact'   =>  'aris@tomas.com'
                )                
            )
        ); 
        
        $this->call('PUT', '/api/webapps',$post_data,[],[],['HTTP_X-CSRF-Token'=>csrf_token(),'contentType'=>'application/json; charset=utf-8'],[]);
        $webapps = json_decode($this->response->getContent());
        $this->assertEquals(200,$this->response->getStatusCode(),'A user should be able to update a webapp on a server he can manage');
        $this->assertEquals(1,count($webapps),'Updating one webapp should return one webapp as response.');
        $this->assertEquals('http://www.iefimerida2.gr',$webapps[0]->url,'The url of the updated webapp in the response should be http://www.iefimerida2.gr');
        
        // Try to update a webapp you don't manage
        $webapp = Webapp::where('url','http://www.protagon.gr')->first();
        $post_data = array(
            'webapps'   =>  array(
                array(
                    'id'        =>  $webapp->id,
                    'server'    =>  $webapp->server,
                    'url'       =>  'http://www.protagon.gr',
                    'language'  =>  'java',
                    'developer' =>  'Michael Jordan',
                    'contact'   =>  'mic@gmail.com'
                )                
            )
        ); 
        $this->call('PUT', '/api/webapps',$post_data,[],[],['HTTP_X-CSRF-Token'=>csrf_token(),'contentType'=>'application/json; charset=utf-8'],[]);
        $this->assertEquals(403,$this->response->getStatusCode(),'A user should not be able to update a webapp on a server he cannot manage');
        
    }
    
    /** @test */
    public function delete_webapps(){    
        
        $this->be($this->admin);  
        
        // Try to delete a webapp on a server you can manage
        $webapp = Webapp::where('url','http://www.iefimerida.gr')->first();
        $this->call('DELETE','api/webapps/'.$webapp->id,[],[],[],['HTTP_X-CSRF-Token'=>csrf_token(),'contentType'=>'application/json; charset=utf-8'],[]);
        $this->assertEquals(200,$this->response->getStatusCode(),'A user should be able to delete a service on a server he can manage');
        
        // Try to delete a webapp on a server you cannot manage
        $webapp = Webapp::where('url','http://www.protagon.gr')->first();
        $this->call('DELETE','api/webapps/'.$webapp->id,[],[],[],['HTTP_X-CSRF-Token'=>csrf_token(),'contentType'=>'application/json; charset=utf-8'],[]);
        $this->assertEquals(403,$this->response->getStatusCode(),'A user should not be able to delete a webapp on a server he cannot manage');                 
        
    }
    
    /** @test */
    public function read_webapp_info(){    
        
        $this->be($this->admin);  
        
        // Try to read webapp info from a server you can manage
        $webapp = Webapp::where('url','http://www.iefimerida.gr')->first();         
        $this->visit('api/webapps/'.$webapp->id)->seeJsonEquals([
                'data'  =>  [
                    'id'        =>  $webapp->id,
                    'server'    =>  $webapp->server,
                    'url'       =>  'http://www.iefimerida.gr',
                    'language'  =>  'php',
                    'developer' =>  'Aris Tomas',
                    'contact'   =>  'aris@tomas.com',
                    'watch'     =>  '0'
                ]
        ]);
                
        // Try to read webapp info from a server you cannot manage
        $webapp = Webapp::where('url','http://www.protagon.gr')->first();
        $this->call('GET','api/webapps/'.$webapp->id);
        $this->assertEquals(403,$this->response->getStatusCode(),'You should be not able to read service info from a server you cannot manage!');
    }
    
}