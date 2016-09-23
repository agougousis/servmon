<?php

use Illuminate\Foundation\Testing\WithoutMiddleware;
use Illuminate\Foundation\Testing\DatabaseMigrations;
use Illuminate\Foundation\Testing\DatabaseTransactions;

use App\User;
use App\Models\Domain;
use App\Models\Server;
use App\Models\Setting;
use App\Models\DomainDelegation;

class MonitorApiTest extends TestCase
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
        $this->add_sample_domains();
        $this->add_sample_servers();
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
    public function get_monitorable_items(){
        
        // Get monitorable items as admin
        $this->be($this->admin);
        $this->call('GET','api/monitor/items');
        $this->assertEquals(200,$this->response->getStatusCode());
        $items = json_decode($this->response->getContent());
        $this->assertObjectHasAttribute('gougousis.gr',$items);
        
        // Try to get monitorable items as non-admin
        $this->be($this->non_admin);
        $this->call('GET','api/monitor/items');
        $this->assertEquals(401,$this->response->getStatusCode());
    }
    
    /** @test */
    public function update_configuration(){                
        
        $server = Server::find(1);
        $this->assertEquals(0,$server->watch);
        
        $this->be($this->admin);
        $post_data = array(
            'items'   =>  array('server--1','server--2')
        ); 
        
        $this->call('POST', 'api/monitor/items',$post_data,[],[],['HTTP_X-CSRF-Token'=>csrf_token(),'contentType'=>'application/json; charset=utf-8'],[]);
        $this->assertEquals(200,$this->response->getStatusCode());
        $server = Server::find(1);
        $this->assertEquals(1,$server->watch);
        
    }
    
    /** @test */
    public function change_monitoring_status(){  
        
        $status = Setting::find('monitoring_status');
        $this->assertEquals(0,$status->value);
        
        $this->be($this->admin);
        $post_data = array(
            'config'    =>  [
                'monitoring_status'     =>  1,
                'monitoring_period'     =>  30
            ]            
        ); 
        
        $this->call('PUT', 'api/monitor/status',$post_data,[],[],['HTTP_X-CSRF-Token'=>csrf_token(),'contentType'=>'application/json; charset=utf-8'],[]);
        $this->assertEquals(200,$this->response->getStatusCode());
        $status = Setting::find('monitoring_status');
        $this->assertEquals(1,$status->value);
        $status = Setting::find('monitoring_period');
        $this->assertEquals(30,$status->value);
        
    }
    
}