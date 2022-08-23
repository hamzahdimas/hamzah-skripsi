<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\HomeController;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\RoleController;
use App\Http\Controllers\UserController;

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
Route::get('/user/create','App\Http\Controllers\AuthController@createUser');
Route::group(['middleware' => ['ifLogged','web']], function () {
    Route::get('/','App\Http\Controllers\AuthController@index');
    Route::post('/doLogin','App\Http\Controllers\AuthController@doLogin');
    Route::get('/doLogout','App\Http\Controllers\AuthController@doLogout');
});

Route::group(['middleware' => ['authLogin','web']], function () {
    Route::get('/dashboard','App\Http\Controllers\HomeController@index');

    //Item
    Route::get('/item','App\Http\Controllers\ItemController@index');
    Route::get('/item/load','App\Http\Controllers\ItemController@loadData');
    Route::get('/item/create','App\Http\Controllers\ItemController@createItem');
    Route::post('/item/insert','App\Http\Controllers\ItemController@insertItem');
    Route::get('/item/edit/{id}','App\Http\Controllers\ItemController@editItem');
    Route::post('/item/update/{id}','App\Http\Controllers\ItemController@updateItem');
    Route::get('/item/delete/{id}','App\Http\Controllers\ItemController@deleteItem');
    Route::get('/item/get/{id}','App\Http\Controllers\ItemController@getItem');

    Route::get('/transaksi','App\Http\Controllers\TransaksiController@index');
    Route::post('/transaksi/insert','App\Http\Controllers\TransaksiController@addTransaksi');
    Route::get('/transaksi/list','App\Http\Controllers\TransaksiController@list');
    Route::get('/transaksi/load','App\Http\Controllers\TransaksiController@loadData');
    Route::get('/transaksi/nota/{id}','App\Http\Controllers\TransaksiController@nota');
    Route::get('/transaksi/delete/{id}','App\Http\Controllers\TransaksiController@destroy');
});



Route::get('/home', [App\Http\Controllers\HomeController::class, 'index'])->name('home');

  
Route::group(['middleware' => ['auth']], function() {
    Route::resource('roles', RoleController::class);
    Route::resource('users', UserController::class);
    Route::resource('products', ProductController::class);
});


Route::get('/home', [App\Http\Controllers\HomeController::class, 'index'])->name('home');
