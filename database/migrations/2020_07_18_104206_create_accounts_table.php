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
            $table->id();
            $table->string('unitLabel');
            $table->text('description')->nullable(true);
            $table->unsignedBigInteger('company_id');
            $table->foreign('company_id')->references('id')->on('companies'); 
            // $table->unsignedBigInteger('branch_id')->nullable(true);
            // $table->foreign('branch_id')->references('id')->on('branches'); 
            $table->boolean('isActive')->default(true);
        });

        Schema::create('inventory_items', function (Blueprint $table) {
            $table->id();
            $table->string('itemName');
            $table->string('pricePerUnit');
            $table->boolean('isActive')->default(true);
            $table->text('description')->nullable(true);
            $table->string('availableStock')->default('0');
            $table->string('lastPurchasedPrice')->default('0');
            $table->unsignedBigInteger('unitId')->nullable(true);
            $table->foreign('unitId')->references('id')->on('measure_units')->onDelete('cascade');  
            $table->unsignedBigInteger('company_id');
            $table->foreign('company_id')->references('id')->on('companies'); 
            $table->unique(['itemName', 'company_id']);
        });

        Schema::create('inventory_item_journals', function (Blueprint $table) {
            $table->id();
            $table->string('quantity');
            $table->string('pricePerUnit');
            $table->text('description')->nullable(true);
            $table->string('totalAmount')->default('0');
            $table->enum('transactionType', ['Damaged Item', 'Wastage', 'Used For Order']);
            $table->unsignedBigInteger('inventoryId')->nullable(true);
            $table->foreign('inventoryId')->references('id')->on('inventory_items')->onDelete('cascade');  
            $table->unsignedBigInteger('orderId')->nullable(true);
            $table->foreign('orderId')->references('id')->on('orders'); 
            $table->unsignedBigInteger('updatedBy')->nullable(true);
            $table->foreign('updatedBy')->references('id')->on('users'); 
            $table->timestamps();
        });

        Schema::create('ledger_accounts', function (Blueprint $table) {
            $table->id();
            $table->string('ledgerName');
            $table->enum('accountType', [
                'Purchase Account',
                'Sales Account',
                'Sundry Creditor',
                'Sundry Debitor',
                'Duties and Taxes',
                'Bank Account',
                'Cash Account',
                'Direct Expense',
                'Indirect Expense',
                'Direct Income',
                'Indirect Income'
            ]);
            // $table->string('openingBalance')->nullable(true);
            // $table->string('taxPercentage')->nullable(true);
            $table->boolean('isActive')->default(true);
            $table->boolean('isAutoCreated')->default(false);
            $table->text('description')->nullable(true);
            $table->unsignedBigInteger('company_id');
            $table->foreign('company_id')->references('id')->on('companies'); 
            $table->unique(['ledgerName', 'company_id']);
        });

        Schema::create('yearly_sheets', function (Blueprint $table) {
            $table->id();
            $table->dateTimeTz('fromDate');
            $table->dateTimeTz('toDate');
            $table->string('amountBrought')->default('0');
            $table->string('amountCarried')->default('0');
            $table->unsignedBigInteger('branch_id')->nullable(true);
            $table->foreign('branch_id')->references('id')->on('branches'); 
            $table->unsignedBigInteger('company_id');
            $table->foreign('company_id')->references('id')->on('companies'); 
            $table->timestamps();
        });

        Schema::create('month_sheets', function (Blueprint $table) {
            $table->id();
            $table->string('month');
            $table->string('year');
            $table->string('amountBrought')->default('0');
            $table->string('totalMonthlyIncome')->default('0');
            $table->string('totalMonthlyExpense')->default('0');
            $table->string('amountCarried')->default('0');
            $table->unsignedBigInteger('branch_id')->nullable(true);
            $table->foreign('branch_id')->references('id')->on('branches'); 
            $table->unsignedBigInteger('company_id');
            $table->foreign('company_id')->references('id')->on('companies'); 
            $table->unsignedBigInteger('yearly_sheet_id');
            $table->foreign('yearly_sheet_id')->references('id')->on('yearly_sheets'); 
            $table->timestamps();
        });

        Schema::create('transactions', function (Blueprint $table) {
            $table->id();
            $table->dateTimeTz('transactionDate');
            $table->string('transactionRefNumber')->nullable(true);
            $table->enum('transactionType', [
                'Purchase',
                'Sales',
                'Payment',
                'Receipt',
            ]);
            $table->string('grandTotal')->nullable(true);
            $table->text('description')->nullable(true);
            $table->unsignedBigInteger('accountId')->nullable(true);
            $table->foreign('accountId')->references('id')->on('ledger_accounts')->onDelete('cascade');  
            $table->unsignedBigInteger('branch_id')->nullable(true);
            $table->foreign('branch_id')->references('id')->on('branches'); 
            $table->unsignedBigInteger('company_id');
            $table->foreign('company_id')->references('id')->on('companies'); 
            $table->unsignedBigInteger('monthly_sheet_id');
            $table->foreign('monthly_sheet_id')->references('id')->on('month_sheets'); 
            $table->unsignedBigInteger('updatedBy')->nullable(true);
            $table->foreign('updatedBy')->references('id')->on('users'); 
            $table->timestamps();
        });

        Schema::create('transaction_items', function (Blueprint $table) {
            $table->id();
            $table->string('quantity');
            $table->string('amount');
            $table->string('total');
            $table->unsignedBigInteger('transactionId')->nullable(true);
            $table->foreign('transactionId')->references('id')->on('transactions')->onDelete('cascade');  
            $table->unsignedBigInteger('itemId')->nullable(true);
            $table->foreign('itemId')->references('id')->on('inventory_items')->onDelete('cascade');  
            $table->timestamps();
        });

        Schema::create('transaction_on_accounts', function (Blueprint $table) {
            $table->id();
            $table->enum('amountProcessType', ['amount', 'percent']);
            $table->string('amountValue');
            $table->string('totalAmount');
            $table->unsignedBigInteger('transactionId');
            $table->foreign('transactionId')->references('id')->on('transactions')->onDelete('cascade');  
            $table->unsignedBigInteger('accountId');
            $table->foreign('accountId')->references('id')->on('ledger_accounts')->onDelete('cascade');  
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
        Schema::dropIfExists('accounts');
    }
}
