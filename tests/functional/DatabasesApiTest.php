<?php

use Illuminate\Foundation\Testing\WithoutMiddleware;
use Illuminate\Foundation\Testing\DatabaseMigrations;
use Illuminate\Foundation\Testing\DatabaseTransactions;

use App\User;
use App\Models\Domain;
use App\Models\Server;
use App\Models\Database;
use App\Models\DomainDelegation;

class DatabasesApiTest extends TestCase
{

    protected $admin;
    protected $non_admin;

    public function setUp(){
        parent::setUp();
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

    /**
     * @test
     * @group databasesApi
     */
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
        $responseArray = json_decode($this->response->getContent());
        $databases = $responseArray->data;
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

    /**
     * @test
     * @group databasesApi
     */
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
        $responseArray = json_decode($this->response->getContent());
        $databases = $responseArray->data;
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

    /**
     * @test
     * @group databasesApi
     */
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

    /**
     * @test
     * @group databasesApi
     */
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