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
            $table->string('mobileNumber')->unique();
            $table->string('email')->unique();
            $table->timestamp('email_verified_at')->nullable();
            $table->string('password');
            $table->enum('roles', ['Super Admin', 'Admin', 'Accountant', 'Order Manager', 'Customer']);
            $table->rememberToken();
            $table->timestamps();
        });
        Schema::create('branches', function (Blueprint $table) {
            $table->id();
            $table->string('branchTitle', 191)->unique();
            $table->text('description')->nullable();
            $table->text('branchAddress')->nullable();
            $table->boolean('isActive')->default(true);
            $table->string('branchCode')->unique();
            $table->timestamps();
        });

        Schema::table('users', function(Blueprint $table) {
            $table->unsignedBigInteger('branch_id')->nullable(true);
            $table->foreign('branch_id')->references('id')->on('branches');  
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
