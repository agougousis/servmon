<?php

use Illuminate\Database\Seeder;
use Illuminate\Database\Eloquent\Model;

class DatabaseSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        
        if(App::environment() === 'production'){
            exit('No seeding for production environment!');
        }
        
        Model::unguard();

        // Empty all the tables before seeding
        // Some tables that are not seeded are truncated because they are used 
        // in testing and we need to clear them out before each test
        $tables = ['users','service_types','webapp_types','database_types','settings','domains','servers','services','webapps','databases'];
        foreach($tables as $table){
            DB::table($table)->truncate();
        }
        
        $this->call(UserTableSeeder::class);
        $this->call(ServiceTypesTableSeeder::class);
        $this->call(WebappTypesTableSeeder::class);
        $this->call(DatabaseTypesTableSeeder::class);
        $this->call(SettingsTableSeeder::class);
 
        Model::reguard();
    }
}
