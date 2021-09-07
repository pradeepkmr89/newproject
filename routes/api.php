<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;


Route::group([ 'prefix' => 'auth'], function () {
    Route::post('login', 'Api\UserApiController@login');
    Route::post('signup', 'Api\UserApiController@signup');
  
   Route::group([ 'middleware' => 'auth:api' ], function() {
        //Route::get('logout', 'AuthController@logout');
    Route::get('get/profile','Api\UserApiController@get_profile');
    Route::post('reset/password','Api\UserApiController@reset_password');
    Route::post('change/password','Api\UserApiController@change_password');
    Route::post('update/profile','Api\UserApiController@update_profile');

       
    }); 
}); 

