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

Route::get('/', function () {
    return view('welcome');
});

Route::get('login', 'Auth\AuthController@getLogin');
Route::post('login', 'Auth\AuthController@postLogin');
Route::get('logout', 'Auth\AuthController@getLogout');

Route::get('register', 'Auth\AuthController@getRegister');
Route::post('register', 'Auth\AuthController@postRegister');

Route::group(['prefix' => '/designer'], function () {
	Route::get("/", "DesignerController@create");
	Route::get("/edit/{routeId}", "DesignerController@edit");
	Route::post("/save", "DesignerController@save");
	Route::get("/search", "DesignerController@search");
});

Route::group(['prefix' => '/api'], function () {
	Route::post('/authenticate', 'ApiController@authenticate');
	Route::post('/globe_auth', 'ApiController@globeAuth');
	Route::get('/globe_auth', 'ApiController@globeAuth');
	Route::post('/globe_kaway', 'ApiController@globeKaway');
	
	Route::group(['prefix' => '/{apiKey}', 'middleware' => 'apiauth'], function() {
		Route::get('/test', 'ApiController@test');
		Route::get('/kaway/{stopCode}', 'ApiController@kaway');
		
		Route::group(['prefix' => '/routes'], function() {
			Route::get('/near/{latitude}/{longitude}', 'RoutesController@near');
			Route::get('/{routeId}/stops/{latitude}/{longitude}', 'RoutesController@stops');
			Route::get('/{routeId}/predict/{dateTime}', 'RoutesController@predict');
		});
		
		Route::group(['prefix' => '/stops'], function() {
		});
	});
});