<?php

use Illuminate\Foundation\Testing\WithoutMiddleware;
use Illuminate\Foundation\Testing\DatabaseMigrations;
use Illuminate\Foundation\Testing\DatabaseTransactions;

use App\User;
use App\Models\Domain;
use App\Models\Server;
use App\Models\DomainDelegation;
use App\Models\ServerDelegation;

class DelegationsApiTest extends TestCase
{
    
    protected $admin;
    protected $admin2;
    protected $non_admin;
    protected $non_admin2;
    
    public function setUp(){
        parent::setUp();                   
        $this->artisan("db:seed");
        
        $this->add_sample_users();
        $this->add_sample_domains();
        $this->add_sample_servers();
        $this->add_sample_domain_delegations();        
    }
    
    protected function add_sample_users(){
        factory(User::class,2)->create();   
        factory(User::class,1)->create(['superuser'=>1]);

        $users = User::where('superuser',0)->get();
        $this->non_admin = $users[0];
        $this->non_admin2 = $users[1];
        $users = User::where('superuser',1)->get();
        $this->admin = $users[0];
        $this->admin2 = $users[1];
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
        
        $takis = Domain::where('node_name','takis.gr')->first();
        $dom1 = Domain::where('node_name','dom1')->first();
        
        $s1 = new Server([
            'hostname'  =>  's1',
            'domain'    =>  $dom1->parent_id,
            'ip'        =>  '62.169.226.30',
            'os'        =>  'Windows'
        ]);
        $s1->save();
        
        $s2 = new Server([
            'hostname'  =>  's2',
            'domain'    =>  $dom1->id,
            'ip'        =>  '148.251.138.169',
            'os'        =>  'Linux'
        ]);
        $s2->save();
        
        $s4 = new Server([
            'hostname'  =>  's4',
            'domain'    =>  $takis->id,
            'ip'        =>  '77.235.54.162',
            'os'        =>  'Windows'
        ]);
        $s4->save();                               
        
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
    
    protected function add_sample_server_delegations(){
        
        $server = Server::where('hostname','s4')->first();                               
        $delegation = new ServerDelegation([
            'user_id'   =>  $this->admin2->id,
            'server_id' =>  $server->id
        ]);
        $delegation->save();
        
    } 
    
    
    /** 
     * @test 
     * @group delegationsApi
     */
    public function create_server_delegations(){ 

        // Admin tries to delegate a server to a user who cannot manage server's domain
        $this->be($this->admin);  
        $server = Server::where('hostname','s2')->first();
        $post_data = array(
            'delegations'   =>  array( 
                array(
                    'dtype' =>  'server',
                    'ditem' =>  $server->id,
                    'duser' =>  $this->non_admin2->email       
                )
            )
        );        
        $this->call('POST', '/api/delegations',$post_data,[],[],['HTTP_X-CSRF-Token'=>csrf_token(),'contentType'=>'application/json; charset=utf-8'],[]);
        $this->assertEquals(200,$this->response->getStatusCode(),"An admin should be able to delegate a server to a user who cannot manage server's domaine!");        
        
        // Admin tries to delegate a server to user who manages the server's domain 
        $server = Server::where('hostname','s4')->first();
        $post_data = array(
            'delegations'   =>  array( 
                array(
                    'dtype' =>  'server',
                    'ditem' =>  $server->id,
                    'duser' =>  $this->non_admin->email       
                )
            )
        );        
        $this->call('POST', '/api/delegations',$post_data,[],[],['HTTP_X-CSRF-Token'=>csrf_token(),'contentType'=>'application/json; charset=utf-8'],[]);
        $this->assertEquals(400,$this->response->getStatusCode(),"An admin should be able to delegate a server to a user who cannot manage server's domaine!");
        $delegation = ServerDelegation::where('user_id',$this->non_admin->id)->get()->toArray();
        
        // Non-admin domain manager tries to delegate server
        $this->be($this->non_admin);  
        $server = Server::where('hostname','s4')->first();
        $post_data = array(
            'delegations'   =>  array( 
                array(
                    'dtype' =>  'server',
                    'ditem' =>  $server->id,
                    'duser' =>  $this->non_admin2->email       
                )
            )
        );     
        $this->call('POST', '/api/delegations',$post_data,[],[],['HTTP_X-CSRF-Token'=>csrf_token(),'contentType'=>'application/json; charset=utf-8'],[]);
        $this->assertEquals(401,$this->response->getStatusCode(),"An non admin should not be able to delegate a server to a user who cannot manage server's domaine!");        
    }
    
    /** 
     * @test 
     * @group delegationsApi
     */
    public function create_domain_delegations(){                
        
         // Try to delegate a domain you cannot manage without being superuser   
        $this->be($this->non_admin);  
        $post_data = array(
            'delegations'   =>  array( 
                array(
                    'dtype' =>  'domain',
                    'ditem' =>  'dom1.gougousis.gr',
                    'duser' =>  $this->non_admin->email       
                )
            )
        );         
        $this->call('POST', '/api/delegations',$post_data,[],[],['HTTP_X-CSRF-Token'=>csrf_token(),'contentType'=>'application/json; charset=utf-8'],[]);        
        $this->assertEquals(401,$this->response->getStatusCode(),'A non superuser should not be able to delegate a domain he cannot manage!');
                        
        // Delegate a domain you can manage, being a superuser        
        $this->be($this->admin);  
        $post_data = array(
            'delegations'   =>  array( 
                array(
                    'dtype' =>  'domain',
                    'ditem' =>  'dom1.gougousis.gr',
                    'duser' =>  $this->non_admin->email       
                )
            )
        );         
        $this->call('POST', 'api/delegations',$post_data,[],[],['HTTP_X-CSRF-Token'=>csrf_token(),'contentType'=>'application/json; charset=utf-8'],[]);
        $this->assertEquals(200,$this->response->getStatusCode(),'Superuser should be able to make domain delegations!');
        $delegations = json_decode($this->response->getContent());
        $this->assertEquals(1,count($delegations),'When making 1 delegation, the response should contain 1 delegation.');
        $domain = Domain::findByFullname('dom1.gougousis.gr');
        $this->assertEquals($domain->id,$delegations[0]->domain_id);
        
        // Check if the delegation works
        $userServers = Server::allUserServers($this->non_admin->id);
        $this->assertEquals(2,count($userServers));    
        
        // Delegate a domain to a user that already manages a server in this domain  
        $this->add_sample_server_delegations();
        $post_data = array(
            'delegations'   =>  array( 
                array(
                    'dtype' =>  'domain',
                    'ditem' =>  'takis.gr',
                    'duser' =>  $this->admin2->email       
                )
            )
        );  
        $this->call('POST', 'api/delegations',$post_data,[],[],['HTTP_X-CSRF-Token'=>csrf_token(),'contentType'=>'application/json; charset=utf-8'],[]);        
        $this->assertEquals(200,$this->response->getStatusCode(),'A domain can be delegated to a user that already manages a server in this domain!');
        // Check that the server delegation in this domain has been deleted
        $s4 = Server::where('hostname','s4')->first();       
        $delegation = ServerDelegation::where('user_id',$this->admin2->id)->where('server_id',$s4->id)->first();
        $this->assertTrue(empty($delegation));
    }
    
    /** 
     * @test 
     * @group delegationsApi
     */
    public function delete_domain_delegation(){          
        
        $delegation = DomainDelegation::where('user_id',$this->admin2->id)->first();
        
        // Try to delete a domain delegation when you are not superuser and you cannot manage this domain
        $this->be($this->non_admin);        
        $this->call('DELETE','api/delegations/domain/'.$delegation->id,[],[],[],['HTTP_X-CSRF-Token'=>csrf_token(),'contentType'=>'application/json; charset=utf-8'],[]);
        $this->assertEquals(401,$this->response->getStatusCode(),'A user who is not superuser and cannot manage this domain, should not be able to delete a domain delegation of this domain.');
        
        // Try to delete a domain delegation when you are not superuser and you can manage this domain
        $this->be($this->non_admin2);        
        $this->call('DELETE','api/delegations/domain/'.$delegation->id,[],[],[],['HTTP_X-CSRF-Token'=>csrf_token(),'contentType'=>'application/json; charset=utf-8'],[]);
        $this->assertEquals(401,$this->response->getStatusCode(),'A user who is not superuser and can manage this domain, should not be able to delete a domain delegation of this domain.');                 
        
        // Try to delete a domain delegation when you are superuser and you cannot manage this domain
        $delegation = DomainDelegation::where('user_id',$this->admin->id)->first();
        $this->be($this->admin2);        
        $this->call('DELETE','api/delegations/domain/'.$delegation->id,[],[],[],['HTTP_X-CSRF-Token'=>csrf_token(),'contentType'=>'application/json; charset=utf-8'],[]);
        $this->assertEquals(200,$this->response->getStatusCode(),'A user who is superuser and cannot manage this domain, should be able to delete a domain delegation of this domain.');   
               
        $newUserDelegations = DomainDelegation::where('user_id',$this->admin->id)->first();
        $this->assertEquals(0,count($newUserDelegations),''); 
    }
    
    /** 
     * @test 
     * @group delegationsApi
     */
    public function delete_server_delegation(){
        $this->add_sample_server_delegations();
        $delegation = ServerDelegation::where('user_id',$this->admin2->id)->first();        
        
        // Try to delete a server delegation when you are not superuser and you cannot manage this domain
        $this->be($this->non_admin2);        
        $this->call('DELETE','api/delegations/server/'.$delegation->id,[],[],[],['HTTP_X-CSRF-Token'=>csrf_token(),'contentType'=>'application/json; charset=utf-8'],[]);
        $this->assertEquals(401,$this->response->getStatusCode(),'A user who is not superuser and cannot manage this domain, should not be able to delete a server delegation in this domain.');        
        
        // Try to delete a server delegation when you are not superuser and you can manage this domain
        $this->be($this->non_admin);        
        $this->call('DELETE','api/delegations/server/'.$delegation->id,[],[],[],['HTTP_X-CSRF-Token'=>csrf_token(),'contentType'=>'application/json; charset=utf-8'],[]);
        $this->assertEquals(401,$this->response->getStatusCode(),'A user who is not superuser and can manage this domain, should not be able to delete a server delegation in this domain.');        
        
        // Try to delete a server delegation when you are superuser and you cannot manage this domain
        $this->be($this->admin);        
        $this->call('DELETE','api/delegations/server/'.$delegation->id,[],[],[],['HTTP_X-CSRF-Token'=>csrf_token(),'contentType'=>'application/json; charset=utf-8'],[]);
        $this->assertEquals(200,$this->response->getStatusCode(),'A user who is superuser and cannot manage this domain, should be able to delete a server delegation in this domain.');        
        
    }
    
    /** 
     * @test 
     * @group delegationsApi
     */
    public function search_delegations(){ 
        $this->add_sample_server_delegations();        
        $server = Server::where('hostname','s4')->first();                
        
        $this->be($this->admin);  
        $this->call('GET','api/delegations');
        $response = json_decode($this->response->getContent());
        $this->assertObjectHasAttribute('domain_delegations',$response);
        $this->assertObjectHasAttribute('server_delegations',$response);
        $this->assertObjectHasAttribute('gougousis.gr',$response->domain_delegations);
        
        $n = 'gougousis.gr';
        $structure = [
            [
                'id'        =>  1,  
                'full_name' =>  'gougousis.gr',
                'email'     =>  $this->admin->email,
                'firstname' =>  $this->admin->firstname,
                'lastname'  =>  $this->admin->lastname
            ]
        ];
        $expected = json_encode($structure);
        $actual = json_encode($response->domain_delegations->{$n});
        $this->assertEquals($actual,$expected);
        
        $structure = [
            $server->id =>  [
                [
                    'id'    =>  1,
                    'server_id' =>  $server->id.'',
                    'email'     =>  $this->admin2->email,
                    'firstname' =>  $this->admin2->firstname,
                    'lastname'  =>  $this->admin2->lastname
                ]
            ]            
        ];
        $expected = json_encode($structure);
        $actual = json_encode($response->server_delegations);
        $this->assertEquals($actual,$expected);        
        
    }
    
    
}