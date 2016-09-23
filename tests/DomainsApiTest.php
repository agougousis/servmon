<?php

use Illuminate\Foundation\Testing\WithoutMiddleware;
use Illuminate\Foundation\Testing\DatabaseMigrations;
use Illuminate\Foundation\Testing\DatabaseTransactions;

use App\User;
use App\Models\Domain;

class DomainsApiTest extends TestCase
{
    use DatabaseTransactions;    
    
    protected $admin;
    protected $non_admin;
    
    public function setUp(){
        parent::setUp();                   
        $this->artisan("migrate:refresh");
        $this->artisan("db:seed");
    }
    
    protected function add_sample_data(){ 
        
        // An admin user already exists from seeding. We also need
        // a non-admin user
        factory(User::class,1)->create();        
        
        $this->non_admin = User::where('superuser',0)->first();
        $this->admin = User::where('superuser',1)->first();
        
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
    
     /** @test */
    public function can_search_domains(){ 
        $this->add_sample_data();
        
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
                    ]
                ]
            ],[
                'nid'   =>  3,
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
                    ]
                ]
            ],[
                'nid'   =>  3,
                'text'  =>  'takis.gr'               
            ]
        ]);
       
    }    
    
     /** @test */
    public function can_delete_domains(){ 
        
        $this->add_sample_data();
        $this->be($this->admin);
        
        // Admin - Try to delete a domain that he can manage
        $this->call('DELETE', '/api/domains/dom1.gougousis.gr',[],[],[],['HTTP_X-CSRF-Token'=>csrf_token()]);        
        $this->visit('api/domains')->seeJsonEquals([
            [
                'nid'   =>  1,
                'text'  =>  'gougousis.gr'                
            ],[
                'nid'   =>  3,
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
                ]
            ]
        ]);
        
        // Non-admin - Try to delete a domain that he cannot manage
        $this->call('DELETE', '/api/domains/gougousis.gr',[],[],[],['HTTP_X-CSRF-Token'=>csrf_token()]);
        $this->assertEquals(403,$this->response->getStatusCode(),'ERROR: Non-admin should not be able to delete a domain he cannot manage!');
         
    }

}