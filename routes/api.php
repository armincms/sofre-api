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

Route::post('{versiond}/sofre/order-validate', 'OrderValidationController@handle')->name('order.validate');
Route::post('{versiond}/sofre/order', 'OrderStoreController@handle')->name('order.store');

Route::get('setting', 'SettingController@show')->name('setting');

Route::middleware(config('snail.middleware', null))->post('{version}/{resource}/{resourceId}/comment', 'CommentStoreController@handle')->name('comment.store');

Route::middleware(config('snail.middleware', null))->post('{version}/{resource}/{resourceId}/rating', 'RatingStoreController@handle')->name('rating.store');
