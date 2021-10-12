<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| is assigned the "api" middleware group. Enjoy building your API!
|
*/

// Route::middleware('auth:api')->get('/user', function (Request $request) {
//     return $request->user();
// });

Route::post('auth/signin', 'AuthController@authenticate');


Route::get('master-sync/{branchId}', 'MigrationController@masterSync');
Route::get('master-sync-get-data/{branchId}', 'MigrationController@getMasterInfo');



Route::group(['middleware' => ['jwt.verify']], function() {
    Route::get('current-user', 'AuthController@getAuthenticatedUser');

    Route::get('sync', 'MigrationController@syncStart');
    Route::post('execute', 'MigrationController@syncExecute');
    Route::post('online-execute', 'MigrationController@finalOnlineExecute');


    Route::group(['prefix'=>'order-manager'], function(){

        Route::group(['prefix'=>'user'], function(){
            Route::get('', 'UserController@getUsers');
            Route::get('{id}', 'UserController@getUser');
            Route::post('', 'UserController@updateUser');
            Route::post('change-current-user-image', 'UserController@uploadCurrentUserImage');//->middleware(['role:Super Admin,Admin']);
            Route::post('change-current-user-password', 'UserController@changeCurrentUserPassword');
            Route::post('change-other-user-password', 'UserController@changeOtherUserPassword');
            // Route::put('{id}', 'UserController@updateUser');
            Route::delete('{id}', 'UserController@deleteUser');
        });

        Route::group(['prefix'=>'user-attendance'], function(){
            Route::get('', 'UserAttendanceController@getAttendance');
            Route::post('', 'UserAttendanceController@updateAttendace');
        });
        Route::group(['prefix'=>'category'], function(){
            Route::get('', 'ProductController@getCategories');
            Route::get('{id}', 'ProductController@getCategoryDetail');
            Route::post('', 'ProductController@updateCategory');//->middleware(['role:Super Admin,Admin']);
            Route::delete('{id}', 'ProductController@deleteCategory');//->middleware(['role:Super Admin,Admin']);
            Route::put('status/{id}', 'ProductController@changeCategoryStatus');//->middleware(['role:Super Admin,Admin']);
        });


        Route::group(['prefix'=>'company'], function(){
            Route::get('', 'CompanyController@getAllCompanies');
            Route::get('{id}', 'CompanyController@getCompanyDetails');
            Route::post('', 'CompanyController@updateCompany');//->middleware(['role:Super Admin']);
            Route::delete('{id}', 'CompanyController@deleteCompany');//->middleware(['role:Super Admin']);
            Route::put('status/{id}', 'CompanyController@changeCompanyStatus');//->middleware(['role:Super Admin']);
        });


        Route::group(['prefix'=>'branch'], function(){
            Route::get('', 'BranchController@getBranches');
            Route::get('{id}', 'BranchController@getBranchDetails');
            Route::post('', 'BranchController@updateBranch');//->middleware(['role:Super Admin']);
            Route::put('{id}', 'BranchController@updateBranch');//->middleware(['role:Super Admin']);
            Route::delete('{id}', 'BranchController@deleteBranch');//->middleware(['role:Super Admin']);
            Route::put('status/{id}', 'BranchController@changeBranchStatus');//->middleware(['role:Super Admin']);
        });


        Route::group(['prefix'=>'table-manager'], function(){
            Route::get('', 'OrderController@getTableManger'); //done
            Route::post('table', 'OrderController@updateTable'); //done
            Route::delete('table/{id}', 'OrderController@deleteTable'); //done
            // Route::get('{id}', 'OrderController@getOrderTypeDetails');
            Route::post('', 'OrderController@updateTableManager');//->middleware(['role:Super Admin,Admin']);
            // Route::put('{id}', 'OrderController@updateOrderType');//->middleware(['role:Super Admin,Admin']);
            // Route::delete('{id}', 'OrderController@deleteOrderType');//->middleware(['role:Super Admin,Admin']);
            // Route::put('status/{id}', 'OrderController@changeOrderTypeStatus');//->middleware(['role:Super Admin,Admin']);
            Route::put('reserved/{id}', 'OrderController@changeTableReserved');//->middleware(['role:Super Admin,Admin']);
        });

        Route::group(['prefix'=>'tables'], function(){
            Route::get('', 'OrderController@getOrderTypeWithTableOccupy');
        });

        Route::group(['prefix'=>'kitchen'], function(){
            Route::get('', 'KitchenController@getKitchenData');
            Route::post('', 'KitchenController@updateKitchenStatus');
        });

        Route::group(['prefix'=>'product'], function(){
            Route::get('', 'ProductController@getProducts');
            Route::get('category-based-product', 'ProductController@getCategoryGroupedProduct');
            Route::get('{id}', 'ProductController@getProductDetail');
            Route::post('', 'ProductController@updateProduct');//->middleware(['role:Super Admin,Admin']);
            Route::delete('{id}', 'ProductController@deleteProduct');//->middleware(['role:Super Admin,Admin']);
            Route::put('status/{id}', 'ProductController@changeProductStatus');//->middleware(['role:Super Admin,Admin']);
        });


        Route::group(['prefix'=>'product-combo'], function(){
            Route::get('', 'ProductController@getProductCombos');
            Route::get('{id}', 'ProductController@getProductComboDetail');
            Route::post('', 'ProductController@updateProductCombo');//->middleware(['role:Super Admin,Admin']);
            Route::delete('{id}', 'ProductController@deleteProductCombo');//->middleware(['role:Super Admin,Admin']);
            Route::put('status/{id}', 'ProductController@changeProductComboStatus');//->middleware(['role:Super Admin,Admin']);
        });

        Route::group(['prefix'=>'order'], function(){
            Route::post('change-order-status', 'OrderController@changeStatusBack');
            Route::get('', 'OrderController@getOrderList');
            Route::get('item-sales-report', 'OrderController@orderItemReportBasedOnProduct');
            Route::get('{id}', 'OrderController@getOrderDetails');
            Route::post('', 'OrderController@updateOrder');//->middleware(['role:Super Admin,Admin,Order Manager']);
            Route::post('rejected-item-remove', 'OrderController@removeRejectedItems');//->middleware(['role:Super Admin,Admin,Order Manager']);
            Route::post('kot-print', 'OrderController@kotPrintedItems');//->middleware(['role:Super Admin,Admin,Order Manager']);
            Route::delete('{orderIds}', 'OrderController@deleteBulkOrders');
            // Route::put('status/{id}', 'ProductController@changeProductStatus');
        });
    });


    Route::group(['prefix'=>'account-manager'], function(){
        Route::group(['prefix'=>'ledger'], function(){
            Route::get('', 'AccountMasterController@getLedgers');
            Route::get('{id}', 'AccountMasterController@getLedger');
            Route::post('', 'AccountMasterController@updateLedger');
            Route::delete('{id}', 'AccountMasterController@deleteLedger');
        });

        Route::group(['prefix'=>'unit'], function(){
            Route::get('', 'AccountMasterController@getUnits');
            Route::get('{id}', 'AccountMasterController@getUnit');
            Route::post('', 'AccountMasterController@updateUnit');
            Route::delete('{id}', 'AccountMasterController@deleteUnit');
        });

        Route::group(['prefix'=>'inventory'], function(){
            Route::get('', 'InventoryManagementController@getInventoryItems');
            Route::get('{id}', 'InventoryManagementController@getInventoryItem');
            Route::post('', 'InventoryManagementController@updateInventoryItem');
            // Route::put('{id}', 'InventoryManagementController@updateInventoryItem');
            Route::delete('{id}', 'InventoryManagementController@deleteInventoryItem');
            Route::get('inventory-history/{invenotoryId}', 'InventoryManagementController@getInventoryTrackings');
            Route::post('stock-update', 'InventoryManagementController@updateInventoryStock');
        });

        Route::group(['prefix'=>'transaction'], function(){
            Route::get('', 'AccountTransactionController@getAllTransactions');
            Route::get('consolidated-report', 'AccountTransactionController@getConsolidatedReport');
            Route::get('{id}', 'AccountTransactionController@getTransactionDetails');
            Route::post('', 'AccountTransactionController@updateTransaction');
            Route::get('report-dash/monthly', 'AccountTransactionController@monthlyDashStats');
            Route::get('reports/profit-top-loss', 'AccountTransactionController@getMonthlyProfitAndLoss');
            // Route::put('{id}', 'AccountTransactionController@updateTransaction');
            // Route::delete('{id}', 'AccountTransactionController@deleteInventoryItem');
        });
    });
});
