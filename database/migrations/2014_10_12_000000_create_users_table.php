<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateUsersTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('users', function (Blueprint $table) {
            // $table->string('id')->primary();

            $table->string('id')->primary();
            $table->string('firstName');
            $table->string('lastName')->nullable();
            $table->string('profilePic')->nullable();
            $table->boolean('isActive')->default(true);
            $table->boolean('attendaceRequired')->default(false);
            $table->string('mobileNumber')->unique();
            $table->string('email')->unique();
            $table->timestamp('email_verified_at')->nullable();
            $table->string('password');
            $table->enum('roles', [
                'Super Admin', 
                'Company Admin', 
                'Company Accountant', 
                'Branch Admin', 
                'Branch Accountant', 
                'Branch Manager', 
                'Branch Order Manager', 
                'Kitchen Manager', 
                'Bearer'
            ]);
            $table->rememberToken();
            $table->timestamps();
            $table->boolean('isSync')->default(false);
        });


        Schema::create('user_attendances', function (Blueprint $table) {
            $table->string('id')->primary();
            $table->date('effectedDate');
            $table->boolean('isPresent')->default(false);
            $table->text('description')->nullable();
            $table->string('user_id');
            $table->foreign('user_id')->references('id')->on('users'); 
            $table->unique(['effectedDate', 'user_id']);
            $table->timestamps();
            $table->boolean('isSync')->default(false);
        });
        
        Schema::create('companies', function (Blueprint $table) {
            $table->string('id')->primary();
            $table->string('companyLogo')->nullable();
            $table->string('companyName', 191)->unique();
            $table->text('companyDetails')->nullable();
            $table->text('apiKey')->nullable();
            $table->integer('numberOfBranchesAllowed')->default(1);
            $table->boolean('enableAccounting')->default(true);
            $table->boolean('enableRestaurantFunctions')->default(true);
            $table->boolean('isActive')->default(true);
            $table->timestamps();
            $table->boolean('isSync')->default(false);
        });

        Schema::create('branches', function (Blueprint $table) {
            $table->string('id')->primary();
            $table->string('branchLogo')->nullable();
            $table->string('branchTitle', 191)->unique();
            $table->text('description')->nullable();
            $table->text('branchAddress')->nullable();
            $table->string('gstNumber')->nullable();
            $table->boolean('isActive')->default(true);
            $table->string('branchCode')->unique();
            $table->float('taxPercent');
            $table->string('company_id');
            $table->foreign('company_id')->references('id')->on('companies');
            $table->timestamps();
            $table->boolean('isSync')->default(false);
        });

        
        Schema::create('branch_order_types', function (Blueprint $table) {
            $table->string('id')->primary();
            $table->string('orderType')->nullable();
            $table->boolean('tableRequired')->default(false);
            $table->boolean('isActive')->default(true);
            $table->string('branch_id');
            $table->foreign('branch_id')->references('id')->on('branches');  
            $table->unique(['orderType', 'branch_id']);
            $table->timestamps();
            $table->boolean('isSync')->default(false);
        });

        
        Schema::create('branch_kitchens', function (Blueprint $table) {
            $table->string('id')->primary();
            $table->string('kitchenTitle');
            $table->string('branch_id')->nullable(true);
            $table->foreign('branch_id')->references('id')->on('branches');  
            $table->unique(['kitchenTitle', 'branch_id']);
            $table->timestamps();
            $table->boolean('isSync')->default(false);
        });
        
        Schema::create('branch_payment_methods', function (Blueprint $table) {
            $table->string('id')->primary();
            $table->string('methodTitle');
            $table->string('branch_id')->nullable(true);
            $table->foreign('branch_id')->references('id')->on('branches');  
            $table->unique(['methodTitle', 'branch_id']);
            $table->timestamps();
            $table->boolean('isSync')->default(false);
        });

        Schema::table('users', function(Blueprint $table) {
            $table->string('branch_id')->nullable(true);
            $table->foreign('branch_id')->references('id')->on('branches');  
            $table->string('company_id')->nullable(true);
            $table->foreign('company_id')->references('id')->on('companies'); 
        });

        Schema::table('branches', function(Blueprint $table) {
            $table->string('appDefaultOrderType')->nullable(true);
            $table->foreign('appDefaultOrderType')->references('id')->on('branch_order_types'); 
            $table->string('adminDefaultOrderType')->nullable(true);
            $table->foreign('adminDefaultOrderType')->references('id')->on('branch_order_types');  
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('users');
    }
}
