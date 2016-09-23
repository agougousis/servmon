<?php

use Illuminate\Foundation\Testing\WithoutMiddleware;
use Illuminate\Foundation\Testing\DatabaseMigrations;
use Illuminate\Foundation\Testing\DatabaseTransactions;

use App\User;

class InfoApiTest extends TestCase
{
    
    protected $admin;
    protected $non_admin;
    
    public function setUp(){
        parent::setUp();                   
        $this->artisan("migrate:refresh");
        $this->artisan("db:seed");
        
        $this->add_sample_users();
    }
    
    protected function add_sample_users(){
        // An admin user already exists from seeding. We also need
        // a non-admin user
        factory(User::class,1)->create();        

        $this->non_admin = User::where('superuser',0)->first();
        $this->admin = User::where('superuser',1)->first();
    }
    
    /** @test */
    public function retrieve_list_of_supported_types(){
        
        $this->be($this->admin);
        $this->call('GET','api/info/supported_types');
        $this->assertEquals(200,$this->response->getStatusCode());
        $list = json_decode($this->response->getContent());
        $this->assertEquals(9,count($list->service));
        $this->assertEquals(4,count($list->webapp));
        $this->assertEquals(5,count($list->database));
    }
    
    /** @test */
    public function retrieve_settings(){
        $this->be($this->admin);
        $this->call('GET','api/info/settings');
        $this->assertEquals(200,$this->response->getStatusCode());
        $list = json_decode($this->response->getContent());
        $this->assertObjectHasAttribute('monitoring_period',$list);
        $this->assertObjectHasAttribute('monitoring_status',$list);        
    }
    
    /** @test */
    public function retrieve_my_profile(){
        $this->be($this->admin);
        // We are going to retrieve admin's profile
        $this->call('GET','api/info/myprofile');
        $this->assertEquals(200,$this->response->getStatusCode());
        $list = json_decode($this->response->getContent());
        $this->assertObjectHasAttribute('email',$list);
        $this->assertEquals('user1@gmail.com',$list->email);
    }
    
}