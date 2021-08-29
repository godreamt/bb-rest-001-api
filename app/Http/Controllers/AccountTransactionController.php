<?php

namespace App\Http\Controllers;

use App\User;
use App\Order;
use App\MonthSheet;
use App\Transaction;
use App\YearlySheet;
use App\InventoryItem;
use App\LedgerAccount;
use App\TransactionItem;
use Illuminate\Http\Request;
use App\InventoryItemManager;
use App\TransactionOnAccount;
use App\TransactionAccountJournal;
use Illuminate\Pagination\Paginator;
use App\Http\Requests\TransactionUpdateValidationRequest;

class AccountTransactionController extends Controller
{
    public function updateTransaction(TransactionUpdateValidationRequest $request) {
        try {
            return \DB::transaction(function() use($request) {
                $date = new \Datetime();
                $before3days = $date->modify('-3 days');
                $transactionDate = new \Datetime($request->transactionDate);
                $branchId = $request->branch_id;
                $loggedUser = \Auth::user();
                if($loggedUser instanceof User) {
                    if($loggedUser->roles != 'Super Admin' && $loggedUser->roles != 'Company Admin') {
                        $branchId = $loggedUser->branch_id;
                    }
                }

                $ledgerAccount = LedgerAccount::find($request->accountId);
                if(empty($request->id)) {
                    $transaction = new Transaction();
                    $mainTransactionJournal = new TransactionAccountJournal();
                    $transaction->transactionType = strtolower($request->transactionType);
                    $transaction->branch_id = $branchId;
                    $mainTransactionJournal->branch_id = $branchId;
                }else {
                    $transaction = Transaction::find($request->id);
                    $mainTransactionJournal = TransactionAccountJournal::where('transactionId', $transaction->id) ->where('transactionAccountId', null)->first();

                    
                    if($mainTransactionJournal->accountId != $ledgerAccount->id) {
                        $this->handleEndingBalanceOdAccount($mainTransactionJournal, 0);
                    }


                    if($transaction->transactionDate < $before3days) {
                        return response()->json(['Can not update now, transaction update can be done for last 3 days'], 400);
                    }

                    // handle previous records month sheet and yearly sheet deductions
                    $previousTransactionDate = new \Datetime($transaction->transactionDate);
                    $month = $transactionDate->format('m');
                    $year = $transactionDate->format('Y');
                    $res = $this->getMonthSheet($month, $year, $transaction->branch_id);
                    $previousMonthlySheet = $res['monthly'];
                    $previousYearlySheet = $res['yearly'];
                    if($transaction->transactionType == 'purchase' || $transaction->transactionType == 'payment') {
                        $previousMonthlySheet->totalMonthlyExpense = (float)$previousMonthlySheet->totalMonthlyExpense - (float)$transaction->grandTotal;
                    }else {//if type = sales, receipt
                        $previousMonthlySheet->totalMonthlyIncome = (float)$previousMonthlySheet->totalMonthlyIncome - (float)$transaction->grandTotal;
                    }
                    $previousMonthlySheet->save();
                }
                $transaction->transactionDate = $transactionDate;
                $month = $transactionDate->format('m');
                $year = $transactionDate->format('Y');
                $res = $this->getMonthSheet($month, $year, $transaction->branch_id);
                $monthlySheet = $res['monthly'];
                $yearlySheet = $res['yearly'];
                $transaction->accountId = $ledgerAccount->id;
                $transaction->description = $request->description;
                $transaction->grandTotal = $request->grandTotal;
                $transaction->monthly_sheet_id = $monthlySheet->id;
                $transaction->isSync = false;
                $transaction->save();



                $orderItemTotal = 0;

                if($request->transactionType == 'purchase' || $request->transactionType == 'sales') {
                    foreach($request->items as $item) {
                        $inventoryItem = InventoryItem::find($item['itemId']);
                        if(!empty($item['deletedFlag']) && $item['deletedFlag'] == 'true') {
                            $transactionItem = TransactionItem::find($item['id']);

                            $oldQuantity = $transactionItem->quantity;
                            if($request->transactionType == 'purchase') {
                                $inventoryItem->availableStock = $inventoryItem->availableStock - $oldQuantity;
                            }else if($request->transactionType == 'sales') {
                                $inventoryItem->availableStock = $inventoryItem->availableStock + $oldQuantity;
                            }

                            $inventoryItem->isSync = false;
                            $inventoryItem->save();
                            $transactionItem->delete();
                        }else {
                            if(empty($item['id'])){
                                $transactionItem = new TransactionItem();
                            }else {
                                $transactionItem = TransactionItem::find($item['id']);
                            }

                            
                            $newlyNeededQuantity = $item['quantity'] - $transactionItem->quantity;
                            
                            if($request->transactionType == 'purchase') {
                                if($item['amount'] > 0) {
                                    $inventoryItem->lastPurchasedPrice = $item['amount'];
                                }
                                $inventoryItem->availableStock = $inventoryItem->availableStock + $newlyNeededQuantity;
                            }else if($request->transactionType == 'sales') {
                                $inventoryItem->availableStock = $inventoryItem->availableStock - $newlyNeededQuantity;
                            }

                            $transactionItem->transactionId = $transaction->id;
                            $transactionItem->itemId = $item['itemId'];
                            $transactionItem->quantity = $item['quantity'];
                            $transactionItem->amount = $item['amount'];
                            $transactionItem->total = $transactionItem->quantity * $transactionItem->amount;
                            $orderItemTotal = $transactionItem->total + $orderItemTotal;
                            $transactionItem->isSync = false;
                            $transactionItem->save();

                            $inventoryItem->isSync = false;
                            $inventoryItem->save();
                        }
                    }
                }

                $accountsTotal = 0;
                foreach($request->accounts as $account) {
                    if(!empty($account['deletedFlag']) && $account['deletedFlag'] == 'true') {
                        $transactionAccount = TransactionOnAccount::find($account['id']);
                        $transactionAccount->delete();
                    }else {
                        if(empty($account['id'])){
                            $transactionAccount = new TransactionOnAccount();
                            $transactionJournal = new TransactionAccountJournal();
                            $transactionJournal->branch_id = $branchId;
                        }else {
                            $transactionAccount = TransactionOnAccount::find($account['id']);
                            $transactionJournal = TransactionAccountJournal::where('transactionAccountId', $transactionAccount->id)->first();

                            if($transactionAccount->accountId != $account['accountId']) {
                                $this->handleEndingBalanceOdAccount($transactionJournal, 0, 'inverse');
                            }
                        }


                        $transactionAccount->transactionId = $transaction->id;
                        $transactionAccount->accountId = $account['accountId'];
                        $transactionAccount->amountProcessType = $account['amountProcessType'];
                        $transactionAccount->amountValue = $account['amountValue'];
                        if($transactionAccount->amountProcessType == 'percent') {
                            $transactionAccount->totalAmount = $orderItemTotal * $transactionAccount->amountValue / 100;
                        }else {
                            $transactionAccount->totalAmount = $transactionAccount->amountValue;
                        }
                        $accountsTotal = $accountsTotal + $transactionAccount->totalAmount;
                        $transactionAccount->isSync = false;
                        $transactionAccount->save();
                        
                        // ! Todo adjust the balance while changing previous account
                        if($transaction->transactionType == 'sales') {
                            $description = "Sales has done to ".$ledgerAccount->ledgerName;
                        }else if($transaction->transactionType == 'purchase') {
                            $description = "Purchase has done from ".$ledgerAccount->ledgerName;
                        }else if($transaction->transactionType == 'payment') {
                            $description = "Payment has done from ".$ledgerAccount->ledgerName;
                        }else if($transaction->transactionType == 'receipt') {
                            $description = "Recept amount has added to ".$ledgerAccount->ledgerName;
                        }
                        $transactionJournal->description = $description;
                        $transactionJournal->transactionDate = $transaction->transactionDate;
                        $transactionJournal->transactionAccountId = $transactionAccount->id;
                        $transactionJournal->accountId = $account['accountId'];
                        $transactionJournal->transactionAmount = $transactionAccount->totalAmount;
                        $transactionJournal->transactionId = $transaction->id;
                        $transactionJournal->save();
                        $this->handleEndingBalanceOdAccount($transactionJournal, $transactionAccount->totalAmount);
                    }
                }
                $transaction->grandTotal = $orderItemTotal + $accountsTotal;
                
                // handle present month sheet and yearly sheet managment
                if($transaction->transactionType == 'purchase' || $transaction->transactionType == 'payment') {
                    $monthlySheet->totalMonthlyExpense = (float)$monthlySheet->totalMonthlyExpense - (float)$transaction->grandTotal;
                }else {//if type = sales, receipt
                    $monthlySheet->totalMonthlyIncome = (float)$monthlySheet->totalMonthlyIncome - (float)$transaction->grandTotal;
                }
                $monthlySheet->isSync = false;
                $monthlySheet->save();
                $transaction->save();



                if($transaction->transactionType == 'sales') {
                    $description = "Sales has done ";
                }else if($transaction->transactionType == 'purchase') {
                    $description = "Purchase has done";
                }else if($transaction->transactionType == 'payment') {
                    $description = "Payment has made";
                }else if($transaction->transactionType == 'receipt') {
                    $description = "Recept amount has received";
                }
                $mainTransactionJournal->description = $description;
                $mainTransactionJournal->transactionDate = $transaction->transactionDate;
                $mainTransactionJournal->accountId = $ledgerAccount->id;
                $mainTransactionJournal->transactionAmount = $transaction->grandTotal;
                $mainTransactionJournal->transactionId = $transaction->id;
                $mainTransactionJournal->save();
                $this->handleEndingBalanceOdAccount($mainTransactionJournal, $transaction->grandTotal);



                // ! Todo : Handle all month carrid down aaounts
                return $transaction;
            });
        }catch(\Exception $e) {
            return response()->json(['msg' => ' Can not able to save transaction', 'error'=>$e->getMessage()], 400);
        }
    }

    public function handleEndingBalanceOdAccount(TransactionAccountJournal $journal, $totalCurretTransAmmount, $transferType = "straight") {
        $previousTransaction = TransactionAccountJournal::where('accountId', $journal->accountId)
                                            ->where('transactionDate', '<=' ,$journal->transactionDate)
                                            ->where('id', '<>', $journal->id)
                                            ->orderBy('transactionDate', 'DESC')
                                            ->orderBy('id', 'DESC')
                                            ->first();
        // \Debugger::dump($previousTransaction);
        // throw new \Exception("Resdd");
        $lastEndingBalance=0;
        if($previousTransaction instanceof TransactionAccountJournal) {
            $lastEndingBalance = $previousTransaction->endingBalance;
        }
        
        $transaction = Transaction::find($journal->transactionId);
        if($transaction->transactionType == 'sales') {
            $journal->endingBalance = $lastEndingBalance + $totalCurretTransAmmount;
        } if($transaction->transactionType == 'receipt') {
            if($transaction->accountId != $journal->accountId) {
                $journal->endingBalance = $lastEndingBalance - $totalCurretTransAmmount;
            }else {
                $journal->endingBalance = $lastEndingBalance + $totalCurretTransAmmount;
            }
        }else if($transaction->transactionType == 'payment') {
            if($transaction->accountId != $journal->accountId) {
                $journal->endingBalance = $lastEndingBalance + $totalCurretTransAmmount;
            }else {
                $journal->endingBalance = $lastEndingBalance - $totalCurretTransAmmount;
            }
        } if($transaction->transactionType == 'purchase') {
            $journal->endingBalance = $lastEndingBalance - $totalCurretTransAmmount;
        }
        $lastEndingBalance = $journal->endingBalance;
        $journal->save();
        $nextEntries = TransactionAccountJournal::where('accountId', $journal->accountId)
                                        ->where('transactionDate', '>' ,$journal->transactionDate)
                                        ->where('id', '<>' ,$journal->id)
                                        ->orderBy('transactionDate', 'ASC')
                                        ->orderBy('id', 'ASC')
                                        ->get();

        foreach($nextEntries as $journal) {
            $transaction = Transaction::find($journal->transactionId);
            if($transaction->transactionType == 'sales') {
                $journal->endingBalance = $lastEndingBalance + $journal->transactionAmount;
            } if($transaction->transactionType == 'receipt') {
                if($transaction->accountId != $journal->accountId) {
                    $journal->endingBalance = $lastEndingBalance - $totalCurretTransAmmount;
                }else {
                    $journal->endingBalance = $lastEndingBalance + $totalCurretTransAmmount;
                }
            }else if($transaction->transactionType == 'payment') {
                if($transaction->accountId != $journal->accountId) {
                    $journal->endingBalance = $lastEndingBalance + $totalCurretTransAmmount;
                }else {
                    $journal->endingBalance = $lastEndingBalance - $totalCurretTransAmmount;
                }
            } if ($transaction->transactionType == 'purchase') {
                $journal->endingBalance = $lastEndingBalance - $journal->transactionAmount;
            }
            $lastEndingBalance = $journal->endingBalance;
            $journal->save();
        }
    }
    

    public function getMonthSheet($month, $year, $branch_id) {
        $yearlySheet = $this->getYearlySheet($month, $year, $branch_id);
        $monthSheet = MonthSheet::where('month', $month)
                                ->where('year', $year)
                                ->where('branch_id', $branch_id);
        $monthSheet = $monthSheet->first();
        if($monthSheet instanceof MonthSheet) {
            return [
                'monthly' => $monthSheet,
                'yearly' => $yearlySheet
            ];
        }
        $monthSheet = new MonthSheet();
        $monthSheet->month = $month;
        $monthSheet->year = $year;
        $monthSheet->branch_id = $branch_id;
        $monthSheet->yearly_sheet_id = $yearlySheet->id;
        $monthSheet->isSync = false;
        $monthSheet->save();
        return [
            'monthly' => $monthSheet,
            'yearly' => $yearlySheet
        ];
    }

    public function getYearlySheet($month, $year, $branch_id) {
        $searchingDate = new \Datetime($year.'-'.$month."-01");
        $yearlySheet = YearlySheet::where('branch_id', $branch_id)
                                ->where(function($query) use ($searchingDate) {
                                    $query->where('fromDate', '<=', $searchingDate)
                                        ->where('toDate', '>=', $searchingDate);
                                });
        $yearlySheet = $yearlySheet->first();
        if($yearlySheet instanceof YearlySheet) {
            return $yearlySheet;
        }

        $yearlySheet = new YearlySheet();
        $startYear = $year;
        $endYear = $year + 1;
        if($month <= 3) {
            $startYear = $year - 1;
            $endYear = $year;
        }
        $yearlySheet->fromDate = new \Datetime(('01-04-'.$startYear));
        $yearlySheet->toDate = new \Datetime(('01-03-'.$endYear));
        $yearlySheet->branch_id = $branch_id;
        $yearlySheet->isSync = false;
        $yearlySheet->save();
        return $yearlySheet;
    }




    public function getAllTransactions(Request $request) {
        $fields = $request->get('fields', '*');
        if($fields != '*'){
            $fields = explode(',',$fields);
        }
        $transactions = Transaction::select($fields)->with('branch')->with('ledgerAccount')
                                    ->leftJoin('ledger_accounts as main_account', 'main_account.id', 'transactions.accountId');

        if(!empty($request->searchString)) {
            $transactions = $transactions->where(function($query) use ($request) {
                $query->where('transactionRefNumber', 'LIKE', '%'.$request->searchString.'%')
                    ->orWhere('transactions.description', 'LIKE', '%'.$request->searchString.'%');
            });
        }


        if(!empty($request->startDate) && !empty($request->endDate)) {
            $endDate = (new \Datetime($request->endDate))->modify('+1 day');
            $transactions = $transactions->whereBetween('transactionDate', [new \Datetime($request->startDate), $endDate]);
        }

        if(!empty($request->transactionType)) {
            $transactions = $transactions->where('transactionType', $request->transactionType);
        }

        if(!empty($request->accountType)) {
            $transactions = $transactions->where('main_account.accountType', $request->accountType);
        }

        if(!empty($request->branchId)) {
            $transactions = $transactions->where('transactions.branch_id', $request->branchId);
        }

        if(!empty($request->orderCol) && !empty($request->orderType)) {
            $transactions = $transactions->orderBy($request->orderCol, $request->orderType);
        }else {
            $transactions = $transactions->orderBy('created_at', 'DESC');
        }

        $currentPage = $request->pageNumber;
        if(!empty($currentPage)){
            Paginator::currentPageResolver(function () use ($currentPage) {
                return $currentPage;
            });

            return $transactions->paginate($request->get('perPage', 10));
        }else {
            return $transactions->get();
        }
    }

    public function getTransactionDetails(Request $request, $id) {
        $transaction = Transaction::with('ledgerAccount')
                                ->with('branch')
                                ->with('items')
                                ->with('items.item')
                                ->with('items.item.branch')
                                ->with('items.item.unit')
                                ->with('accounts')
                                ->with('accounts.account')
                                ->where('id', $id)
                                ->first();
        return $transaction;
    }

    public function getConsolidatedReport(Request $request) {
        $transactions = [];
        $orders = [];
        if(!empty($request->startDate) && !empty($request->endDate)) {
            $transactions  = Transaction::with('ledgerAccount');
            if(!empty($request->requiredTypes)) {
                $types = explode(',', $request->requiredTypes);
                $transactions = $transactions->whereIn('transactionType', $types);
            }

            $startDate = new \Datetime($request->startDate);
            $endDate = (new \Datetime($request->endDate))->modify('1 day');
            $transactions = $transactions->whereBetween('transactionDate', [$startDate, $endDate]);

            $transactions = $transactions->get();
            if(empty(!$request->includeOrders) && $request->includeOrders) {
                $orders = Order::with('orderType')->where('orderStatus', 'completed');
                $orders = $orders->whereBetween('created_at', [$startDate, $endDate]);
                $orders = $orders->get();
            }
                
        }
        return [
            'transactions' => $transactions,
            'orders' => $orders
        ];
    }

    public function monthlyDashStats(Request $request){
        $year = $request->get('year', (new \Datetime())->format('Y'));
        $monthlySheets = MonthSheet::where('year', $year)
                                ->get();
        foreach($monthlySheets as $sheet) {
            $sheet['stats'] = Transaction::where('monthly_sheet_id', $sheet->id)
                                                ->groupBy('transactionType')
                                                ->selectRaw('sum(transactions.grandTotal) as amount')
                                                ->addSelect('transactionType')
                                                ->get();
        }
        return $monthlySheets;
    }
}
