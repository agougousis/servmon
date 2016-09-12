<?php

use Illuminate\Database\Seeder;
use Illuminate\Database\Eloquent\Model;

class SettingsTableSeeder extends Seeder

{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        DB::table('settings')->insert([[
            'sname'         =>  'monitoring_period',
            'value'         =>  '30',
            'description'   =>  'Monitoring period. We want to check the status of the selected items every x minutes. The number of minutes is determined by this setting.'
        ],[
            'sname'         =>  'monitoring_status',
            'value'         =>  '0',
            'description'   =>  'This setting enables or disables the monitoring.'
        ]]);

    }
}
