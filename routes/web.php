<?php

use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Route;
use App\User;
use Illuminate\Http\Request;

use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;


// Route to clear all cache
Route::get('/cc', function () {
    Artisan::call('cache:clear');
    Artisan::call('config:clear');
    Artisan::call('route:clear');
    Artisan::call('view:clear');
    Artisan::call('optimize:clear'); // This runs all the above at once
    
    return "All systems cleared and optimized!";
});
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
Route::post('/fraud-check', function (Request $request) {

    $request->validate([
        'phone'    => 'required|string',
        'order_id' => 'nullable|integer|exists:orders,id',
    ]);

    $phone  = $request->phone;
    $apiKey = env('BD_COURIER_FRAUD_CHECK_API');

    $ch = curl_init();

    curl_setopt($ch, CURLOPT_URL, 'https://api.bdcourier.com/courier-check');
    curl_setopt($ch, CURLOPT_POST, true);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_TIMEOUT, 30);

    curl_setopt($ch, CURLOPT_HTTPHEADER, [
        'Content-Type: application/json',
        'Authorization: Bearer ' . $apiKey,
    ]);

    curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode([
        'phone' => $phone
    ]));

    $response = curl_exec($ch);

    if ($response === false) {
        $error = curl_error($ch);
        curl_close($ch);

        return response()->json([
            'status' => 'error',
            'error'  => $error
        ], 500);
    }

    curl_close($ch);

    $decoded = json_decode($response, true);

    if (!is_array($decoded)) {
        return response()->json([
            'status' => 'error',
            'error'  => 'Invalid fraud-check API response',
        ], 502);
    }

    // Persist only a successful summary. A temporary API failure/no-data
    // response never wipes the last known fraud-check result.
    if (
        $request->filled('order_id') &&
        ($decoded['status'] ?? null) === 'success' &&
        isset($decoded['data']['summary']) &&
        Schema::hasColumn('orders', 'fraud_success_count') &&
        Schema::hasColumn('orders', 'fraud_fail_count')
    ) {
        $summary = $decoded['data']['summary'];

        DB::table('orders')
            ->where('id', (int) $request->order_id)
            ->update([
                'fraud_success_count' => (int) ($summary['success_parcel'] ?? 0),
                'fraud_fail_count'    => (int) ($summary['cancelled_parcel'] ?? 0),
            ]);
    }

    return response()->json($decoded);
})->middleware('auth');


Route::get('/', 'WebsiteController@index')->name('home');
Route::post('/newsletter/subscribe', 'NewsletterController@store')->middleware('throttle:6,1')->name('newsletter.subscribe');
Route::get('/product/{id}', 'WebsiteController@product')->name('product');
Route::get('/category/{slug}', 'WebsiteController@category')->name('category');
Route::get('/page/{slug}', 'WebsiteController@page')->name('page');
Route::get('/shop', 'WebsiteController@shop')->name('shop');
Route::get('/campaign/{slug}', 'WebsiteController@campaign')->name('campaign');

Route::get('/getProducts', 'WebsiteController@loadProducts')->name('loadProducts');

Route::resource('/checkout','CartController');
Route::get('/mini_cart','CartController@mini_cart')->name('mini_cart');
Route::get('/mini-cart-view','CartController@miniCart')->name('miniCart');
Route::post('/updateQuantity','CartController@updateQuantity')->name('updateQuantity');
Route::get('/updateDeliveryCharge','CartController@updateDeliveryCharge')->name('updateDeliveryCharge');
Route::post('/placeOrder','CartController@placeOrder')->name('placeOrder');
Route::post('/save-input','CartController@saveOrderInput')->name('saveOrderInput');
Route::get('/checkout/order-received/{id}','CartController@orderRecived')->name('placeOrder');
Route::get('/updateCartOptions', 'CartController@updateCartOptions')->name('updateCartOptions');
Route::post('/campaign-cart/product', 'CartController@updateCampaignProduct')->name('campaign.cart.product');
Route::post('/campaign-cart/quantity', 'CartController@updateCampaignQuantity')->name('campaign.cart.quantity');
Route::get('/campaign-cart/option', 'CartController@updateCampaignOption')->name('campaign.cart.option');
Route::post('/campaign-cart/color', 'CartController@updateCampaignColor')->name('campaign.cart.color');

//Route::get('/', function () {
//    return redirect('login');
//});

//Route::get('pathao', 'PathaoController@pathao')->name('pathao');
//Route::get('deliveryTiger', 'DeliveryTigerController@deliveryTiger')->name('deliveryTiger');
//Route::get('redx', 'PathaoController@redx')->name('redx');

Route::get('/user', function (){
    return redirect('user/dashboard');
});
Route::get('/admin', function (){
    return redirect('admin/dashboard');
});

Auth::routes(['except' => 'register']);

Route::group(['as' => 'admin.', 'prefix' => 'admin', 'namespace' => 'Admin'], function () {
    Route::get('media/get', 'MediaController@get')->name('get');
    Route::get('media/delete', 'MediaController@delete')->name('delete');
    Route::get('media/iframeget', 'MediaController@iframeget')->name('iframeget');
    Route::get('media/iframe', 'MediaController@iframe')->name('iframe');
    Route::resource('media', 'MediaController');
});
Route::group(['as' => 'admin.', 'prefix' => 'admin', 'namespace' => 'Admin', 'middleware' => ['auth', 'admin']], function () {

    // Newsletter subscriber management (Admin only)
    Route::resource('newsletter-subscribers', 'NewsletterSubscriberController')->except(['show']);

    Route::get('dashboard', 'DashboardController@index')->name('dashboard');
    Route::get('dashboard/getData', 'DashboardController@getData')->name('getData');
    Route::get('dashboard/stockOutProduct', 'DashboardController@stockOutProduct')->name('stockOutProduct');
    Route::get('dashboard/recentUpdate', 'DashboardController@recentUpdate')->name('recentUpdate');
    Route::get('dashboard/getNotification', 'DashboardController@getNotification')->name('getNotification');
    //shipping
    Route::get('/shipping', 'IpController@shipping')->name('shipping');
    //Pathao pathao_create
    Route::get('/pathao-create/{order_id}', 'IpController@pathao_create')->name('pathao_create');
    //IP
    Route::get('/ip', 'IpController@index')->name('ip.index');
    Route::get('/optimize', 'IpController@optimize')->name('ip.optimize');
    Route::post('/migrate', 'IpController@migrateDatabase')->name('migrate.force');
    Route::post('/ip/block', 'IpController@block')->name('ip.block');
    Route::delete('/ip/unblock','IpController@unblock')->name('ip.unblock');

    // Products
    Route::get('product/oldProductSync', 'ProductController@oldProductSync')->name('oldProductSync');
    Route::get('product/productSync', 'ProductController@productSync')->name('productSync');
    Route::post('product/image', 'ProductController@image')->name('image');
    Route::post('product/status', 'ProductController@status')->name('status');
    Route::post('product/category','ProductController@category');
    Route::post('product/color', 'ProductController@color');
    Route::post('product/size', 'ProductController@size');
    Route::post('product/option', 'ProductController@option');
    Route::get('product/delete','ProductController@delete')->name('delete');
    Route::resource('product', 'ProductController');
    Route::get('product/{id}/option-price','ProductController@productOptionPrice')->name('product.option-price');
    Route::post('product/{id}/update-option-price','ProductController@updateOptionPrice')->name('product.update-option-price');
    


    // Media
    // Route::get('media/get','MediaController@get')->name('get');
    // Route::get('media/delete','MediaController@delete')->name('delete');
    // Route::get('media/iframeget','MediaController@iframeget')->name('iframeget');
    // Route::get('media/iframe','MediaController@iframe')->name('iframe');
    // Route::resource('media','MediaController');
    
    

    // Category
    Route::post('category/status','CategoryController@status');
    Route::get('category/get','CategoryController@get');
    Route::get('category/delete','CategoryController@delete')->name('delete');
    Route::resource('category','CategoryController');
    
    // Section
    Route::post('section/status', 'SectionController@status'); // Custom route to change status
    Route::get('section/get', 'SectionController@get'); // Custom route to retrieve sizes
    Route::get('section/delete', 'SectionController@delete')->name('option.delete'); // Custom delete route
    Route::resource('section', 'SectionController'); // Standard resource routes
    
    
    // Option
    Route::post('option/status', 'OptionController@status'); // Custom route to change status
    Route::get('option/get', 'OptionController@get'); // Custom route to retrieve sizes
    Route::get('option/delete', 'OptionController@delete')->name('option.delete'); // Custom delete route
    Route::resource('option', 'OptionController'); // Standard resource routes
    
    // Size
    Route::post('size/status', 'SizeController@status'); // Custom route to change status
    Route::get('size/get', 'SizeController@get'); // Custom route to retrieve sizes
    Route::get('size/delete', 'SizeController@delete')->name('size.delete'); // Custom delete route
    Route::resource('size', 'SizeController'); // Standard resource routes
    
    // Color
    Route::post('color/status', 'ColorController@status'); // Custom route to change status
    Route::get('color/get', 'ColorController@get'); // Custom route to retrieve colors
    Route::get('color/delete', 'ColorController@delete')->name('color.delete'); // Custom delete route
    Route::resource('color', 'ColorController'); // Standard resource routes




    // Store
    Route::post('store/status', 'StoreController@status')->name('status');
    Route::resource('store', 'StoreController');

    // Supplier

    Route::post('supplier/status', 'SupplierController@status')->name('status');
    Route::resource('supplier', 'SupplierController');

    // Purchase
    Route::get('purchase/supplier', 'PurchaseController@supplier')->name('supplier');
    Route::get('purchase/product', 'PurchaseController@product')->name('product');
    Route::resource('purchase', 'PurchaseController');

    // Product Stock
    Route::resource('stock', 'StockController');

    // Notification
    Route::resource('notification', 'NotificationController');


    // Order

    Route::get('order/export', 'OrderController@export')->name('order.export');
    Route::get('order/import', 'OrderController@import')->name('order.import');
    Route::get('order/delete-selected', 'OrderController@deleteSelected')->name('order.deleteSelected');
    Route::get('order/empty-trash', 'OrderController@emptyTrash')->name('emptyTrash');
    Route::get('order/trash', 'OrderController@trashList')->name('orderTrashList');
    Route::put('/orders/{id}/restore', 'OrderController@restore')->name('orders.restore');
    Route::delete('/orders/{id}/forceDelete', 'OrderController@forceDelete')->name('orders.forceDelete');
    Route::get('order/deleteAll', 'OrderController@deleteAll')->name('deleteAll');
    Route::get('order/status', 'OrderController@status')->name('status');
    Route::get('order/orderSync', 'OrderController@orderSync')->name('orderSync');
    Route::get('order/view', 'OrderController@view')->name('view');

    Route::get('order/status/{status}', 'OrderController@ordersByStatus')->name('ordersByStatus');

    Route::get('order/assign', 'OrderController@assign')->name('assign');
    Route::get('order/changeStatusByCheckbox', 'OrderController@changeStatusByCheckbox')->name('changeStatusByCheckbox');
    Route::get('order/getNotes', 'OrderController@getNotes')->name('getNotes');
    Route::get('order/updateNotes', 'OrderController@updateNotes')->name('updateNotes');
    Route::get('order/oldOrders', 'OrderController@oldOrders')->name('oldOrders');


    Route::get('order/product', 'OrderController@product')->name('product');
    Route::get('order/stores', 'OrderController@stores')->name('stores');
    Route::get('order/users', 'OrderController@users')->name('users');
    Route::get('order/courier', 'OrderController@courier')->name('courier');
    Route::get('order/city', 'OrderController@city')->name('city');
    Route::get('order/zone', 'OrderController@zone')->name('zone');
    Route::get('order/paymenttype', 'OrderController@paymenttype')->name('paymenttype');
    Route::get('order/paymentnumber', 'OrderController@paymentnumber')->name('paymentnumber');


    Route::get('order/countOrders', 'OrderController@countOrders')->name('countOrders');
    Route::get('order/invoice', 'OrderController@invoice')->name('invoice');
    Route::get('order/storeInvoice', 'OrderController@storeInvoice')->name('storeInvoice');
    Route::get('order/invoice/{id}', 'OrderController@viewInvoice')->name('viewInvoice');
    Route::get('order/memoUpdate', 'OrderController@memoUpdate')->name('memoUpdate');



    // Send Sms
    Route::get('order/sendNumber', 'OrderController@sendNumber')->name('sendNumber');

    Route::resource('order', 'OrderController');

    // Order Type

    // Payment Type
    Route::post('payment/type/status', 'PaymentTypeController@status')->name('status');
    Route::resource('payment/type', 'PaymentTypeController');

    // Payment
    Route::get('payment/paymentType', 'PaymentController@paymentType')->name('paymentType');
    Route::post('payment/status', 'PaymentController@status')->name('status');
    Route::resource('payment', 'PaymentController');

    // Courier
    Route::post('courier/status', 'CourierController@status')->name('status');
    Route::resource('courier', 'CourierController');

    // City
    Route::post('city/status', 'CityController@status')->name('status');
    Route::get('city/courier', 'CityController@courier')->name('courier');
    Route::resource('city', 'CityController');

    // Zone
    Route::get('zone/courier', 'ZoneController@courier')->name('courier');
    Route::get('zone/city', 'ZoneController@city')->name('city');
    Route::post('zone/status', 'ZoneController@status')->name('status');
    Route::resource('zone', 'ZoneController');

    // User
    Route::get('user/users', 'UserController@users')->name('users');
    Route::get('user/role', 'UserController@role')->name('role');
    Route::get('user/status', 'UserController@status')->name('status');
    Route::get('user/login/{id}', function($id) {
       $user = User::find($id);;
        Auth::login($user);
        return redirect()->to('/login');
    })->name('admin');

    Route::resource('user', 'UserController');
    
    

    // Report
    Route::get('report/users', 'Report@users')->name('users');
    Route::get('report/dateCourierUser', 'Report@dateCourierUser')->name('dateCourierUser');
    Route::get('report/getOrdersOnDateCourierUser', 'Report@getOrdersOnDateCourierUser')->name('getOrdersOnDateCourierUser');
    Route::get('report/multipleDateCourierUser', 'Report@multipleDateCourierUser')->name('multipleDateCourierUser');
    Route::get('report/getMultipleDateCourierUser', 'Report@getMultipleDateCourierUser')->name('getMultipleDateCourierUser');
    Route::get('report/dateCourier', 'Report@dateCourier')->name('dateCourier');
    Route::get('report/getDateCourier', 'Report@getDateCourier')->name('getDateCourier');
    Route::get('report/dateUser', 'Report@dateUser')->name('dateUser');
    Route::get('report/getDateUser', 'Report@getDateUser')->name('getDateUser');
    Route::get('report/product', 'Report@product')->name('product');
    Route::get('report/getProduct', 'Report@getProduct')->name('getProduct');
    Route::get('report/payment', 'Report@payment')->name('payment');
    Route::get('report/getPayment', 'Report@getPayment')->name('getPayment');
    Route::get('report/paymentID', 'Report@paymentID')->name('paymentID');
    Route::get('report/paymentType', 'Report@paymentType')->name('paymentType');


    Route::get('menu', function () {
        return view('admin.menu');
    });

    Route::resource('page', 'PageController');
    Route::post('page/status', 'PageController@status')->name('status');

    Route::post('slider/status','SliderController@status');
    Route::get('slider/delete','SliderController@delete')->name('delete');
    Route::resource('slider', 'SliderController');


    Route::post('setting/getSlider', 'SettingController@getSlider')->name('getSlider');
    Route::resource('setting', 'SettingController');
    
    Route::get('campaign/status', 'CampaignController@status')->name('campaign.status');
    Route::resource('campaign', 'CampaignController');
    



});

Route::group(['as' => 'manager.', 'prefix' => 'manager', 'namespace' => 'Manager', 'middleware' => ['auth', 'manager']], function () {

    Route::get('dashboard', 'DashboardController@index')->name('dashboard');
    Route::get('dashboard/getData', 'DashboardController@getData')->name('getData');
    Route::get('dashboard/stockOutProduct', 'DashboardController@stockOutProduct')->name('stockOutProduct');
    Route::get('dashboard/recentUpdate', 'DashboardController@recentUpdate')->name('recentUpdate');
    Route::get('dashboard/getNotification', 'DashboardController@getNotification')->name('getNotification');


    //
    //IP
    Route::get('/ip', 'IpController@index')->name('ip.index');
    Route::get('/optimize', 'IpController@optimize')->name('ip.optimize');
    Route::post('/ip/block', 'IpController@block')->name('ip.block');
    Route::delete('/ip/unblock','IpController@unblock')->name('ip.unblock');
    
    // Products
    Route::get('product/productSync', 'ProductController@productSync')->name('productSync');
    Route::post('product/image', 'ProductController@image')->name('image');
    Route::post('product/status', 'ProductController@status')->name('status');
    Route::post('product/category','ProductController@category');
    Route::post('product/color', 'ProductController@color');
    Route::post('product/size', 'ProductController@size');
    Route::post('product/option', 'ProductController@option');
    Route::resource('product', 'ProductController');
    Route::get('product/{id}/option-price','ProductController@productOptionPrice')->name('product.option-price');
    Route::post('product/{id}/update-option-price','ProductController@updateOptionPrice')->name('product.update-option-price');

    // Category
    Route::post('category/status','CategoryController@status');
    Route::get('category/get','CategoryController@get');
    Route::get('category/delete','CategoryController@delete')->name('delete');
    Route::resource('category','CategoryController');
    
    // Section
    Route::post('section/status', 'SectionController@status'); // Custom route to change status
    Route::get('section/get', 'SectionController@get'); // Custom route to retrieve sizes
    Route::get('section/delete', 'SectionController@delete')->name('option.delete'); // Custom delete route
    Route::resource('section', 'SectionController'); // Standard resource routes
    
    
    // Option
    Route::post('option/status', 'OptionController@status'); // Custom route to change status
    Route::get('option/get', 'OptionController@get'); // Custom route to retrieve sizes
    Route::get('option/delete', 'OptionController@delete')->name('option.delete'); // Custom delete route
    Route::resource('option', 'OptionController'); // Standard resource routes
    
    // Size
    Route::post('size/status', 'SizeController@status'); // Custom route to change status
    Route::get('size/get', 'SizeController@get'); // Custom route to retrieve sizes
    Route::get('size/delete', 'SizeController@delete')->name('size.delete'); // Custom delete route
    Route::resource('size', 'SizeController'); // Standard resource routes
    
    // Color
    Route::post('color/status', 'ColorController@status'); // Custom route to change status
    Route::get('color/get', 'ColorController@get'); // Custom route to retrieve colors
    Route::get('color/delete', 'ColorController@delete')->name('color.delete'); // Custom delete route
    Route::resource('color', 'ColorController'); // Standard resource routes
    
    
    
    // Store
    Route::post('store/status', 'StoreController@status')->name('status');
    Route::resource('store', 'StoreController');

    // Supplier

    Route::post('supplier/status', 'SupplierController@status')->name('status');
    Route::resource('supplier', 'SupplierController');

    // Purchase
    Route::get('purchase/supplier', 'PurchaseController@supplier')->name('supplier');
    Route::get('purchase/product', 'PurchaseController@product')->name('product');
    Route::resource('purchase', 'PurchaseController');

    // Product Stock
    Route::resource('stock', 'StockController');

    // Notification
    Route::resource('notification', 'NotificationController');


    // Order

    Route::get('order/import', 'OrderController@import')->name('order.import');
    Route::get('order/export', 'OrderController@export')->name('order.export');
    Route::get('order/deleteAll', 'OrderController@deleteAll')->name('deleteAll');
    Route::get('order/status', 'OrderController@status')->name('status');
    Route::get('order/orderSync', 'OrderController@orderSync')->name('orderSync');
    Route::get('order/view', 'OrderController@view')->name('view');

    Route::get('order/status/{status}', 'OrderController@ordersByStatus')->name('ordersByStatus');

    Route::get('order/assign', 'OrderController@assign')->name('assign');
   
    Route::get('order/changeStatusByCheckbox', 'OrderController@changeStatusByCheckbox')->name('changeStatusByCheckbox');
    Route::get('order/getNotes', 'OrderController@getNotes')->name('getNotes');
    Route::get('order/updateNotes', 'OrderController@updateNotes')->name('updateNotes');
    Route::get('order/oldOrders', 'OrderController@oldOrders')->name('oldOrders'); 


    Route::get('order/product', 'OrderController@product')->name('product');
    Route::get('order/stores', 'OrderController@stores')->name('stores');
    Route::get('order/users', 'OrderController@users')->name('users');
    Route::get('order/courier', 'OrderController@courier')->name('courier');
    Route::get('order/city', 'OrderController@city')->name('city');
    Route::get('order/zone', 'OrderController@zone')->name('zone');
    Route::get('order/paymenttype', 'OrderController@paymenttype')->name('paymenttype');
    Route::get('order/paymentnumber', 'OrderController@paymentnumber')->name('paymentnumber');


    Route::get('order/countOrders', 'OrderController@countOrders')->name('countOrders');
    Route::get('order/invoice', 'OrderController@invoice')->name('invoice');
    Route::get('order/storeInvoice', 'OrderController@storeInvoice')->name('storeInvoice');
    Route::get('order/invoice/{id}', 'OrderController@viewInvoice')->name('viewInvoice');
    Route::get('order/memoUpdate', 'OrderController@memoUpdate')->name('memoUpdate');



    // Send Sms
    Route::get('order/sendNumber', 'OrderController@sendNumber')->name('sendNumber');

    Route::resource('order', 'OrderController');

    // Order Type

    // Payment Type
    Route::post('payment/type/status', 'PaymentTypeController@status')->name('status');
    Route::resource('payment/type', 'PaymentTypeController');

    // Payment
    Route::get('payment/paymentType', 'PaymentController@paymentType')->name('paymentType');
    Route::post('payment/status', 'PaymentController@status')->name('status');
    Route::resource('payment', 'PaymentController');

    // Courier
    Route::post('courier/status', 'CourierController@status')->name('status');
    Route::resource('courier', 'CourierController');

    // City
    Route::post('city/status', 'CityController@status')->name('status');
    Route::get('city/courier', 'CityController@courier')->name('courier');
    Route::resource('city', 'CityController');

    // Zone
    Route::get('zone/courier', 'ZoneController@courier')->name('courier');
    Route::get('zone/city', 'ZoneController@city')->name('city');
    Route::post('zone/status', 'ZoneController@status')->name('status');
    Route::resource('zone', 'ZoneController');

    // User
    Route::get('user/users', 'UserController@users')->name('users');
    Route::get('user/role', 'UserController@role')->name('role');
    Route::get('user/status', 'UserController@status')->name('status');
    Route::resource('user', 'UserController');

    // Report
    Route::get('report/users', 'Report@users')->name('users');
    Route::get('report/dateCourierUser', 'Report@dateCourierUser')->name('dateCourierUser');
    Route::get('report/getOrdersOnDateCourierUser', 'Report@getOrdersOnDateCourierUser')->name('getOrdersOnDateCourierUser');
    Route::get('report/multipleDateCourierUser', 'Report@multipleDateCourierUser')->name('multipleDateCourierUser');
    Route::get('report/getMultipleDateCourierUser', 'Report@getMultipleDateCourierUser')->name('getMultipleDateCourierUser');
    Route::get('report/dateCourier', 'Report@dateCourier')->name('dateCourier');
    Route::get('report/getDateCourier', 'Report@getDateCourier')->name('getDateCourier');
    Route::get('report/dateUser', 'Report@dateUser')->name('dateUser');
    Route::get('report/getDateUser', 'Report@getDateUser')->name('getDateUser');
    
    Route::get('report/product', 'Report@product')->name('product');
    Route::get('report/getProduct', 'Report@getProduct')->name('getProduct');
    
    Route::get('report/payment', 'Report@payment')->name('payment');
    Route::get('report/getPayment', 'Report@getPayment')->name('getPayment');
    Route::get('report/paymentID', 'Report@paymentID')->name('paymentID');
    Route::get('report/paymentType', 'Report@paymentType')->name('paymentType');});
    


Route::group(['as' => 'user.', 'prefix' => 'user', 'namespace' => 'User', 'middleware' => ['auth', 'user']], function () {
    Route::get('dashboard', 'DashboardController@index')->name('dashboard');
    Route::get('dashboard/getData', 'DashboardController@getData')->name('getData');


    // Order

    Route::get('order/deleteAll', 'OrderController@deleteAll')->name('deleteAll');
    Route::get('order/status', 'OrderController@status')->name('status');
    Route::get('order/orderSync', 'OrderController@orderSync')->name('orderSync');
    Route::get('order/view', 'OrderController@view')->name('view');

    Route::get('order/status/{status}', 'OrderController@ordersByStatus')->name('ordersByStatus');

    Route::get('order/assign', 'OrderController@assign')->name('assign');
    Route::get('order/changeStatusByCheckbox', 'OrderController@changeStatusByCheckbox')->name('changeStatusByCheckbox');
    Route::get('order/getNotes', 'OrderController@getNotes')->name('getNotes');
    Route::get('order/updateNotes', 'OrderController@updateNotes')->name('updateNotes');
    Route::get('order/oldOrders', 'OrderController@oldOrders')->name('oldOrders');


    Route::get('order/product', 'OrderController@product')->name('product');
    Route::get('order/stores', 'OrderController@stores')->name('stores');
    Route::get('order/courier', 'OrderController@courier')->name('courier');
    Route::get('order/city', 'OrderController@city')->name('city');
    Route::get('order/zone', 'OrderController@zone')->name('zone');
    Route::get('order/paymenttype', 'OrderController@paymenttype')->name('paymenttype');
    Route::get('order/paymentnumber', 'OrderController@paymentnumber')->name('paymentnumber');

    Route::get('order/complain', 'OrderController@complain')->name('complain');
    Route::get('order/complainOrder', 'OrderController@complainOrder')->name('complainOrder');


    Route::get('order/countOrders', 'OrderController@countOrders')->name('countOrders');
    Route::get('order/invoice', 'OrderController@invoice')->name('invoice');
    Route::get('order/storeInvoice', 'OrderController@storeInvoice')->name('storeInvoice');
    Route::get('order/invoice/{id}', 'OrderController@viewInvoice')->name('viewInvoice');
    Route::get('order/memoUpdate', 'OrderController@memoUpdate')->name('memoUpdate');

    // Send Sms
    Route::get('order/sendNumber', 'OrderController@sendNumber')->name('sendNumber');
    Route::resource('order', 'OrderController');
});
