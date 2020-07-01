<?php

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

class CategorySeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $count = 35;
        while($count > 0){
            
            DB::table('categories')->insert([
                'categoryName' => 'cat'.Str::random(10),
                'description' => 'des'.Str::random(25),
            ]);
            $count--;
        }
    }
}
