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

        $this->generateRecords(1);
        $this->generateRecords(2);
        
    }

    public function generateRecords($index) {
        

        DB::table('companies')->insert([
            'id' => $index,
            'companyName' => 'Black Bamboo '. $index
        ]);

        DB::table('branches')->insert([
            'id' => $index,
            'branchTitle' => 'Mukka '. $index,
            'branchCode' => 'MK'. $index,
            'taxPercent' => 5,
            'company_id' => $index
        ]);

        
        DB::table('users')->insert([
            'id' => 'SD00000000000000000'.$index."2",
            'firstname' => 'Giri',
            'email' => 'giri'.$index.'@gmail.com',
            'mobileNumber' => '111111111' . $index,
            'password' => Hash::make('Giri@123'),
            'company_id' => $index,
            'branch_id' => $index,
            'roles' => 'Branch Admin',
        ]);
        
        DB::table('measure_units')->insert([
            'id' => $index,
            'unitLabel' => 'Pc',
            'branch_id' => $index
        ]);

        DB::table('ledger_accounts')->insert([
            "id" => $index,
            "ledgerName" => "Cash Account",
            "accountType" => "Cash Account",
            "description" => "",
            "isActive" => true,
            "branch_id" => $index
        ]);;

        DB::table('ledger_accounts')->insert([
            "id" => $index."2",
            "ledgerName" => "Abhi Account",
            "accountType" => "Others Account",
            "description" => "",
            "isActive" => true,
            "branch_id" => $index
        ]);

        DB::table('inventory_items')->insert([
            "id" => "OFL0000000000000000" . $index . "1",
            "itemName" => "Pen",
            "pricePerUnit" => 10,
            "unitId" => $index,
            "description" => null,
            "isActive" => true,
            "branch_id" => $index
        ]);

        DB::table('inventory_items')->insert([
            "id" => "OFL0000000000000000" . $index . "2",
            "itemName" => "Book",
            "pricePerUnit" => 50,
            "unitId" => $index,
            "description" => null,
            "isActive" => true,
            "branch_id" => $index
        ]);

        DB::table('inventory_items')->insert([
            "id" => "OFL0000000000000000" . $index . "3",
            "itemName" => "Chock",
            "pricePerUnit" => 5,
            "unitId" => $index,
            "description" => null,
            "isActive" => true,
            "branch_id" => $index
        ]);
    }
}
