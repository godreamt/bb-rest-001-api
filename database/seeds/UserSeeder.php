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

        DB::table('companies')->insert([
            'id' => '1',
            'companyName' => 'Black Bamboo'
        ]);

        DB::table('branches')->insert([
            'id' => '1',
            'branchTitle' => 'Mukka',
            'branchCode' => 'MK',
            'taxPercent' => 5,
            'company_id' => 1
        ]);
        
        DB::table('measure_units')->insert([
            'id' => '1',
            'unitLabel' => 'Pc',
            'branch_id' => 1
        ]);

        DB::table('ledger_accounts')->insert([
            "id" => "1",
            "ledgerName" => "Cash Account",
            "accountType" => "Cash Account",
            "description" => "",
            "isActive" => true,
            "branch_id" => "1"
        ]);;

        DB::table('ledger_accounts')->insert([
            "id" => "2",
            "ledgerName" => "Abhi Account",
            "accountType" => "Others Account",
            "description" => "",
            "isActive" => true,
            "branch_id" => "1"
        ]);

        DB::table('inventory_items')->insert([
            "id" => "OFL00000000000000001",
            "itemName" => "Pen",
            "pricePerUnit" => 10,
            "unitId" => "1",
            "description" => null,
            "isActive" => true,
            "branch_id" => "1"
        ]);

        DB::table('inventory_items')->insert([
            "id" => "OFL00000000000000002",
            "itemName" => "Book",
            "pricePerUnit" => 50,
            "unitId" => "1",
            "description" => null,
            "isActive" => true,
            "branch_id" => "1"
        ]);

        DB::table('inventory_items')->insert([
            "id" => "OFL00000000000000003",
            "itemName" => "Chock",
            "pricePerUnit" => 5,
            "unitId" => "1",
            "description" => null,
            "isActive" => true,
            "branch_id" => "1"
        ]);
    }
}
