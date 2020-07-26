<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class OrderManager extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        
        Schema::create('branches', function (Blueprint $table) {
            $table->id();
            $table->string('branchTitle', 191)->unique();
            $table->text('description')->nullable();
            $table->text('branchAddress')->nullable();
            $table->boolean('isActive')->default(true);
            $table->string('branchCode')->unique();
            $table->timestamps();
        });
        
        Schema::create('customers', function (Blueprint $table) {
            $table->id();
            $table->string('customerName')->nullable();  
            $table->string('mobileNumber')->unique();  
            $table->string('emailId')->nullable();  
            $table->unsignedBigInteger('branch_id');
            $table->foreign('branch_id')->references('id')->on('branches'); 
            $table->timestamps();
        });

        Schema::table('users', function(Blueprint $table) {
            $table->unsignedBigInteger('branch_id')->nullable(true);
            $table->foreign('branch_id')->references('id')->on('branches');  
        });

        
        Schema::create('categories', function (Blueprint $table) {
            $table->id();
            $table->string('categoryName')->unique();
            $table->text('description')->nullable();
            $table->string('featuredImage')->nullable();
            $table->boolean('isActive')->default(true);
            $table->unsignedBigInteger('branch_id');
            $table->foreign('branch_id')->references('id')->on('branches');  
            $table->timestamps();
        });

        

        
        Schema::create('order_types', function (Blueprint $table) {
            $table->id();
            $table->string('typeName')->unique();
            $table->text('description')->nullable();
            $table->boolean('enableTables')->default(true);
            $table->boolean('enableExtraInfo')->default(true);
            $table->boolean('enableDeliverCharge')->default(true);
            $table->boolean('enableExtraCharge')->default(true);
            $table->boolean('isActive')->default(true);
            $table->unsignedBigInteger('branch_id');
            $table->foreign('branch_id')->references('id')->on('branches');  
            $table->timestamps();
        });
        
        Schema::create('table_managers', function (Blueprint $table) {
            $table->id();
            $table->string('tableId');
            $table->string('noOfChair');
            $table->string('bookedChairs')->nullable();
            $table->text('description')->nullable();
            $table->boolean('isReserved')->default(false);
            $table->boolean('isActive')->default(true);
            $table->unsignedBigInteger('orderTypeId')->nullable(true);
            $table->foreign('orderTypeId')->references('id')->on('order_types')->onDelete('cascade');  
            $table->unique(['tableId', 'orderTypeId']);
            $table->unsignedBigInteger('branch_id');
            $table->foreign('branch_id')->references('id')->on('branches');  
            $table->timestamps();
        });
        
        
        Schema::create('products', function (Blueprint $table) {
            $table->id();
            $table->string('productNumber');
            $table->string('productName');
            $table->string('productSlug')->unique();
            $table->text('description')->nullable();
            $table->string('featuredImage')->nullable();
            $table->string('price')->nullable();
            $table->string('taxPercent')->nullable();
            $table->string('packagingCharges')->nullable();
            $table->boolean('isOrderTypePricing')->default(false);
            $table->boolean('isVeg')->default(false);
            $table->boolean('isActive')->default(true);
            $table->unsignedBigInteger('branch_id');
            $table->foreign('branch_id')->references('id')->on('branches');  
            $table->timestamps();
        });

        
        Schema::create('product_order_type_pricings', function (Blueprint $table) {
            $table->id();
            $table->string('price')->nullable();
            $table->string('taxPercent')->nullable();
            $table->string('packagingCharges')->nullable();
            $table->unsignedBigInteger('orderTypeId')->nullable(true);
            $table->foreign('orderTypeId')->references('id')->on('order_types');  
            $table->unsignedBigInteger('productId')->nullable(true);
            $table->foreign('productId')->references('id')->on('products');  
            $table->timestamps();
        });

        
        Schema::create('product_categories', function (Blueprint $table) {
            $table->unsignedBigInteger('product_id')->nullable(true);
            $table->foreign('product_id')->references('id')->on('products')->onDelete('cascade');  
            $table->unsignedBigInteger('category_id')->nullable(true);
            $table->foreign('category_id')->references('id')->on('categories')->onDelete('cascade');  
        });

        
        Schema::create('orders', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('customerId')->nullable(true);
            $table->foreign('customerId')->references('id')->on('customers');  
            $table->unsignedBigInteger('branch_id');
            $table->foreign('branch_id')->references('id')->on('branches'); 
            $table->unsignedBigInteger('orderTypeId')->nullable(true);
            $table->foreign('orderTypeId')->references('id')->on('order_types');  
            $table->unsignedBigInteger('takenBy')->nullable(true);
            $table->foreign('takenBy')->references('id')->on('users');  
            $table->string('cgst')->nullable();
            $table->text('relatedInfo')->nullable();
            $table->string('sgst')->nullable();
            $table->string('igst')->nullable();
            $table->string('orderItemTotal');
            $table->string('orderAmount');
            $table->string('packingCharge')->nullable();
            $table->string('extraCharge')->nullable();
            $table->boolean('excludeFromReport')->default(false);
            $table->string('deliverCharge')->nullable();
            $table->enum('orderStatus', ['new', 'accepted', 'prepairing', 'packing', 'dispatched', 'delivered', 'completed', 'cancelled'])->nullable();
            $table->timestamps();
        });


        
        Schema::create('order_items', function (Blueprint $table) {
            $table->id();
            $table->string('price');
            $table->string('quantity')->nullable();
            $table->string('packagingCharges')->nullable();
            $table->string('totalPrice')->nullable();
            $table->unsignedBigInteger('orderId');
            $table->foreign('orderId')->references('id')->on('orders');  
            $table->unsignedBigInteger('productId');
            $table->foreign('productId')->references('id')->on('products');  
            $table->enum('itemStatus', ['new', 'completed', 'cancelled'])->default('new');
            $table->timestamps();
        });
        
        Schema::create('order_tables', function (Blueprint $table) {
            $table->id();
            $table->string('selectedChairs')->nullable();
            $table->unsignedBigInteger('orderId');
            $table->foreign('orderId')->references('id')->on('orders');  
            $table->unsignedBigInteger('tableId');
            $table->foreign('tableId')->references('id')->on('table_managers');  
            $table->timestamps();
        });
        
        Schema::create('order_feedbacks', function (Blueprint $table) {
            $table->id();
            $table->string('rating');
            $table->string('comments')->nullable();
            $table->unsignedBigInteger('orderId');
            $table->foreign('orderId')->references('id')->on('orders');  
            $table->unsignedBigInteger('customerId');
            $table->foreign('customerId')->references('id')->on('customers');  
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
