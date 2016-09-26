<?php

use Illuminate\Foundation\Testing\WithoutMiddleware;
use Illuminate\Foundation\Testing\DatabaseMigrations;
use Illuminate\Foundation\Testing\DatabaseTransactions;

use App\User;
use App\Models\Domain;
use App\Models\Server;
use App\Models\Service;
use App\Models\DomainDelegation;

class ServicesApiTest extends TestCase
{
    
    protected $admin;
    protected $non_admin;
    
    public function setUp(){
        parent::setUp();                   
        $this->artisan("db:seed");
        
        $this->add_sample_users();
        $this->add_sample_domains();
        $this->add_sample_servers();
        $this->add_sample_services();
    }
    
    protected function add_sample_users(){
        // An admin user already exists from seeding. We also need
        // a non-admin user
        factory(User::class,1)->create();        
        
        $this->non_admin = User::where('superuser',0)->first();
        $this->admin = User::where('superuser',1)->first();
    }
    
    protected function add_sample_domains(){        
        
        // These are supposed to be created by $this->admin
        $gougousis = new Domain([
            'node_name' =>  'gougousis.gr',
            'full_name' =>  'gougousis.gr',
            'fake'      =>  0
        ]);
        $gougousis->save();
        
        $dom1 = new Domain([
            'node_name' =>  'dom1',
            'full_name' =>  'dom1.gougousis.gr',
            'fake'      =>  0
        ]);
        $dom1->save();
        $dom1->makeChildOf($gougousis);
        
        $dom2 = new Domain([
            'node_name' =>  'dom2',
            'full_name' =>  'dom2.gougousis.gr',
            'fake'      =>  0
        ]);
        $dom2->save();
        $dom2->makeChildOf($gougousis);
                                     
        $delegation = new DomainDelegation([
            'user_id'   =>  $this->admin->id,
            'domain_id' =>  $gougousis->id
        ]);
        $delegation->save();
        
        // These are supposed to be created by $this->non_admin
        $takis = new Domain([
            'node_name' =>  'takis.gr',
            'full_name' =>  'takis.gr',
            'fake'      =>  0
        ]);
        $takis->save();   
        
        $delegation = new DomainDelegation([
            'user_id'   =>  $this->non_admin->id,
            'domain_id' =>  $takis->id
        ]);
        $delegation->save();
        
    }
    
    protected function add_sample_servers(){
        $dom1 = Domain::where('node_name','dom1')->first();
        $takis = Domain::where('node_name','takis.gr')->first();
        
        DB::table('servers')->insert([
           [
                'hostname' =>  's1',
                'domain'   =>  $dom1->parent_id,
                'ip'    =>  '62.169.226.30',
                'os'    =>  'Windows'
           ],[
                'hostname' =>  's2',
                'domain'   =>  $dom1->id,
                'ip'    =>  '148.251.138.169',
                'os'    =>  'Linux'
           ],[
                'hostname' =>  's4',
                'domain'   =>  $takis->id,
                'ip'    =>  '77.235.54.162',
                'os'    =>  'Windows'
           ]
        ]);             
    }
    
    protected function add_sample_services(){
        
        $domain = Domain::where('node_name','gougousis.gr')->first();
        $server = Server::where('domain',$domain->id)->first();                
        
        $service = new Service([
            'server'    =>  $server->id,
            'stype'     =>  'tomcat',
            'port'      =>  '8080',
            'version'   =>  '7',
            'watch'     =>  0
        ]);
        $service->owner = $this->admin->id;
        $service->save();
        
        $domain = Domain::where('node_name','takis.gr')->first();
        $server = Server::where('domain',$domain->id)->first();
        
        $service = new Service([
            'server'    =>  $server->id,
            'stype'     =>  'tomcat',
            'port'      =>  '8080',
            'version'   =>  '7',
            'watch'     =>  0            
        ]);
        $service->owner = $this->non_admin->id;
        $service->save();
    }
    
    /** 
     * @test 
     * @group servicesApi
     */
    public function add_services(){    
        
        $this->be($this->non_admin);  
        
        // Add services to a server you can manage
        $domain = Domain::where('full_name','takis.gr')->first();
        $server = Server::where('domain',$domain->id)->first();
        
        $post_data = array(
            'services'   =>  array( 
                array(
                    'server'  =>  $server->id,
                    'stype'   =>  'mysql',
                    'port'    =>  '3306',
                    'version' =>  '5.4'
                ),
                array(
                    'server'  =>  $server->id,
                    'stype'   =>  'apache',
                    'port'    =>  '80',
                    'version' =>  '2.2'
                )
            )
        );         
        $this->call('POST', '/api/services',$post_data,[],[],['HTTP_X-CSRF-Token'=>csrf_token(),'contentType'=>'application/json; charset=utf-8'],[]);        
        $this->assertEquals(200,$this->response->getStatusCode(),'Adding services to a server you can manage failed!');
        $services = json_decode($this->response->getContent());
        $this->assertEquals(2,count($services),'When adding 2 services to a server, the response should contain 2 services.');
        $this->assertEquals('mysql',$services[0]->stype);
        
        // Add services to a server you cannot manage
        $domain = Domain::where('full_name','gougousis.gr')->first();
        $server = Server::where('domain',$domain->id)->first();
        $post_data = array(
            'services'   =>  array( 
                array(
                    'server'  =>  $server->id,
                    'stype'   =>  'mysql',
                    'port'    =>  '3306',
                    'version' =>  '5.4'
                )
            )
        );         
        $this->call('POST', '/api/services',$post_data,[],[],['HTTP_X-CSRF-Token'=>csrf_token(),'contentType'=>'application/json; charset=utf-8'],[]);        
        $this->assertEquals(403,$this->response->getStatusCode(),'You should not be able to add services to a server you cannot manage!');
        
    }
    
    /** 
     * @test 
     * @group servicesApi
     */
    public function update_services(){    
        
        $this->be($this->admin);  
        
        // Try to update a service you manage
        $service = Service::where('stype','tomcat')->where('owner',$this->admin->id)->first();
        $post_data = array(
            'services'   =>  array(
                array(
                    'id'        =>  $service->id,
                    'server'    =>  $service->server,
                    'stype'     =>  'tomcat',
                    'port'      =>  '8084',
                    'version'   =>  '7',
                )                
            )
        ); 
        
        $this->call('PUT', '/api/services',$post_data,[],[],['HTTP_X-CSRF-Token'=>csrf_token(),'contentType'=>'application/json; charset=utf-8'],[]);
        $services = json_decode($this->response->getContent());
        $this->assertEquals(200,$this->response->getStatusCode(),'A user should be able to update a service on a server he can manage');
        $this->assertEquals(1,count($services),'Updating one service should return one service as response.');
        $this->assertEquals('8084',$services[0]->port,'The port of the updated service in the response should be 8084');
        
        // Try to update a service you don't manage
        $service = Service::where('stype','tomcat')->where('owner',$this->non_admin->id)->first();
        $post_data = array(
            'services'   =>  array(
                array(
                    'id'        =>  $service->id,
                    'server'    =>  $service->server,
                    'stype'     =>  'tomcat',
                    'port'      =>  '8089',
                    'version'   =>  '7',
                )                
            )
        ); 
        $this->call('PUT', '/api/services',$post_data,[],[],['HTTP_X-CSRF-Token'=>csrf_token(),'contentType'=>'application/json; charset=utf-8'],[]);
        $this->assertEquals(403,$this->response->getStatusCode(),'A user should not be able to update a service on a server he cannot manage');
        
    }
    
    /** 
     * @test 
     * @group servicesApi
     */
    public function delete_services(){    
        
        $this->be($this->admin);  
        
        // Try to delete a service on a server you can manage
        $service = Service::where('stype','tomcat')->where('owner',$this->admin->id)->first();
        $this->call('DELETE','api/services/'.$service->id,[],[],[],['HTTP_X-CSRF-Token'=>csrf_token(),'contentType'=>'application/json; charset=utf-8'],[]);
        $this->assertEquals(200,$this->response->getStatusCode(),'A user should be able to delete a service on a server he can manage');
        
        // Try to delete a service on a server you cannot manage
        $service = Service::where('stype','tomcat')->where('owner',$this->non_admin->id)->first();
        $this->call('DELETE','api/services/'.$service->id,[],[],[],['HTTP_X-CSRF-Token'=>csrf_token(),'contentType'=>'application/json; charset=utf-8'],[]);
        $this->assertEquals(403,$this->response->getStatusCode(),'A user should not be able to delete a service on a server he cannot manage');                 
        
    }
    
    /** 
     * @test 
     * @group servicesApi
     */
    public function read_service_info(){    
        
        $this->be($this->admin);  
        
        // Try to read service info from a server you can manage
        $service = Service::where('stype','tomcat')->where('owner',$this->admin->id)->first();         
        $this->visit('api/services/'.$service->id)->seeJsonEquals([
                'data'  =>  [
                    'id'    =>  $service->id,
                    'server'=>  $service->server,
                    'stype' =>  'tomcat',
                    'port'  =>  '8080',
                    'version'=> '7',
                    'watch' =>  '0'
                ]
        ]);
                
        // Try to read service info from a server you can manage
        $service = Service::where('stype','tomcat')->where('owner',$this->non_admin->id)->first();
        $this->call('GET','api/services/'.$service->id);
        $this->assertEquals(403,$this->response->getStatusCode(),'You should be not able to read service info from a server you cannot manage!');
    }
    
}