<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Tool API Routes
|--------------------------------------------------------------------------
|
| Here is where you may register API routes for your tool. These routes
| are loaded by the ServiceProvider of your tool. They are protected
| by your tool's "Authorize" middleware by default. Now, go build!
|
*/

Route::get('restaurant-classes', 'RestaurantClassController@index')->name('class.index');

Route::get('restaurant-classes/{id}', 'RestaurantClassController@restaurants')->name('class.restaurants');

Route::get('restaurant/{id}', 'RestaurantController@show')->name('show');
