<?php

use Illuminate\Foundation\Testing\WithoutMiddleware;
use Illuminate\Foundation\Testing\DatabaseMigrations;
use Illuminate\Foundation\Testing\DatabaseTransactions;

use App\User;
use App\Models\Setting;
use App\Models\ServiceType;
use App\Models\WebappType;
use App\Models\DatabaseType;

class DatabaseHealthTest extends TestCase
{
    /** @test */
    public function basic_settings_exist()
    {
        $settings = array_flatten(Setting::select('sname')->get()->toArray());
        
        $this->assertTrue(in_array('monitoring_period',$settings),'monitoring_status parameter is missing from settings table');
        $this->assertTrue(in_array('monitoring_status',$settings),'monitoring_status parameter is missing from settings table.');
    }
    
    /** @test */
    public function basic_service_types_exist()
    {
        $types = array_flatten(ServiceType::select('codename')->get()->toArray());
        
        $this->assertCount(9,$types,'9 service types expected in database.');        
    }
    
    /** @test */
    public function basic_webapp_types_exist()
    {
        $types = array_flatten(WebappType::select('codename')->get()->toArray());
        
        $this->assertCount(4,$types,'4 webapp types expected in database.');        
    }
    
    /** @test */
    public function basic_database_types_exist()
    {
        $types = array_flatten(DatabaseType::select('codename')->get()->toArray());
        
        $this->assertCount(5,$types,'5 database types expected in database.');        
    }
    
    /** @test */
    public function at_least_one_superuser_exists()
    {
        $count_admins = User::where('superuser',1)->get()->count();
        $this->assertGreaterThan(0,$count_admins,'There should be at least one superuser!');
    }
}
