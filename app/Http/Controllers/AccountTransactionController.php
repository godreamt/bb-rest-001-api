<?php

namespace App\Http\Controllers;

use App\Transaction;
use App\TransactionItem;
use Illuminate\Http\Request;
use App\TransactionOnAccount;

class AccountTransactionController extends Controller
{
    public function newPurchase(Request $request) {
        try {
            return \DB::transaction(function() use($request) {
                $transaction = new Transaction();
                $transaction->transactionDate = new \Datetime($request->transactionDate);
                $transaction->transactionRefNumber = $request->transactionRefNumber;
                $transaction->accountId = $request->accountId;
                $transaction->transactionType = 'purchase';
                $transaction->accountCurrentBalance = $request->accountCurrentBalance;
                $transaction->comment = $request->comment;
                $transaction->grandTotal = $request->grandTotal;
                $transaction->save();


                foreach($request->items as $item) {
                    $transactionItem = new TransactionItem();
                    $transactionItem->transactionId = $transaction->id;
                    $transactionItem->itemId = $item['itemId'];
                    $transactionItem->quantity = $item['quantity'];
                    $transactionItem->amount = $item['amount'];
                    $transactionItem->total = $item['total'];
                    $transactionItem->save();
                }

                foreach($request->accounts as $account) {
                    $transactionAccount = new TransactionOnAccount();
                    $transactionAccount->transactionId = $transaction->id;
                    $transactionAccount->accountId = $account['accountId'];
                    $transactionAccount->percentage = $account['percentage'];
                    $transactionAccount->amount = $account['amount'];
                    $transactionAccount->currentBalance = $account['currentBalance'];
                    $transactionAccount->save();
                }
                return $transaction;
            });
        }catch(\Exception $e) {
            return response()->json(['msg' => ' Can not able to save transaction', 'error'=>$e], 400);
        }
    }

    public function updatePurchase(Request $request, $id) {
        try {
            return \DB::transaction(function() use($request, $id) {
                $transaction = Transaction::find($id);
                $transaction->transactionDate = new \Datetime($request->transactionDate);
                $transaction->transactionRefNumber = $request->transactionRefNumber;
                $transaction->accountId = $request->accountId;
                $transaction->transactionType = 'purchase';
                $transaction->accountCurrentBalance = $request->accountCurrentBalance;
                $transaction->comment = $request->comment;
                $transaction->grandTotal = $request->grandTotal;
                $transaction->save();


                foreach($request->items as $item) {
                    if(!empty($item['id'])) {
                        $transactionItem = TransactionItem::find($item['id']);
                    }else {
                        $transactionItem = new TransactionItem();
                        $transactionItem->transactionId = $transaction->id;
                    }
                    $transactionItem->itemId = $item['itemId'];
                    $transactionItem->quantity = $item['quantity'];
                    $transactionItem->amount = $item['amount'];
                    $transactionItem->total = $item['total'];
                    $transactionItem->save();
                }

                foreach($request->accounts as $account) {
                    if(!empty($account['id'])) {
                        $transactionAccount = TransactionOnAccount::find($item['id']);
                    }else {
                        $transactionAccount = new TransactionOnAccount();
                        $transactionAccount->transactionId = $transaction->id;
                    }
                    $transactionAccount->accountId = $account['accountId'];
                    $transactionAccount->percentage = $account['percentage'];
                    $transactionAccount->amount = $account['amount'];
                    $transactionAccount->currentBalance = $account['currentBalance'];
                    $transactionAccount->save();
                }
                return $transaction;
            });
        }catch(\Exception $e) {
            return response()->json(['msg' => ' Can not able to save transaction', 'error'=>$e], 400);
        }
    }

    // public function 
}
