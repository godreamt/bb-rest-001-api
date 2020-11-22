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

        DB::table('companies')->insert([
            'companyName' => 'Black Bamboo'
        ]);

        DB::table('branches')->insert([
            'branchTitle' => 'Mukka',
            'branchCode' => 'MK',
            'taxPercent' => 5,
            'company_id' => 1
        ]);
    }
}
