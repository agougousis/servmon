<?php

use Illuminate\Database\Seeder;
use Illuminate\Database\Eloquent\Model;

class UserTableSeeder extends Seeder

{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        DB::table('users')->insert([
            'id'        =>  1,
            'email'     =>  'user1@gmail.com',
            'password'  =>  bcrypt('user1pwd'),
            'firstname' =>  'Alexandros',
            'lastname'  =>  'Gougousis',
            'activated' =>  1,
            'superuser' =>  1
        ]);

    }
}
