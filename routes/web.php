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


Auth::routes();

Route::get('/home', 'HomeController@index')->name('home');

// Threads
Route::get('/threads', 'ThreadsController@index')->name('threads');
Route::get('threads/create', 'ThreadsController@create');
Route::get('/threads/{channel}/{thread}', 'ThreadsController@show');
Route::patch('/threads/{channel}/{thread}', 'ThreadsController@update');
Route::post('/threads', 'ThreadsController@store')->middleware('must-be-confirmed');
Route::delete('threads/{channel}/{thread}', 'ThreadsController@destroy');
Route::get('threads/{channel}', 'ThreadsController@index');

// Replies
Route::get('/threads/{channel}/{thread}/replies', 'RepliesController@index');
Route::post('/threads/{channel}/{thread}/replies', 'RepliesController@store');
Route::patch('/replies/{reply}', 'RepliesController@update');
Route::delete('/replies/{reply}', 'RepliesController@destroy')->name('replies.destroy');

// Favorites
Route::post('/replies/{reply}/favorites', 'FavoritesController@store');
Route::delete('/replies/{reply}/favorites', 'FavoritesController@destroy');

// Thread Subscriptions
Route::post('/threads/{channel}/{thread}/subscriptions', 'ThreadSubscriptionsController@store')->middleware('auth');
Route::delete('/threads/{channel}/{thread}/subscriptions', 'ThreadSubscriptionsController@destroy')->middleware('auth');

// User Profile
Route::get('/profiles/{user}', 'ProfilesController@show')->name('profile');

//User Notifications
Route::get('/profiles/{user}/notifications', 'UserNotificationsController@index');
Route::delete('/profiles/{user}/notifications/{notification}', 'UserNotificationsController@destroy');

// Confirm User Email Address on registration
Route::get('/register/confirm', 'Auth\RegisterConfirmationController@index')->name('register.confirm');

// Mark the best reply for a thread
Route::post('/replies/{reply}/best', 'BestRepliesController@store')->name('best-replies.store');

// An administrator can lock the thread
Route::post('locked-threads/{thread}', 'LockedThreadsController@store')->name('locked-threads.store')->middleware('admin');

// Search
Route::get('threads/search', 'SearchController@show');


// API

// Username Autocomplete
Route::get('api/users', 'Api\UsersController@index');
// User Avatar
Route::post('api/users/{user}/avatar', 'Api\UserAvatarController@store')->middleware('auth')->name('avatar');