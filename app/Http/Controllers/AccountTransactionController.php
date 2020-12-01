<?php

namespace App\Http\Controllers;

use App\MonthSheet;
use App\Transaction;
use App\YearlySheet;
use App\TransactionItem;
use Illuminate\Http\Request;
use App\TransactionOnAccount;
use Illuminate\Pagination\Paginator;

class AccountTransactionController extends Controller
{
    public function updateTransaction(Request $request) {
        try {
            return \DB::transaction(function() use($request) {
                $date = new \Datetime();
                $before30days = $date->modify('-31 days');
                $transactionDate = new \Datetime($request->transactionDate);
                if(empty($request->id)) {
                    $transaction = new Transaction();
                    $transaction->transactionType = $request->transactionType;
                    $transaction->company_id = $request->company_id;
                    $transaction->branch_id = $request->branch_id;
                }else {
                    $transaction = Transaction::find($request->id);
                    if($transaction->transactionDate < $before30days) {
                        return response()->json(['Can not update now'], 400);
                    }

                    // handle previous records month sheet and yearly sheet deductions
                    $previousTransactionDate = new \Datetime($transaction->transactionDate);
                    $month = $transactionDate->format('m');
                    $year = $transactionDate->format('Y');
                    $res = $this->getMonthSheet($month, $year, $transaction->company_id, $transaction->branch_id);
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
                $res = $this->getMonthSheet($month, $year, $transaction->company_id, $transaction->branch_id);
                $monthlySheet = $res['monthly'];
                $yearlySheet = $res['yearly'];



                // $transaction->transactionRefNumber = $request->transactionRefNumber;
                $transaction->accountId = $request->accountId;
                $transaction->description = $request->description;
                $transaction->grandTotal = $request->grandTotal;
                $transaction->monthly_sheet_id = $monthlySheet->id;
                $transaction->branch_id = $request->branch_id;
                $transaction->company_id = $request->company_id;
                $transaction->save();

                
                // handle present month sheet and yearly sheet managment
                if($transaction->transactionType == 'purchase' || $transaction->transactionType == 'payment') {
                    $monthlySheet->totalMonthlyExpense = (float)$monthlySheet->totalMonthlyExpense - (float)$transaction->grandTotal;
                }else {//if type = sales, receipt
                    $monthlySheet->totalMonthlyIncome = (float)$monthlySheet->totalMonthlyIncome - (float)$transaction->grandTotal;
                }
                $monthlySheet->save();
                // ! Todo : Handle all month carrid down aaounts


                if($request->transactionType == 'purchase' || $request->transactionType == 'sales') {
                    foreach($request->items as $item) {
                        if(!empty($item['deletedFlag']) && $item['deletedFlag'] == 'true') {
                            $transactionItem = TransactionItem::find($item['id']);
                            $transactionItem->delete();
                        }else {
                            if(empty($item['id'])){
                                $transactionItem = new TransactionItem();
                            }else {
                                $transactionItem = TransactionItem::find($item['id']);
                            }
                            $transactionItem->transactionId = $transaction->id;
                            $transactionItem->itemId = $item['itemId'];
                            $transactionItem->quantity = $item['quantity'];
                            $transactionItem->amount = $item['amount'];
                            $transactionItem->total = $item['total'];
                            $transactionItem->save();
                        }
                    }
                }

                foreach($request->accounts as $account) {
                    if(!empty($account['deletedFlag']) && $account['deletedFlag'] == 'true') {
                        $transactionAccount = TransactionOnAccount::find($account['id']);
                        $transactionAccount->delete();
                    }else {
                        if(empty($account['id'])){
                            $transactionAccount = new TransactionOnAccount();
                        }else {
                            $transactionAccount = TransactionOnAccount::find($account['id']);
                        }
                        $transactionAccount->transactionId = $transaction->id;
                        $transactionAccount->accountId = $account['accountId'];
                        $transactionAccount->amountProcessType = $account['amountProcessType'];
                        $transactionAccount->amountValue = $account['amountValue'];
                        $transactionAccount->totalAmount = $account['totalAmount'];
                        $transactionAccount->save();
                    }
                }
                return $transaction;
            });
        }catch(\Exception $e) {
            return response()->json(['msg' => ' Can not able to save transaction', 'error'=>$e->getMessage()], 400);
        }
    }

    public function getMonthSheet($month, $year, $company_id, $branch_id=null) {
        $yearlySheet = $this->getYearlySheet($month, $year, $company_id, $branch_id);
        $monthSheet = MonthSheet::where('month', $month)
                                ->where('year', $year)
                                ->where('company_id', $company_id);
        
        
        if($branch_id != null  && !empty($branch_id)) {
            $monthSheet = $monthSheet->where('branch_id', $branch_id);
        }
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
        $monthSheet->company_id = $company_id;
        $monthSheet->branch_id = $branch_id;
        $monthSheet->yearly_sheet_id = $yearlySheet->id;
        $monthSheet->save();
        return [
            'monthly' => $monthSheet,
            'yearly' => $yearlySheet
        ];
    }

    public function getYearlySheet($month, $year, $company_id, $branch_id) {
        $searchingDate = new \Datetime($year.'-'.$month."-01");
        $yearlySheet = YearlySheet::where('company_id', $company_id)
                                ->where(function($query) use ($searchingDate) {
                                    $query->where('fromDate', '<=', $searchingDate)
                                        ->where('toDate', '>=', $searchingDate);
                                });
        if($branch_id != null  && !empty($branch_id)) {
            $yearlySheet = $yearlySheet->where('branch_id', $branch_id);
        }
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
        $yearlySheet->company_id = $company_id;
        $yearlySheet->branch_id = $branch_id;
        $yearlySheet->save();
        return $yearlySheet;
    }



    // public function updatePurchase(Request $request, $id) {
    //     try {
    //         return \DB::transaction(function() use($request, $id) {
    //             $transaction = Transaction::find($id);
    //             $transaction->transactionDate = new \Datetime($request->transactionDate);
    //             $transaction->transactionRefNumber = $request->transactionRefNumber;
    //             $transaction->accountId = $request->accountId;
    //             $transaction->transactionType = 'purchase';
    //             $transaction->accountCurrentBalance = $request->accountCurrentBalance;
    //             $transaction->comment = $request->comment;
    //             $transaction->grandTotal = $request->grandTotal;
    //             $transaction->save();


    //             foreach($request->items as $item) {
    //                 if(!empty($item['id'])) {
    //                     $transactionItem = TransactionItem::find($item['id']);
    //                 }else {
    //                     $transactionItem = new TransactionItem();
    //                     $transactionItem->transactionId = $transaction->id;
    //                 }
    //                 $transactionItem->itemId = $item['itemId'];
    //                 $transactionItem->quantity = $item['quantity'];
    //                 $transactionItem->amount = $item['amount'];
    //                 $transactionItem->total = $item['total'];
    //                 $transactionItem->save();
    //             }

    //             foreach($request->accounts as $account) {
    //                 if(!empty($account['id'])) {
    //                     $transactionAccount = TransactionOnAccount::find($item['id']);
    //                 }else {
    //                     $transactionAccount = new TransactionOnAccount();
    //                     $transactionAccount->transactionId = $transaction->id;
    //                 }
    //                 $transactionAccount->accountId = $account['accountId'];
    //                 $transactionAccount->percentage = $account['percentage'];
    //                 $transactionAccount->amount = $account['amount'];
    //                 $transactionAccount->currentBalance = $account['currentBalance'];
    //                 $transactionAccount->save();
    //             }
    //             return $transaction;
    //         });
    //     }catch(\Exception $e) {
    //         return response()->json(['msg' => ' Can not able to save transaction', 'error'=>$e], 400);
    //     }
    // }

    public function getAllTransactions(Request $request) {
        $fields = $request->get('fields', '*');
        if($fields != '*'){
            $fields = explode(',',$fields);
        }
        $transactions = Transaction::select($fields)->with('branch')->with('company')->with('ledgerAccount');

        if(!empty($request->searchString)) {
            $transactions = $transactions->where(function($query) use ($request) {
                $query->where('transactionRefNumber', 'LIKE', '%'.$request->searchString.'%')
                    ->orWhere('description', 'LIKE', '%'.$request->searchString.'%');
            });
        }


        if(!empty($request->startDate) && !empty($request->endDate)) {
            $endDate = (new \Datetime($request->endDate))->modify('+1 day');
            $transactions = $transactions->whereBetween('transactionDate', [new \Datetime($request->startDate), $endDate]);
        }

        if(!empty($request->transactionType)) {
            $transactions = $transactions->where('transactionType', $request->transactionType);
        }

        if(!empty($request->companyId)) {
            $transactions = $transactions->where('company_id', $request->companyId);
        }

        if(!empty($request->branchId)) {
            $transactions = $transactions->where('branch_id', $request->branchId);
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

            return $transactions->paginate(10);
        }else {
            return $transactions->get();
        }
    }

    public function getTransactionDetails(Request $request, $id) {
        $transaction = Transaction::with('ledgerAccount')
                                ->with('company')
                                ->with('branch')
                                ->with('items')
                                ->with('items.item')
                                ->with('items.item.company')
                                ->with('items.item.unit')
                                ->with('accounts')
                                ->with('accounts.account')
                                ->where('id', $id)
                                ->first();
        return $transaction;
    }
}
