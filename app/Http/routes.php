<?php

/*
|--------------------------------------------------------------------------
| Application Routes
|--------------------------------------------------------------------------
|
| Here is where you can register all of the routes for an application.
| It's a breeze. Simply tell Laravel the URIs it should respond to
| and give it the controller to call when that URI is requested.
|
*/

Route::get('/', 'IndexController@Index');
Route::get('/name', 'NamesController@index');
Route::get('/test', 'OtherController@index');
Route::get('/t', 'OtherController@test');
Route::get('/p', 'OtherController@p');