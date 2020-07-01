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

Route::group(['middleware' => ['jwt.verify']], function() {
    Route::get('current-user', 'AuthController@getAuthenticatedUser');

    
    Route::group(['prefix'=>'order-manager'], function(){

        Route::group(['prefix'=>'category'], function(){
            Route::get('', 'ProductController@getCategories');
            Route::get('{id}', 'ProductController@getCategoryDetail');
            Route::post('', 'ProductController@createCategory');
            Route::put('{id}', 'ProductController@updateCategory');
            Route::delete('{id}', 'ProductController@deleteCategory');
            Route::put('status/{id}', 'ProductController@changeCategoryStatus');
        });

        
        Route::group(['prefix'=>'branch'], function(){
            Route::get('', 'BranchController@getBranches');
            Route::get('{id}', 'BranchController@getBranchDetails');
            Route::post('', 'BranchController@createBranch');
            Route::put('{id}', 'BranchController@updateBranch');
            Route::delete('{id}', 'BranchController@deleteBranch');
            Route::put('status/{id}', 'BranchController@changeBranchStatus');
        });

        
        Route::group(['prefix'=>'order-type'], function(){
            Route::get('', 'OrderController@getOrderTypes');
            Route::get('{id}', 'OrderController@getOrderTypeDetails');
            Route::post('', 'OrderController@createOrderType');
            Route::put('{id}', 'OrderController@updateOrderType');
            Route::delete('{id}', 'OrderController@deleteOrderType');
            Route::put('status/{id}', 'OrderController@changeOrderTypeStatus');
        });

        
        Route::group(['prefix'=>'product'], function(){
            Route::get('', 'ProductController@getProducts');
            Route::get('{id}', 'ProductController@getProductDetail');
            Route::post('', 'ProductController@createProduct');
            Route::put('{id}', 'ProductController@updateProduct');
            Route::delete('{id}', 'ProductController@deleteProduct');
            Route::put('status/{id}', 'ProductController@changeProductStatus');
        });
    });
});
