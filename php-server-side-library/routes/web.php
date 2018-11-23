<?php

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

Route::get('/', function () {
    return view('welcome');
});

Route::get('/start_discovery', 'Controller@StartDiscovery');
Route::get('/start_discovery_manually', 'WdController@StartAuthenticationWithoutDiscovery');
Route::get('/start_authentication', 'Controller@StartAuthentication');
Route::get('/start_authorization', 'Controller@StartAuthorization');
Route::get('/start_wd_authentication', 'WdController@StartAuthentication');
Route::get('/start_wd_authorization', 'WdController@StartAuthorisation');
Route::get('/user_info', 'Controller@RequestUserInfo');
Route::get('/identity', 'Controller@RequestIdentity');
Route::get('/user_info_wd', 'WdController@RequestUserInfo');
Route::get('/identity_wd', 'WdController@RequestIdentity');
Route::get('/callback', 'Controller@HandleRedirect');
Route::get('/callback_wd', 'WdController@HandleRedirect');


