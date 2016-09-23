<?php

use Illuminate\Foundation\Testing\WithoutMiddleware;
use Illuminate\Foundation\Testing\DatabaseMigrations;
use Illuminate\Foundation\Testing\DatabaseTransactions;

use App\User;
use App\Models\Domain;
use App\Models\Server;
use App\Models\Database;

class DatabasesApiTest extends TestCase
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
        $this->add_sample_databases();
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
    
    protected function add_sample_databases(){
        $domain = Domain::where('node_name','gougousis.gr')->first();
        $server = Server::where('domain',$domain->id)->first();                
        
        $database = new Database([
            'server'    =>  $server->id,
            'dbname'    =>  'gougousisdb',
            'type'      =>  'mysql'
        ]);
        $database->owner = $this->admin->id;
        $database->save();
        
        $domain = Domain::where('node_name','takis.gr')->first();
        $server = Server::where('domain',$domain->id)->first();
        
        $database = new Database([
            'server'    =>  $server->id,
            'dbname'    =>  'takisdb',
            'type'      =>  'mysql'          
        ]);
        $database->owner = $this->non_admin->id;
        $database->save();
    }
    
    /** @test */
    public function add_databases(){    
        
        $this->be($this->non_admin);  
        
        // Add databases to a server you can manage
        $domain = Domain::where('full_name','takis.gr')->first();
        $server = Server::where('domain',$domain->id)->first();
        
        $post_data = array(
            'databases'   =>  array( 
                array(
                    'server'    =>  $server->id,
                    'dbname'    =>  'takis2db',
                    'type'      =>  'mysql'       
                ),
                array(
                    'server'    =>  $server->id,
                    'dbname'    =>  'takis3db',
                    'type'      =>  'postgres'      
                )
            )
        );         
        $this->call('POST', '/api/databases',$post_data,[],[],['HTTP_X-CSRF-Token'=>csrf_token(),'contentType'=>'application/json; charset=utf-8'],[]);        
        $this->assertEquals(200,$this->response->getStatusCode(),'Adding databases to a server you can manage failed!');
        $databases = json_decode($this->response->getContent());
        $this->assertEquals(2,count($databases),'When adding 2 databases to a server, the response should contain 2 databases.');
        $this->assertEquals('takis2db',$databases[0]->dbname);
        
        // Add webapps to a server you cannot manage
        $domain = Domain::where('full_name','gougousis.gr')->first();
        $server = Server::where('domain',$domain->id)->first();
        $post_data = array(
            'databases'   =>  array( 
                array(
                    'server'    =>  $server->id,
                    'dbname'    =>  'gougousis2db',
                    'type'      =>  'mysql'  
                )
            )
        );         
        $this->call('POST', '/api/databases',$post_data,[],[],['HTTP_X-CSRF-Token'=>csrf_token(),'contentType'=>'application/json; charset=utf-8'],[]);        
        $this->assertEquals(403,$this->response->getStatusCode(),'You should not be able to add databases to a server you cannot manage!');
        
    }
    
    /** @test */
    public function update_databases(){    
        
        $this->be($this->admin);  
        
        // Try to update a database you manage
        $database = Database::where('dbname','gougousisdb')->first();
        $post_data = array(
            'databases'   =>  array(
                array(
                    'id'        =>  $database->id,
                    'server'    =>  $database->server,
                    'dbname'    =>  'gougousis3db',
                    'type'      =>  'mysql'
                )                
            )
        ); 
        
        $this->call('PUT', '/api/databases',$post_data,[],[],['HTTP_X-CSRF-Token'=>csrf_token(),'contentType'=>'application/json; charset=utf-8'],[]);
        $databases = json_decode($this->response->getContent());
        $this->assertEquals(200,$this->response->getStatusCode(),'A user should be able to update a database on a server he can manage');
        $this->assertEquals(1,count($databases),'Updating one database should return one database as response.');
        $this->assertEquals('gougousis3db',$databases[0]->dbname,'The dbname of the updated database in the response should be gougousis3db');
        
        // Try to update a database you don't manage
        $database = Database::where('dbname','takisdb')->first();
        $post_data = array(
            'databases'   =>  array(
                array(
                    'id'        =>  $database->id,
                    'server'    =>  $database->server,
                    'dbname'    =>  'takis4db',
                    'type'      =>  'mysql'
                )                
            )
        ); 
        $this->call('PUT', '/api/databases',$post_data,[],[],['HTTP_X-CSRF-Token'=>csrf_token(),'contentType'=>'application/json; charset=utf-8'],[]);
        $this->assertEquals(403,$this->response->getStatusCode(),'A user should not be able to update a database on a server he cannot manage');
        
    }
    
    /** @test */
    public function delete_databases(){    
        
        $this->be($this->admin);  
        
        // Try to delete a database on a server you can manage
        $database = Database::where('dbname','gougousisdb')->first();
        $this->call('DELETE','api/databases/'.$database->id,[],[],[],['HTTP_X-CSRF-Token'=>csrf_token(),'contentType'=>'application/json; charset=utf-8'],[]);
        $this->assertEquals(200,$this->response->getStatusCode(),'A user should be able to delete a database on a server he can manage');
        
        // Try to delete a database on a server you cannot manage
        $database = Database::where('dbname','takisdb')->first();
        $this->call('DELETE','api/databases/'.$database->id,[],[],[],['HTTP_X-CSRF-Token'=>csrf_token(),'contentType'=>'application/json; charset=utf-8'],[]);
        $this->assertEquals(403,$this->response->getStatusCode(),'A user should not be able to delete a database on a server he cannot manage');                 
        
    }
    
    /** @test */
    public function read_database_info(){    
        
        $this->be($this->admin);  
        
        // Try to read database info from a server you can manage
        $database = Database::where('dbname','gougousisdb')->first(); 
        $this->visit('api/databases/'.$database->id)->seeJsonEquals([
                'data'  =>  [
                    'id'        =>  $database->id,
                    'server'    =>  $database->server,
                    'dbname'    =>  'gougousisdb',
                    'type'      =>  'mysql',
                    'related_webapp'    =>  null
                ]
        ]);
                
        // Try to read database info from a server you cannot manage
        $database = Database::where('dbname','takisdb')->first();
        $this->call('GET','api/databases/'.$database->id);
        $this->assertEquals(403,$this->response->getStatusCode(),'You should be not able to read database info from a server you cannot manage!');
    }
    
}