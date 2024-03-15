<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Auth\DeleteUserController;

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



Auth::routes();

Route::get('/home', [App\Http\Controllers\HomeController::class, 'index'])->name('home');

Route::post('/delete-user', [App\Http\Controllers\Auth\DeleteUserController::class, 'deleteUser'])->name('delete.user');
Route::get('/user-delete', [App\Http\Controllers\Auth\DeleteUserController::class, 'userDelete'])->name('user-delete');

// Route::resource('users', UsersController::class);

// //Roles

// Route::resource('roles', RolesController::class);

// //Permission

// Route::resource('permission', PermissionsController::class);
