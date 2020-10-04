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
Route::get('kitchen/{branch}', 'KitchenController@getKitchenData');


Route::group(['middleware' => ['jwt.verify']], function() {
    Route::get('current-user', 'AuthController@getAuthenticatedUser');

    
    Route::group(['prefix'=>'order-manager'], function(){
        
        Route::group(['prefix'=>'user'], function(){
            Route::get('', 'UserController@getUsers');
            Route::get('{id}', 'UserController@getUser');
            Route::post('', 'UserController@createUser');
            Route::post('change-current-user-image', 'UserController@uploadCurrentUserImage')->middleware(['role:Super Admin,Admin']);
            Route::post('change-current-user-password', 'UserController@changeCurrentUserPassword');
            Route::put('{id}', 'UserController@updateUser');
            Route::delete('{id}', 'UserController@deleteUser');
        });

        Route::group(['prefix'=>'category'], function(){
            Route::get('', 'ProductController@getCategories');
            Route::get('{id}', 'ProductController@getCategoryDetail');
            Route::post('', 'ProductController@createCategory')->middleware(['role:Super Admin,Admin']);
            Route::put('{id}', 'ProductController@updateCategory')->middleware(['role:Super Admin,Admin']);
            Route::delete('{id}', 'ProductController@deleteCategory')->middleware(['role:Super Admin,Admin']);
            Route::put('status/{id}', 'ProductController@changeCategoryStatus')->middleware(['role:Super Admin,Admin']);
        });

        
        Route::group(['prefix'=>'branch'], function(){
            Route::get('', 'BranchController@getBranches');
            Route::get('{id}', 'BranchController@getBranchDetails');
            Route::post('', 'BranchController@updateBranch')->middleware(['role:Super Admin']);
            Route::put('{id}', 'BranchController@updateBranch')->middleware(['role:Super Admin']);
            Route::delete('{id}', 'BranchController@deleteBranch')->middleware(['role:Super Admin']);
            Route::put('status/{id}', 'BranchController@changeBranchStatus')->middleware(['role:Super Admin']);
        });

        
        Route::group(['prefix'=>'table-manager'], function(){
            Route::get('', 'OrderController@getTableManger'); //done
            // Route::get('{id}', 'OrderController@getOrderTypeDetails');
            Route::post('', 'OrderController@updateTableManager')->middleware(['role:Super Admin,Admin']);
            // Route::put('{id}', 'OrderController@updateOrderType')->middleware(['role:Super Admin,Admin']);
            // Route::delete('{id}', 'OrderController@deleteOrderType')->middleware(['role:Super Admin,Admin']);
            // Route::put('status/{id}', 'OrderController@changeOrderTypeStatus')->middleware(['role:Super Admin,Admin']);
            Route::put('reserved/{id}', 'OrderController@changeTableReserved')->middleware(['role:Super Admin,Admin']);
        });

        Route::group(['prefix'=>'tables'], function(){
            Route::get('', 'OrderController@getOrderTypeWithTableOccupy');
        });
        
        Route::group(['prefix'=>'product'], function(){
            Route::get('', 'ProductController@getProducts');
            Route::get('{id}', 'ProductController@getProductDetail');
            Route::post('', 'ProductController@updateProduct')->middleware(['role:Super Admin,Admin']);
            Route::delete('{id}', 'ProductController@deleteProduct')->middleware(['role:Super Admin,Admin']);
            Route::put('status/{id}', 'ProductController@changeProductStatus')->middleware(['role:Super Admin,Admin']);
        });

        Route::group(['prefix'=>'order'], function(){
            Route::get('', 'OrderController@getOrderList');
            Route::get('{id}', 'OrderController@getOrderDetails');
            Route::post('', 'OrderController@updateOrder')->middleware(['role:Super Admin,Admin,Order Manager']);
            // Route::delete('{id}', 'ProductController@deleteProduct');
            // Route::put('status/{id}', 'ProductController@changeProductStatus');
        });
    });

    
    Route::group(['prefix'=>'account-manager'], function(){
        Route::group(['prefix'=>'ledger'], function(){
            Route::get('', 'AccountMasterController@getLedgers');
            Route::get('{id}', 'AccountMasterController@getLedger');
            Route::post('', 'AccountMasterController@createLedger');
            Route::put('{id}', 'AccountMasterController@updateLedger');
            Route::delete('{id}', 'AccountMasterController@deleteLedger');
        });
        
        Route::group(['prefix'=>'unit'], function(){
            Route::get('', 'AccountMasterController@getUnits');
            Route::get('{id}', 'AccountMasterController@getUnit');
            Route::post('', 'AccountMasterController@createUnit');
            Route::put('{id}', 'AccountMasterController@updateUnit');
            Route::delete('{id}', 'AccountMasterController@deleteUnit');
        });
        
        Route::group(['prefix'=>'inventory'], function(){
            Route::get('', 'AccountMasterController@getInventoryItems');
            Route::get('{id}', 'AccountMasterController@getInventoryItem');
            Route::post('', 'AccountMasterController@createInventoryItem');
            Route::put('{id}', 'AccountMasterController@updateInventoryItem');
            Route::delete('{id}', 'AccountMasterController@deleteInventoryItem');
        });
        
        Route::group(['prefix'=>'purchase'], function(){
            Route::get('', 'AccountTransactionController@getInventoryItems');
            Route::get('{id}', 'AccountTransactionController@getInventoryItem');
            Route::post('', 'AccountTransactionController@newPurchase');
            Route::put('{id}', 'AccountTransactionController@updatePurchase');
            Route::delete('{id}', 'AccountTransactionController@deleteInventoryItem');
        });
    });
});
