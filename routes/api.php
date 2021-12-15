<?php

use Illuminate\Support\Facades\Route;
//header('Access-Control-Allow-Methods: GET, POST, PATCH, PUT, DELETE, OPTIONS');
//header('Access-Control-Allow-Headers: Origin, Content-Type, X-Auth-Token, Authorization, Accept,charset,boundary,Content-Length');
//header('Access-Control-Allow-Origin: *');

Route::get('invoice/{id}', 'CourierController@downloadInvoicePdf');
Route::get('downloadpdf/{id}', 'CourierController@downloadInvoicePdf');
Route::get('courieraccountcron', 'AccountController@courierAccountCron');
Route::get('downloadinvoice', 'AccountController@downloadInvoice');
Route::get('searchpincode/{pincode}', 'PincodeController@searchpincode');
    


Route::group(['prefix' => 'auth'], function () {

    Route::group(['middleware' => ['auth:api']], function () {
        Route::get('logout', 'UserAuthController@logoutUser');
        Route::get('refreshUser', 'UserAuthController@refreshUser');
        Route::post('changePassword', 'UserAuthController@changePassword');
    });
    Route::post('createUser', 'UserAuthController@createUser');
    Route::post('login', 'UserAuthController@loginUser');
    Route::get('sendForgotPassword/{email}', 'PasswordActionsController@sendForgotPassword');
    Route::post('validateForgotPassword', 'PasswordActionsController@validateForgotPassword');
    Route::post('resetForgotPassword', 'PasswordActionsController@resetForgotPassword');
    Route::get('UnAuthenticated', 'LaravelThingsController@unAuthenticatedResponse')->name('UnAuthenticated');
});

Route::get('track_courier/{awb_number}', 'CourierController@trackCourier');

Route::get('statuslist', 'CourierController@statuslist');

Route::group(['middleware' => ['auth:api']], function () {
    
    Route::resource('couriers', CourierController::class);
    Route::get('rate', 'CourierController@getRates');
    Route::get('rates', 'CourierController@getRates');
    Route::get('area', 'CourierController@getArea');

    Route::get('viewcourierwithid','CourierController@getCourierwithid');
    
    Route::get('gettrakinglist', 'CourierController@getTrakingList');

    Route::get('courier_report', 'CourierController@courierReport');
   // Route::get('view_courier/{id}','AdminController@viewCourierDetails');
    Route::get('view_courier/{id}','CourierController@getCourierShowPdf');
    

    Route::get('downloadpdf/{id}','CourierController@getCourierDownloadPdf');
    Route::delete('users/{id}', 'UserController@deleteTodoById');
    Route::resource('users', UserController::class);
    Route::get('usersmainlist/{role}', 'UserController@usersmainlist');
    Route::post('update_user', 'UserController@updateUser');
    Route::get('users/details/{id}', 'UserController@getUserDetail');
    Route::get('users_list/{type}', 'UserController@userList');
    Route::get('parentlist/{id}', 'UserController@parentlist');
    Route::get('deleteuser/{id}', 'UserController@deleteUser');
    Route::get('/courier_sales_counts/{year}', 'AdminController@getCourierSalesCount');
    Route::get('/dashboard_counts', 'AdminController@getDashboarCounts');
    Route::get('/is_pincode_exist', 'PincodeController@isPincodeExist');
    Route::get('listofdelaers', 'UserController@franchiserList');
    Route::get('actionloglist', 'AdminController@ActionlogList');
    Route::post('addaccountstatement', 'AccountController@addAccountstatement');
    ///api for shopify
    Route::post('addcourier', 'CourierController@addCourier');
    Route::get('addfranchise', 'UserController@addfranchise');
  

});

Route::group(['middleware' => ['auth:api']], function () {
    
    Route::post('updatecourier','CourierController@updateCourier');
    Route::get('viewcourier/{id}','CourierController@getCourier');

});


//accounting 
Route::post('courierOrder', 'CourierOrderController@store');
Route::group(['middleware' => ['auth:api']], function () {

    Route::get('accountingreport', 'AccountController@courierAccountReport');
    Route::get('getaccountstatementreport', 'AccountController@getAccountstatementreport');
    
    
});

//APIs for Courier Orders Booking
Route::group(['middleware' => ['auth:api']], function () {
    Route::post('courierOrder/reject/{id}', 'CourierOrderController@rejectOrder');
    Route::post('courierOrder/proceed/{id}', 'CourierOrderController@proceedOrder');
    Route::get('courierOrder/{status}', 'CourierOrderController@courierOrderList');
    Route::post('addcouriertraking', 'CourierController@addCourierTraking');
    Route::post('addpod', 'CourierController@AddPod');
    Route::get('deletecourier/{id}', 'CourierController@deleteCourier');
    
    
});
//APIs for pincode state Booking
Route::group(['middleware' => ['auth:api']], function () {
    Route::resource('pincodes', PincodeController::class);
    Route::get('state_list', 'PincodeController@stateList');
    Route::post('addstateprice', 'PincodeController@addStateprice');
    Route::post('editstateprice', 'PincodeController@editStateprice');
    Route::post('addspecialpincodeprice', 'PincodeController@addSpecialpincodeprice');
    Route::get('getstatepricelist', 'PincodeController@getStatePricelist');
    Route::get('getspecialpricelist', 'PincodeController@getSpecialPricelist');
    Route::post('editspecialpincodeprice', 'PincodeController@editSpecialpincodeprice');
    Route::get('deletespecialprice/{id}', 'PincodeController@deleteSpecialprice');
    Route::get('deletestatepricelist/{id}', 'PincodeController@deleteStatepricelist');
    Route::post('smstosender', 'AdminController@sendFST2SMStosender');
    Route::get('getpincodelist', 'PincodeController@getpincodeList');
    Route::get('mycouriers', 'CourierController@myCouriers');
    Route::get('userlist', 'UserController@userList');
    
    
});
//APIs for Userpermission 
Route::group(['middleware' => ['auth:api']], function () {
    
    Route::get('permissionlist', 'UserController@permissionlist');
    Route::get('userpermission', 'UserController@Userpermission');
    Route::post('adduserpermission', 'UserController@addUserpermission');
    Route::post('updateuserpermission', 'UserController@UpdateUserpermission');
   
    
});


Route::any('{anything}', 'LaravelThingsController@anyRoute');
