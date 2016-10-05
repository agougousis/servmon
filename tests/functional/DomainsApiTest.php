<?php

use Illuminate\Foundation\Testing\WithoutMiddleware;
use Illuminate\Foundation\Testing\DatabaseMigrations;
use Illuminate\Foundation\Testing\DatabaseTransactions;

use App\User;
use App\Models\Domain;
use App\Models\DomainDelegation;

class DomainsApiTest extends TestCase
{
    protected $admin;
    protected $non_admin;
    
    public function setUp(){
        parent::setUp();                   
        $this->artisan("db:seed");
        
        $this->add_sample_users();
        $this->add_sample_domains();
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
    
    /** 
     * @test 
     * @group domainsApi
     */
    public function can_create_domains(){
        // Adding a root domain with subdomain
        $this->be($this->non_admin);  
        $post_data = array(
            'domains'   =>  array( 
                array(
                    'node_name'     =>  'test.gr',
                    'parent_domain' =>  null,
                    'fake_domain'   =>  0
                ),    
                array(
                    'node_name'     =>  'dom1',
                    'parent_domain' =>  'test.gr',
                    'fake_domain'   =>  0
                )
            )
        );         
        $this->call('POST', '/api/domains',$post_data,[],[],['HTTP_X-CSRF-Token'=>csrf_token(),'contentType'=>'application/json; charset=utf-8'],[]);        
        $this->assertEquals(200,$this->response->getStatusCode(),'Adding root domain with subdomain failed!');
        $domains = json_decode($this->response->getContent());
        $this->assertEquals(2,count($domains),'When adding 2 domains, the response should contain 2 domains.');
        $this->assertEquals('test.gr',$domains[0]->node_name);
        
        // Adding a subdomain to a domain you cannot manage 
        $post_data = array(
            'domains'   =>  array( 
                array(
                    'node_name'     =>  'dom7',
                    'parent_domain' =>  'gougousis.gr',
                    'fake_domain'   =>  0
                )
            )
        );         
        $this->call('POST', '/api/domains',$post_data,[],[],['HTTP_X-CSRF-Token'=>csrf_token(),'contentType'=>'application/json; charset=utf-8'],[]);        
        $this->assertEquals(403,$this->response->getStatusCode(),'You should not be able to add a subdomain to a domain you cannot manage!');
    }
    
    /** 
     * @test 
     * @group domainsApi
     */
    public function can_search_domains(){         
        
        // Admin searching
        $this->be($this->admin);        
        $this->visit('api/domains')->seeJsonEquals([
            [
                'nid'   =>  1,
                'text'  =>  'gougousis.gr',
                'children'  =>  [
                    [
                        'nid'   =>  2,
                        'text'  =>  'dom1.gougousis.gr'
                    ],
                    [
                        'nid'   =>  3,
                        'text'  =>  'dom2.gougousis.gr'
                    ]
                ]
            ],[
                'nid'   =>  4,
                'text'  =>  'takis.gr',
                'state' =>  [
                    'disabled'  =>  true
                ]
            ]
        ]);
        
        // Non-admin searching
        $this->be($this->non_admin);
        $this->visit('api/domains')->seeJsonEquals([
            [
                'nid'   =>  1,
                'text'  =>  'gougousis.gr',
                'state' =>  [
                    'disabled'  =>  true
                ],
                'children'  =>  [
                    [
                        'nid'   =>  2,
                        'text'  =>  'dom1.gougousis.gr',
                        'state' =>  [
                            'disabled'  =>  true
                        ]
                    ],[
                        'nid'   =>  3,
                        'text'  =>  'dom2.gougousis.gr',
                        'state' =>  [
                            'disabled'  =>  true
                        ]
                    ]
                ]
            ],[
                'nid'   =>  4,
                'text'  =>  'takis.gr'               
            ]
        ]);
       
    }    
    
    /** 
     * @test 
     * @group domainsApi
     */
    public function can_delete_domains(){ 
                
        $this->be($this->admin);
        
        // Admin - Try to delete a domain that he can manage
        $this->call('DELETE', '/api/domains/dom1.gougousis.gr',[],[],[],['HTTP_X-CSRF-Token'=>csrf_token()]);        
        $this->visit('api/domains')->seeJsonEquals([
            [
                'nid'   =>  1,
                'text'  =>  'gougousis.gr'                ,
                'children'  =>  [                    
                    [
                        'nid'   =>  3,
                        'text'  =>  'dom2.gougousis.gr'
                    ]
                ]
            ],[
                'nid'   =>  4,
                'text'  =>  'takis.gr',
                'state' =>  [
                    'disabled'  =>  true
                ]
            ]
        ]);
        
        // Admin - Try to delete a domain that he cannot manage
        $this->call('DELETE', '/api/domains/takis.gr',[],[],[],['HTTP_X-CSRF-Token'=>csrf_token()]);
        $this->assertEquals(403,$this->response->getStatusCode(),'ERROR: Admin should not be able to delete a domain he cannot manage!');
               
        $this->be($this->non_admin);
        
        // Non-admin - Try to delete a domain that he can manage
        $this->call('DELETE', '/api/domains/takis.gr',[],[],[],['HTTP_X-CSRF-Token'=>csrf_token()]);                
        $this->visit('api/domains')->seeJsonEquals([
            [
                'nid'   =>  1,
                'text'  =>  'gougousis.gr',
                'state' =>  [
                    'disabled'  =>  true
                ],
                'children'  =>  [                    
                    [
                        'nid'   =>  3,
                        'text'  =>  'dom2.gougousis.gr',
                        'state' =>  [
                            'disabled'  =>  true
                        ]
                    ]
                ]
            ]
        ]);
        
        // Non-admin - Try to delete a domain that he cannot manage
        $this->call('DELETE', '/api/domains/gougousis.gr',[],[],[],['HTTP_X-CSRF-Token'=>csrf_token()]);
        $this->assertEquals(403,$this->response->getStatusCode(),'ERROR: Non-admin should not be able to delete a domain he cannot manage!');
         
    }

}