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

Route::middleware('auth:api')->get('/user', function (Request $request) {
    return $request->user();
});

Route::any('/', function () {
    return response()->json(['data' => ['status' => 'success']]);
});

Route::prefix('user')->group(function () {
    Route::post('/register', 'API\UserController@register')->name('user.register');
    Route::post('/login', 'API\UserController@login')->name('user.login');
    Route::post('/logout', 'API\UserController@logout')->name('user.logout')->middleware('auth:sanctum');
    Route::post('/update', 'API\UserController@update')->name('user.update')->middleware('auth:sanctum');
    Route::post('/delete', 'API\UserController@delete')->name('user.delete')->middleware('auth:sanctum');

    Route::prefix('reset')->group(function () {
        Route::post('/send-code', 'API\ResetPasswordController@send_code')->name('user.reset.send_code');
        Route::post('/validate-code', 'API\ResetPasswordController@validate_code')->name('user.reset.validate_code');
        Route::post('/password', 'API\ResetPasswordController@new_password')->name('user.reset.new_password');
    });
});

Route::prefix('image')->group(function () {
    Route::get('/{type}/{name}', 'API\ImageController@show')->name('image.show');
});

Route::middleware('auth:sanctum')->group(function () { # Authenticate Users

    Route::get('/home', 'API\HomeController@show')->name('home.show');

    Route::prefix('store')->group(function () {
        Route::get('/{store_id}', 'API\StoreController@show')->name('store.show');
    });
    
    Route::prefix('grocery')->group(function () {
        Route::get('{store_type_id}', 'API\GroceryController@categories')->name('grocery.categories');
        Route::get('products/{store_type_id}', 'API\GroceryController@products')->name('grocery.products');
    });
    
    Route::prefix('product/{product}')->group(function () {
        Route::get('/', 'API\ProductController@show')->name('product.show');
    
        Route::post('/review/create', 'API\ReviewController@create')->name('review.create');
        Route::post('/review/delete', 'API\ReviewController@delete')->name('review.delete');
        
        Route::get('/reviews', 'API\ReviewController@index')->name('review.index');
        Route::get('/review', 'API\ReviewController@show')->name('review.show');
    
        Route::post('/favourite', 'API\FavouriteController@update')->name('favourite.update');
        Route::post('/monitor', 'API\MonitoredController@update')->name('monitor.update');
    });
    
    Route::get('/favourites', 'API\FavouriteController@index')->name('favourite.index');
    
    Route::prefix('list')->group(function () {
        Route::get('/', 'API\ListViewController@index')->name('list.index');
        Route::post('/create', 'API\ListViewController@create')->name('list.create');
        Route::post('/delete', 'API\ListViewController@delete')->name('list.delete');
        Route::post('/update', 'API\ListViewController@update')->name('list.update');
        
        Route::get('/{id}', 'API\ListViewController@show')->name('list.show');
        Route::post('{id}/restart', 'API\ListViewController@restart')->name('list.restart');
    
        Route::prefix('{id}/item')->group(function () {
            Route::post('/create', 'API\GroceryListViewController@create')->name('list_item.create');
            Route::post('/update', 'API\GroceryListViewController@update')->name('list_item.update');
            Route::post('/delete', 'API\GroceryListViewController@delete')->name('list_item.delete');
        });
    });
    
    Route::get('promotion/{promotion_id}', 'API\PromotionController@index')->name('promotion.index');
    
    Route::prefix('search')->group(function () {
        Route::get('/suggestions/{query}', 'API\SearchViewController@suggestions')->name('search.suggestions');
        Route::post('/results', 'API\SearchViewController@results')->name('search.results');
    });

});

