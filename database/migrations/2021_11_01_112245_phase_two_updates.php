<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class PhaseTwoUpdates extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {

        Schema::create('order_item_comments', function (Blueprint $table) {
            $table->string('id')->primary();
            $table->text('description');
            $table->boolean('isPrinted')->default(false);
            $table->boolean('isSync')->default(false);
            $table->string('itemId');
            $table->foreign('itemId')->references('id')->on('order_items')->onDelete('cascade');
            $table->timestamps();
        });
        Schema::create('order_combo_item_comments', function (Blueprint $table) {
            $table->string('id')->primary();
            $table->text('description');
            $table->boolean('isPrinted')->default(false);
            $table->boolean('isSync')->default(false);
            $table->string('itemId');
            $table->foreign('itemId')->references('id')->on('order_item_combos')->onDelete('cascade');
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
