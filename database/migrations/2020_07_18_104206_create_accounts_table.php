<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateAccountsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('measure_units', function (Blueprint $table) {
            $table->string('id')->primary();
            $table->string('unitLabel');
            $table->text('description')->nullable(true);
            $table->string('branch_id');
            $table->foreign('branch_id')->references('id')->on('branches'); 
            $table->boolean('isActive')->default(true);
            $table->boolean('isSync')->default(false);
        });

        Schema::create('inventory_items', function (Blueprint $table) {
            $table->string('id')->primary();
            $table->string('itemName');
            $table->string('pricePerUnit');
            $table->boolean('isActive')->default(true);
            $table->text('description')->nullable(true);
            $table->string('lastPurchasedPrice')->default('0');
            $table->string('availableStock')->default('0');
            $table->string('unitId')->nullable(true);
            $table->foreign('unitId')->references('id')->on('measure_units')->onDelete('cascade');  
            $table->string('branch_id');
            $table->foreign('branch_id')->references('id')->on('branches'); 
            $table->unique(['itemName', 'branch_id']);
            $table->boolean('isSync')->default(false);
        });

        Schema::create('inventory_item_journals', function (Blueprint $table) {
            $table->string('id')->primary();
            $table->string('quantity');
            $table->string('pricePerUnit');
            $table->text('description')->nullable(true);
            $table->string('totalAmount')->default('0');
            $table->enum('transactionType', ['Damaged Item', 'Wastage', 'Used For Order']);
            $table->string('inventoryId')->nullable(true);
            $table->foreign('inventoryId')->references('id')->on('inventory_items')->onDelete('cascade');  
            $table->string('orderId')->nullable(true);
            $table->foreign('orderId')->references('id')->on('orders'); 
            $table->string('updatedBy')->nullable(true);
            $table->foreign('updatedBy')->references('id')->on('users'); 
            $table->timestamps();
            $table->boolean('isSync')->default(false);
        });

        
        // Schema::create('inventory_item_managers', function (Blueprint $table) {
        //     $table->string('id')->primary();
        //     $table->string('availableStock')->default('0');
        //     $table->string('lastPurchasedPrice')->default('0');
        //     $table->string('inventoryId');
        //     $table->foreign('inventoryId')->references('id')->on('inventory_items')->onDelete('cascade');
        //     $table->string('branch_id')->nullable(true);
        //     $table->foreign('branch_id')->references('id')->on('branches'); 
        //     $table->unique(['inventoryId', 'branch_id']);  
        //     $table->timestamps();
        //     $table->boolean('isSync')->default(false);
        // });

        Schema::create('ledger_accounts', function (Blueprint $table) {
            $table->string('id')->primary();
            $table->string('ledgerName');
            $table->enum('accountType', [
                'Purchase Account',
                'Sales Account',
                'Others Account',
                'Duties and Taxes',
                'Bank Account',
                'Cash Account',
                'Expenses',
                'Incomes'
            ]);
            // $table->string('openingBalance')->nullable(true);
            // $table->string('taxPercentage')->nullable(true);
            $table->boolean('isActive')->default(true);
            $table->boolean('isAutoCreated')->default(false);
            $table->text('description')->nullable(true);
            $table->string('branch_id');
            $table->foreign('branch_id')->references('id')->on('branches'); 
            $table->unique(['ledgerName', 'branch_id']);
            $table->boolean('isSync')->default(false);
        });

        Schema::create('yearly_sheets', function (Blueprint $table) {
            $table->string('id')->primary();
            $table->dateTimeTz('fromDate');
            $table->dateTimeTz('toDate');
            $table->string('amountBrought')->default('0');
            $table->string('amountCarried')->default('0');
            $table->string('branch_id')->nullable(true);
            $table->foreign('branch_id')->references('id')->on('branches'); 
            $table->timestamps();
            $table->boolean('isSync')->default(false);
        });

        Schema::create('month_sheets', function (Blueprint $table) {
            $table->string('id')->primary();
            $table->string('month');
            $table->string('year');
            $table->string('amountBrought')->default('0');
            $table->string('totalMonthlyIncome')->default('0');
            $table->string('totalMonthlyExpense')->default('0');
            $table->string('amountCarried')->default('0');
            $table->string('branch_id')->nullable(true);
            $table->foreign('branch_id')->references('id')->on('branches'); 
            $table->string('yearly_sheet_id');
            $table->foreign('yearly_sheet_id')->references('id')->on('yearly_sheets'); 
            $table->timestamps();
            $table->boolean('isSync')->default(false);
        });

        Schema::create('transactions', function (Blueprint $table) {
            $table->string('id')->primary();
            $table->dateTimeTz('transactionDate');
            $table->string('transactionRefNumber');
            $table->enum('transactionType', [
                'purchase',
                'sales',
                'payment',
                'receipt',
            ]);
            $table->string('grandTotal')->nullable(true);
            $table->text('description')->nullable(true);
            $table->string('accountId')->nullable(true);
            $table->foreign('accountId')->references('id')->on('ledger_accounts')->onDelete('cascade');  
            $table->string('branch_id');
            $table->foreign('branch_id')->references('id')->on('branches'); 
            $table->string('monthly_sheet_id');
            $table->foreign('monthly_sheet_id')->references('id')->on('month_sheets'); 
            $table->string('updatedBy')->nullable(true);
            $table->foreign('updatedBy')->references('id')->on('users'); 
            $table->unique('branch_id', 'transactionRefNumber');
            $table->timestamps();
            $table->boolean('isSync')->default(false);
        });

        Schema::create('transaction_items', function (Blueprint $table) {
            $table->string('id')->primary();
            $table->string('quantity');
            $table->string('amount');
            $table->string('total');
            $table->string('transactionId')->nullable(true);
            $table->foreign('transactionId')->references('id')->on('transactions');  
            $table->string('itemId')->nullable(true);
            $table->foreign('itemId')->references('id')->on('inventory_items');  
            $table->timestamps();
            $table->boolean('isSync')->default(false);
        });

        Schema::create('transaction_on_accounts', function (Blueprint $table) {
            $table->string('id')->primary();
            $table->enum('amountProcessType', ['amount', 'percent']);
            $table->string('amountValue');
            $table->string('totalAmount');
            $table->string('transactionId');
            $table->foreign('transactionId')->references('id')->on('transactions');  
            $table->string('accountId');
            $table->foreign('accountId')->references('id')->on('ledger_accounts');  
            $table->timestamps();
            $table->boolean('isSync')->default(false);
        });

        Schema::create('transaction_account_journals', function (Blueprint $table) {
            $table->string('id')->primary();
            $table->text('description');
            $table->dateTimeTz('transactionDate');
            $table->string('endingBalance')->default(0);
            $table->string('transactionAmount')->default(0);
            $table->string('branch_id');
            $table->foreign('branch_id')->references('id')->on('branches'); 
            $table->string('transactionId')->nullable(true);
            $table->foreign('transactionId')->references('id')->on('transactions');  
            $table->string('transactionAccountId')->nullable(true);
            $table->foreign('transactionAccountId')->references('id')->on('transaction_on_accounts');  
            $table->string('accountId');
            $table->foreign('accountId')->references('id')->on('ledger_accounts');  
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
        Schema::dropIfExists('accounts');
    }
}
