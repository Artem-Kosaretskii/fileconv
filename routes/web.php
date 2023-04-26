<?php

use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\View;

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider and all of them will
| be assigned to the "web" middleware group. Make something great!
|
*/

Route::get('/',    fn () => View::make('app'));
Route::get('/users',    fn () => View::make('app'));
Route::get('/index',    fn () => View::make('app'));
Route::get('/upload',    fn () => View::make('app'));
