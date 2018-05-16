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


Auth::routes();

Route::group(['middleware' => 'auth'], function () {
    Route::redirect('/home', '/', 301);

    Route::get('/', 'HomeController@index')->name('home');

    Route::get('/project/create', 'ProjectController@create')->name('project.create');
    Route::get('/project/{id}', 'ProjectController@show')->name('project.show');
    Route::get('/project/{id}/copy', 'ProjectCopyController@create')->name('project.copy');
    Route::get('/project/{id}/edit', 'ProjectController@edit')->name('project.edit');

    Route::post('/project', 'ProjectController@store')->name('project.store');
    Route::post('/project/{id}', 'ProjectController@update')->name('project.update');
    Route::delete('/project/{id}', 'ProjectController@destroy')->name('project.delete');
    Route::post('/project/{id}/accept-students', 'ProjectAcceptanceController@store')->name('project.accept_students');

    Route::post('/choices', 'ChoiceController@store')->name('projects.choose');
    Route::get('/thank-you', 'ChoiceController@thankYou')->name('thank_you');

    Route::delete('/impersonate', 'Admin\ImpersonationController@destroy')->name('impersonate.stop');

    Route::group(['middleware' => 'admin', 'prefix' => '/admin'], function () {

        Route::post('/api/user/find', 'Api\UserController@show')->name('api.user.find');
        Route::post('/api/user', 'Api\UserController@store')->name('api.user.store');
        Route::post('/api/user/{id}', 'Api\UserController@update')->name('api.user.update');

        Route::get('/projects/{category}', 'Admin\ProjectController@index')->name('admin.project.index');
        Route::get('/choices/{category}', 'Admin\ChoiceController@index')->name('admin.student.choices');

        Route::get('/course', 'Admin\CourseController@index')->name('admin.course.index');
        Route::get('/course/create', 'Admin\CourseController@create')->name('admin.course.create');
        Route::get('/course/{course}', 'Admin\CourseController@show')->name('admin.course.show');
        Route::post('/course', 'Admin\CourseController@store')->name('admin.course.store');
        Route::get('/course/{course}/edit', 'Admin\CourseController@edit')->name('admin.course.edit');
        Route::post('/course/{course}', 'Admin\CourseController@update')->name('admin.course.update');
        Route::delete('/course/{course}', 'Admin\CourseController@destroy')->name('admin.course.destroy');

        Route::get('/course/{course}/enroll', 'Admin\EnrollmentController@create')->name('admin.course.enrollment');
        Route::post('/course/{course}/enrollment', 'Admin\EnrollmentController@store')->name('admin.course.enroll');
        Route::delete('/course/{id}/remove-students', 'Admin\EnrollmentController@destroy')->name('course.remove_students');

        Route::get('/programme', 'Admin\ProgrammeController@index')->name('admin.programme.index');
        Route::get('/programme/create', 'Admin\ProgrammeController@create')->name('admin.programme.create');
        Route::post('/programme', 'Admin\ProgrammeController@store')->name('admin.programme.store');
        Route::get('/programme/{id}', 'Admin\ProgrammeController@edit')->name('admin.programme.edit');
        Route::post('/programme/{id}', 'Admin\ProgrammeController@update')->name('admin.programme.update');
        Route::delete('/programme/{id}', 'Admin\ProgrammeController@destroy')->name('admin.programme.destroy');

        Route::post('/bulk-accept', 'Admin\BulkAcceptanceController@store')->name('project.bulk_accept');

        Route::post('/impersonate/{id}', 'Admin\ImpersonationController@store')->name('impersonate.start');

        Route::get('/users/{category}', 'Admin\UserController@index')->name('admin.users');
        Route::get('/user/{user}', 'Admin\UserController@show')->name('admin.user.show');
        Route::delete('/user/{user}', 'Admin\UserController@destroy')->name('admin.user.delete');

        Route::post('/user/{user}/toggle-admin', 'Admin\UserController@toggleAdmin')->name('admin.users.toggle_admin');

        Route::delete('/students/remove/undergrad', 'Admin\BulkRemovalController@undergrads')->name('students.remove_undergrads');
        Route::delete('/students/remove/postgrad', 'Admin\BulkRemovalController@postgrads')->name('students.remove_postgrads');
        Route::delete('/students/remove-all', 'Admin\BulkRemovalController@all')->name('students.remove_all');

        Route::get('/export/projects-excel', 'Admin\ExportController@projects')->name('export.projects.excel');

        Route::get('/gdpr/user/{user}', 'Gdpr\UserExportController@show')->name('gdpr.export.user');

        Route::get('/import/second-supervisors', 'Admin\SecondSupervisorController@show')->name('admin.import.second_supervisors.show');
        Route::post('/import/second-supervisors', 'Admin\SecondSupervisorController@store')->name('admin.import.second_supervisors');

        Route::post('/project/{project}/add-student', 'Admin\ManualAcceptanceController@store')->name('admin.project.add_student');
    });
});
