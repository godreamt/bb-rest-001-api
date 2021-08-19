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
            'id' => 'SD000000000000000001',
            'firstname' => 'Kiran Shetty',
            'email' => 'shiningkiru@gmail.com',
            'mobileNumber' => '7899866288',
            'password' => Hash::make('12345'),
            'roles' => 'Super Admin',
        ]);

        // DB::table('companies')->insert([
        //     'id' => '1',
        //     'companyName' => 'Black Bamboo'
        // ]);

        // DB::table('branches')->insert([
        //     'id' => '1',
        //     'branchTitle' => 'Mukka',
        //     'branchCode' => 'MK',
        //     'taxPercent' => 5,
        //     'company_id' => 1
        // ]);
        // DB::table('measure_units')->insert([
        //     'id' => '1',
        //     'unitLabel' => 'KG',
        //     'company_id' => 1
        // ]);
    }
}
