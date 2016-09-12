<?php

use Illuminate\Database\Seeder;
use Illuminate\Database\Eloquent\Model;

class WebappTypesTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        DB::table('webapp_types')->insert([[
            'codename'  =>  'j2ee',
            'title'     =>  'J2EE (servlets)',
            'image'     =>  'j2ee.png'
        ],[
            'codename'  =>  'java',
            'title'     =>  'Java',
            'image'     =>  'java.png'
        ],[
            'codename'  =>  'php',
            'title'     =>  'PHP',
            'image'     =>  'php.png'
        ],[
            'codename'  =>  'proxy',
            'title'     =>  'Proxy Virtual Host',
            'image'     =>  'proxy.png'
        ]]);

    }
}
