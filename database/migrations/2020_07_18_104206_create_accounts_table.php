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
            $table->unsignedBigInteger('branch_id');
            $table->foreign('branch_id')->references('id')->on('branches'); 
            $table->unique(['unitLabel', 'branch_id']);
        });

        Schema::create('inventory_items', function (Blueprint $table) {
            $table->id();
            $table->string('itemName');
            $table->string('pricePerUnit');
            $table->boolean('isActive');
            $table->text('description')->nullable(true);
            $table->unsignedBigInteger('unitId')->nullable(true);
            $table->foreign('unitId')->references('id')->on('measure_units')->onDelete('cascade');  
            $table->unsignedBigInteger('branch_id');
            $table->foreign('branch_id')->references('id')->on('branches'); 
            $table->unique(['itemName', 'branch_id']);
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
            $table->string('openingBalance')->nullable(true);
            $table->string('taxPercentage')->nullable(true);
            $table->boolean('isActive')->default(true);
            $table->text('description')->nullable(true);
            $table->unsignedBigInteger('branch_id');
            $table->foreign('branch_id')->references('id')->on('branches'); 
            $table->unique(['ledgerName', 'branch_id']);
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
            $table->string('accountCurrentBalance')->nullable(true);
            $table->string('grandTotal')->nullable(true);
            $table->text('comment')->nullable(true);
            $table->unsignedBigInteger('accountId')->nullable(true);
            $table->foreign('accountId')->references('id')->on('ledger_accounts')->onDelete('cascade');  
            $table->unsignedBigInteger('branch_id');
            $table->foreign('branch_id')->references('id')->on('branches'); 
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
            $table->string('percentage')->nullable(true);
            $table->string('amount');
            $table->string('currentBalance')->nullable(true);
            $table->unsignedBigInteger('transactionId')->nullable(true);
            $table->foreign('transactionId')->references('id')->on('transactions')->onDelete('cascade');  
            $table->unsignedBigInteger('accountId')->nullable(true);
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
