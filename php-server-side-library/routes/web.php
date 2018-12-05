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
Route::get('/callback', 'Controller@HandleRedirect');
Route::get('/callback_wd', 'WdController@HandleRedirect');
Route::get('/sector_identifier_uri', 'Controller@GetSectorIdentifierUri');
Route::get('/sector_identifier_uri.json', 'Controller@GetSectorIdentifierUri');