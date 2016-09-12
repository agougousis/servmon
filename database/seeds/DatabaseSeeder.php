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
        $tables = ['users','service_types','webapp_types','database_types','settings'];
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
