<?php

use Illuminate\Database\Seeder;
use Illuminate\Database\Eloquent\Model;

class ServiceTypesTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        DB::table('service_types')->insert([[
            'codename'  =>  'apache',
            'title'     =>  'Apache Web Server',
            'image'     =>  'apache.png',
            'default_port'      =>  80
        ],[
            'codename'  =>  'geoserver',
            'title'     =>  'Geoserver',
            'image'     =>  'geoserver.png',
            'default_port'      =>  8080
        ],[
            'codename'  =>  'glassfish',
            'title'     =>  'Glassfish',
            'image'     =>  'glassfish.png',
            'default_port'      =>  8080
        ],[
            'codename'  =>  'jetty',
            'title'     =>  'Jetty',
            'image'     =>  'jetty.png',
            'default_port'      =>  8080
        ],[
            'codename'  =>  'mariadb',
            'title'     =>  'MariaDB',
            'image'     =>  'mariadb.png',
            'default_port'      =>  3306
        ],[
            'codename'  =>  'mysql',
            'title'     =>  'MySQL',
            'image'     =>  'mysql.png',
            'default_port'      =>  3306
        ],[
            'codename'  =>  'postgres',
            'title'     =>  'PostgreSQL',
            'image'     =>  'postgres.png',
            'default_port'      =>  5432
        ],[
            'codename'  =>  'tomcat',
            'title'     =>  'Tomcat',
            'image'     =>  'tomcat.png',
            'default_port'      =>  8080
        ],[
            'codename'  =>  'virtuoso',
            'title'     =>  'Virtuoso',
            'image'     =>  'virtuoso.png',
            'default_port'      =>  8890
        ]]);

    }
}
