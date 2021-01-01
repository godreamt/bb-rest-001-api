<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class TestMigration extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('inventory_item_managers', function (Blueprint $table) {
            $table->id();
            $table->string('availableStock')->default('0');
            $table->string('lastPurchasedPrice')->default('0');
            $table->unsignedBigInteger('inventoryId');
            $table->foreign('inventoryId')->references('id')->on('inventory_items')->onDelete('cascade');
            $table->unsignedBigInteger('company_id');
            $table->foreign('company_id')->references('id')->on('companies'); 
            $table->unsignedBigInteger('branch_id')->nullable(true);
            $table->foreign('branch_id')->references('id')->on('branches'); 
            $table->unique(['inventoryId', 'company_id', 'branch_id']);  
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        //
    }
}
