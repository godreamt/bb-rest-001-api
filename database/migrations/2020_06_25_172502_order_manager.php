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
        
        
        Schema::create('customers', function (Blueprint $table) {
            $table->string('id')->primary();
            $table->string('customerName')->nullable();  
            $table->string('mobileNumber')->unique();  
            $table->string('emailId')->nullable();  
            $table->string('company_id');
            $table->foreign('company_id')->references('id')->on('companies'); 
            $table->string('branch_id');
            $table->foreign('branch_id')->references('id')->on('branches'); 
            $table->timestamps();
        });

        
        Schema::create('categories', function (Blueprint $table) {
            $table->string('id')->primary();
            $table->string('categoryName')->unique();
            $table->text('description')->nullable();
            $table->string('featuredImage')->nullable();
            $table->boolean('isActive')->default(true);
            $table->string('company_id');
            $table->foreign('company_id')->references('id')->on('companies'); 
            $table->string('branch_id');
            $table->foreign('branch_id')->references('id')->on('branches');  
            $table->timestamps();
        });

        
        Schema::create('table_managers', function (Blueprint $table) {
            $table->string('id')->primary();
            $table->string('tableId')->unique();
            $table->string('noOfChair');
            $table->string('bookedChairs')->nullable();
            $table->text('description')->nullable();
            $table->boolean('isReserved')->default(false);
            $table->boolean('isActive')->default(true);
            $table->string('company_id');
            $table->foreign('company_id')->references('id')->on('companies'); 
            $table->string('branch_id');
            $table->foreign('branch_id')->references('id')->on('branches');  
            $table->timestamps();
        });
        
        
        Schema::create('products', function (Blueprint $table) {
            $table->string('id')->primary();
            $table->string('productNumber');
            $table->string('productName');
            $table->string('productSlug')->unique();
            $table->text('description')->nullable();
            $table->string('featuredImage')->nullable();
            $table->string('price')->nullable();
            $table->string('taxPercent')->nullable();
            $table->string('packagingCharges')->nullable();
            $table->boolean('isVeg')->default(false);
            $table->boolean('isActive')->default(true);
            $table->boolean('isOutOfStock')->default(true);
            $table->boolean('isAdvancedPricing')->default(false);
            $table->string('company_id');
            $table->foreign('company_id')->references('id')->on('companies'); 
            $table->string('branch_id');
            $table->foreign('branch_id')->references('id')->on('branches');  
            $table->string('kitchen_id');
            $table->foreign('kitchen_id')->references('id')->on('branch_kitchens');  
            $table->timestamps();
        });

        
        Schema::create('product_addons', function (Blueprint $table) {
            $table->string('id')->primary();
            $table->string('addonTitle');
            $table->string('price');
            $table->string('productId');
            $table->foreign('productId')->references('id')->on('products')->onDelete('cascade');  
            $table->unique(['addonTitle', 'productId']);
            $table->timestamps();
        });

        //advanced pricing tables
        Schema::create('product_price_models', function (Blueprint $table) {
            $table->string('id')->primary();
            $table->string('title');
            $table->string('description');
            $table->string('productId');
            $table->foreign('productId')->references('id')->on('products')->onDelete('cascade');  
            $table->unique(['title', 'productId']);
            $table->timestamps();
        });

        Schema::create('product_price_model_units', function (Blueprint $table) {
            $table->string('id')->primary();
            $table->string('title');
            $table->text('description')->nullable(true);
            $table->string('priceModelId');
            $table->foreign('priceModelId')->references('id')->on('product_price_models')->onDelete('cascade');  
            $table->unique(['title', 'priceModelId']);
            $table->timestamps();
        });

        Schema::create('product_price_model_combinations', function (Blueprint $table) {
            $table->string('id')->primary();
            $table->string('productId');
            $table->foreign('productId')->references('id')->on('products')->onDelete('cascade');  
            $table->timestamps();
        });

        Schema::create('product_p_m_combination_items', function (Blueprint $table) {
            $table->string('id')->primary();
            $table->string('combinationId');
            $table->foreign('combinationId')->references('id')->on('product_price_model_combinations')->onDelete('cascade');  
            $table->string('priceModelUnitId');
            $table->foreign('priceModelUnitId')->references('id')->on('product_price_model_units')->onDelete('cascade');  
            $table->unique(['combinationId', 'priceModelUnitId'], 'combination_with_units');
            $table->timestamps();
        });

        Schema::create('product_advanced_pricings', function (Blueprint $table) {
            $table->string('id')->primary();
            $table->string('productId');
            $table->foreign('productId')->references('id')->on('products')->onDelete('cascade');  
            $table->string('combinationId');
            $table->foreign('combinationId')->references('id')->on('product_price_model_combinations');  
            $table->string('price')->deafult('0');
            $table->timestamps();
        });

        Schema::create('product_advanced_pricing_images', function (Blueprint $table) {
            $table->string('id')->primary();
            $table->string('productId');
            $table->foreign('productId')->references('id')->on('products');  
            $table->string('advancedPricingId')->nullable(true);
            $table->foreign('advancedPricingId')->references('id')->on('product_advanced_pricings');  
            $table->string('price')->deafult('0');
            $table->timestamps();
        });
        //advanced pricing tables

        
        Schema::create('product_categories', function (Blueprint $table) {
            $table->string('product_id');
            $table->foreign('product_id')->references('id')->on('products')->onDelete('cascade');  
            $table->string('category_id')->nullable(true);
            $table->foreign('category_id')->references('id')->on('categories')->onDelete('cascade');  
        });

        
        Schema::create('orders', function (Blueprint $table) {
            $table->string('id')->primary();
            $table->string('customerId')->nullable(true);
            $table->foreign('customerId')->references('id')->on('customers');  
            $table->string('company_id');
            $table->foreign('company_id')->references('id')->on('companies'); 
            $table->string('branch_id');//nneds to add order type enum
            $table->foreign('branch_id')->references('id')->on('branches'); 
            $table->string('takenBy');
            $table->foreign('takenBy')->references('id')->on('users');  
            $table->string('cgst')->nullable();
            $table->text('relatedInfo')->nullable();
            $table->text('customerAddress')->nullable();
            $table->string('sgst')->nullable();
            $table->string('igst')->nullable();
            $table->string('orderItemTotal')->default('0');
            $table->string('orderAmount')->default('0');
            $table->string('packingCharge')->nullable();
            $table->string('extraCharge')->nullable();
            $table->float('taxPercent')->default(0.0);
            $table->boolean('taxDisabled')->default(false);
            $table->string('deliverCharge')->nullable();
            $table->enum('orderStatus', ['new', 'accepted', 'prepairing', 'packing', 'dispatched', 'delivered', 'completed', 'cancelled'])->default('new');
            $table->string('orderType');
            $table->foreign('orderType')->references('id')->on('branch_order_types');  
            $table->timestamps();
        });


        
        Schema::create('order_items', function (Blueprint $table) {
            $table->string('id')->primary();
            $table->string('price');
            $table->string('quantity')->nullable();
            $table->integer('servedQuantity')->default(0);
            $table->integer('productionAcceptedQuantity')->default(0);
            $table->integer('productionReadyQuantity')->default(0);
            $table->integer('productionRejectedQuantity')->default(0);
            $table->string('packagingCharges')->nullable();
            $table->string('totalPrice')->nullable();
            $table->string('orderId');
            $table->foreign('orderId')->references('id')->on('orders');  
            $table->string('productId');
            $table->foreign('productId')->references('id')->on('products');  
            $table->boolean('isParcel')->default(false);
            $table->timestamps();
        });
        
        Schema::create('order_tables', function (Blueprint $table) {
            $table->string('id')->primary();
            $table->string('selectedChairs')->nullable();
            $table->string('orderId');
            $table->foreign('orderId')->references('id')->on('orders');  
            $table->string('tableId');
            $table->foreign('tableId')->references('id')->on('table_managers');  
            $table->timestamps();
        });
        
        Schema::create('order_feedbacks', function (Blueprint $table) {
            $table->string('id')->primary();
            $table->string('rating');
            $table->string('comments')->nullable();
            $table->string('orderId');
            $table->foreign('orderId')->references('id')->on('orders');  
            $table->string('customerId');
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
