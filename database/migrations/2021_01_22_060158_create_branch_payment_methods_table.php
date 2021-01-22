<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateBranchPaymentMethodsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('branch_payment_methods', function (Blueprint $table) {
            $table->string('id')->primary();
            $table->string('methodTitle');
            $table->string('branch_id')->nullable(true);
            $table->foreign('branch_id')->references('id')->on('branches');  
            $table->unique(['methodTitle', 'branch_id']);
            $table->timestamps();
            $table->boolean('isSync')->default(false);
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('branch_payment_methods');
    }
}
