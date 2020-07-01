<?php

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;

class UserSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        
        DB::table('users')->insert([
            'firstname' => 'kiran',
            'email' => 'shiningkiru@gmail.com',
            'mobileNumber' => '7899866288',
            'password' => Hash::make('123456'),
            'roles' => 'Super Admin',
        ]);
    }
}
