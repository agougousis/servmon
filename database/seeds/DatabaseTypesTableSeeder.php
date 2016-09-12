<?php

use Illuminate\Database\Seeder;
use Illuminate\Database\Eloquent\Model;

class DatabaseTypesTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        DB::table('database_types')->insert([[
            'codename'  =>  'mariadb',
            'title'     =>  'MariaDB',
            'image'     =>  'mariadb.png'
        ],[
            'codename'  =>  'mysql',
            'title'     =>  'MySQL',
            'image'     =>  'mysql.png'
        ],[
            'codename'  =>  'postgres',
            'title'     =>  'PostgreSQL',
            'image'     =>  'postgres.png'
        ],[
            'codename'  =>  'sqlserver',
            'title'     =>  'SQL Server',
            'image'     =>  'sqlserver.png'
        ],[
            'codename'  =>  'virtuoso',
            'title'     =>  'Virtuoso',
            'image'     =>  'virtuoso.png'
        ]]);

    }
}
