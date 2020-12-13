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
            $table->id();
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
        });


        Schema::create('user_attendances', function (Blueprint $table) {
            $table->id();
            $table->date('effectedDate');
            $table->boolean('isPresent')->default(false);
            $table->text('description')->nullable();
            $table->unsignedBigInteger('user_id');
            $table->foreign('user_id')->references('id')->on('users'); 
            $table->unique(['effectedDate', 'user_id']);
            $table->timestamps();
        });
        
        Schema::create('companies', function (Blueprint $table) {
            $table->id();
            $table->string('companyLogo')->nullable();
            $table->string('companyName', 191)->unique();
            $table->text('companyDetails')->nullable();
            $table->text('apiKey')->nullable();
            $table->integer('numberOfBranchesAllowed')->default(1);
            $table->boolean('enableAccounting')->default(true);
            $table->boolean('enableRestaurantFunctions')->default(true);
            $table->boolean('isActive')->default(true);
            $table->timestamps();
        });

        Schema::create('branches', function (Blueprint $table) {
            $table->id();
            $table->string('branchLogo')->nullable();
            $table->string('branchTitle', 191)->unique();
            $table->text('description')->nullable();
            $table->text('branchAddress')->nullable();
            $table->boolean('isActive')->default(true);
            $table->string('branchCode')->unique();
            $table->float('taxPercent');
            $table->unsignedBigInteger('company_id');
            $table->foreign('company_id')->references('id')->on('companies');
            $table->timestamps();
        });

        
        Schema::create('branch_order_types', function (Blueprint $table) {
            $table->id();
            $table->string('orderType')->nullable();
            $table->boolean('tableRequired')->default(false);
            $table->boolean('isActive')->default(true);
            $table->unsignedBigInteger('branch_id');
            $table->foreign('branch_id')->references('id')->on('branches');  
            $table->unique(['orderType', 'branch_id']);
            $table->timestamps();
        });

        
        Schema::create('branch_kitchens', function (Blueprint $table) {
            $table->id();
            $table->string('kitchenTitle');
            $table->unsignedBigInteger('branch_id')->nullable(true);
            $table->foreign('branch_id')->references('id')->on('branches');  
            $table->unique(['kitchenTitle', 'branch_id']);
            $table->timestamps();
        });

        Schema::table('users', function(Blueprint $table) {
            $table->unsignedBigInteger('branch_id')->nullable(true);
            $table->foreign('branch_id')->references('id')->on('branches');  
            $table->unsignedBigInteger('company_id')->nullable(true);
            $table->foreign('company_id')->references('id')->on('companies');  
        });

        Schema::table('branches', function(Blueprint $table) {
            $table->unsignedBigInteger('appDefaultOrderType')->nullable(true);
            $table->foreign('appDefaultOrderType')->references('id')->on('branch_order_types'); 
            $table->unsignedBigInteger('adminDefaultOrderType')->nullable(true);
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
