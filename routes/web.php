<?php

use App\Mail\AcceptedOntoProject;

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

Route::get('/project/{id}', 'ProjectController@show')->name('project.show');
Route::post('/project', 'ProjectController@store')->name('project.store');
Route::post('/project/{id}', 'ProjectController@update')->name('project.update');
Route::delete('/project/{id}', 'ProjectController@destroy')->name('project.delete');
Route::post('/project/{id}/accept-students', 'ProjectAcceptanceController@store')->name('project.accept_students');
Route::post('/choices', 'ChoiceController@store')->name('projects.choose');
Route::get('/thank-you', 'ChoiceController@thankYou')->name('thank_you');

Route::post('/bulk-accept', 'BulkAcceptanceController@store')->name('project.bulk_accept');
Route::delete('/course/{id}/remove-students', 'CourseMemberController@destroy')->name('course.remove_students');

Route::delete('/students/remove-undergrads', 'BulkRemovalController@undergrads')->name('students.remove_undergrads');
Route::delete('/students/remove-postgrads', 'BulkRemovalController@postgrads')->name('students.remove_postgrads');

Route::group(['middleware' => 'admin', 'prefix' => '/admin'], function () {
    Route::get('/projects', 'Admin\ProjectController@index')->name('admin.project.index');
});
