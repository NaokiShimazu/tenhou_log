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
Route::get('/tenhou', 'TableController@view_table')->name('index');
Route::post('/add', 'TableController@add')->name('add');
Route::delete('/delete/{id}', 'TableController@destroy')->name('delete');
Route::get('/screenshot', 'TableController@screenshot')->name('screenshot');