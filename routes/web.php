<?php

use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| contains the "web" middleware group. Now create something great!
|
*/

// Route::get('/', function () {
//     return view('welcome');
// });

//Route::get('/', 'PagesController@root')->name('root');
Route::redirect('/', '/products')->name('root');
Route::get('products', 'ProductsController@index')->name('products.index');
//Route::get('products/{product}', 'ProductsController@show')->name('products.show');
/*
we move above route to the bottom, because products.favorites route has conflict with this
When laravel check the favorites(saved products) url against the routes, it will firstly match it with products.show, and regard the word 'favorites' as an product id
Another way to solve this problem is to add an condition to it using reguar expression
e.g  Route::get('products/{product}', 'ProductsController@show')->name('products.show')->where(['product' => '[0-9]+']);
*/
Route::get('products/{product}', 'ProductsController@show')->name('products.show')->where(['product' => '[0-9]+']);


Auth::routes(['verify' => true]);//Auth::routes() isadded by laravel, it will create routes for users'verification, 'verify' => true enables email verification 

//Route::get('/home', 'HomeController@index')->name('home');

//Auth requires user to login, verified requires user's email being verified
Route::group(['middleware' => ['auth', 'verified']], function(){
    Route::get('user_addresses', 'UserAddressesController@index')->name('user_addresses.index');
    Route::get('user_addresses/create', 'UserAddressesController@create')->name('user_addresses.create');
    Route::post('user_addresses', 'UserAddressesController@store')->name('user_addresses.store');
    Route::get('user_addresses/{user_address}', 'UserAddressesController@edit')->name('user_addresses.edit');
    Route::put('user_addresses/{user_address}', 'UserAddressesController@update')->name('user_addresses.update');
    Route::delete('user_addresses/{user_address}', 'UserAddressesController@destroy')->name('user_addresses.destroy');
    Route::post('products/{product}/favorite', 'ProductsController@favor')->name('products.favor');
    Route::delete('products/{product}/favorite', 'ProductsController@disfavor')->name('products.disfavor');
    Route::get('products/favorites', 'ProductsController@favorites')->name('products.favorites');
    Route::post('cart', 'CartController@add')->name('cart.add');
    Route::get('cart', 'CartController@index')->name('cart.index');
    Route::delete('cart/{sku}', 'CartController@remove')->name('cart.remove');
    Route::post('orders', 'OrdersController@store')->name('orders.store');
    Route::get('orders', 'OrdersController@index')->name('orders.index');
    try{
    Route::get('orders/{order}', 'OrdersController@show')->name('orders.show');
    }catch(\Throwable $t){
        dd($t);
    }
    Route::get('payment/{order}/alipay', 'PaymentController@payByAlipay')->name('payment.alipay');
    Route::get('payment/alipay/return', 'PaymentController@alipayReturn')->name('payment.alipay.return');//this can go through auth middle ware because browser stores our auth info
    Route::post('orders/{order}/received', 'OrdersController@received')->name('orders.received');
    Route::get('orders/{order}/review', 'OrdersController@review')->name('orders.review.show');
    Route::post('orders/{order}/review', 'OrdersController@sendReview')->name('orders.review.store');
    Route::post('orders/{order}/apply_refund', 'OrdersController@applyRefund')->name('orders.apply_refund');
    Route::get('coupon_codes/{code}', 'CouponCodesController@show')->name('coupon_codes.show');
    Route::post('crowdfunding_orders', 'OrdersController@crowdfunding')->name('crowdfunding_orders.store');
});

//This cannot go through auth middleware, because alipay sends this request to our back-end server instead of redirecting to a page, it doesn't contains any auth info 
Route::post('payment/alipay/notify', 'PaymentController@alipayNotify')->name('payment.alipay.notify');


//Route::get('products/{product}', 'ProductsController@show')->name('products.show');

//alipay test route
// Route::get('alipay', function(){
//     return app('alipay')->web([
//         'out_trade_no' => time(),
//         'total_amount' => 1,
//         'subject' => 'test subject - Test'
//     ]);
// });