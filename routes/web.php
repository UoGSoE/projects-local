<?php

Auth::routes();

Route::group(['middleware' => 'auth'], function () {
    Route::redirect('/home', '/', 301);

    Route::get('/', [\App\Http\Controllers\HomeController::class, 'index'])->name('home');

    Route::get('/project/create', [\App\Http\Controllers\ProjectController::class, 'create'])->name('project.create');
    Route::get('/project/{id}', [\App\Http\Controllers\ProjectController::class, 'show'])->name('project.show');
    Route::get('/project/{id}/copy', [\App\Http\Controllers\ProjectCopyController::class, 'create'])->name('project.copy');
    Route::get('/project/{id}/edit', [\App\Http\Controllers\ProjectController::class, 'edit'])->name('project.edit');

    Route::post('/project', [\App\Http\Controllers\ProjectController::class, 'store'])->name('project.store');
    Route::post('/project/{id}', [\App\Http\Controllers\ProjectController::class, 'update'])->name('project.update');
    Route::delete('/project/{id}', [\App\Http\Controllers\ProjectController::class, 'destroy'])->name('project.delete');
    Route::post('/project/{id}/accept-students', [\App\Http\Controllers\ProjectAcceptanceController::class, 'store'])->name('project.accept_students');

    Route::post('/choices', [\App\Http\Controllers\ChoiceController::class, 'store'])->name('projects.choose');
    Route::get('/thank-you', [\App\Http\Controllers\ChoiceController::class, 'thankYou'])->name('thank_you');

    Route::delete('/impersonate', [\App\Http\Controllers\Admin\ImpersonationController::class, 'destroy'])->name('impersonate.stop');

    Route::group(['middleware' => 'admin', 'prefix' => '/admin'], function () {
        Route::post('/api/user/find', [\App\Http\Controllers\Api\UserController::class, 'show'])->name('api.user.find');
        Route::post('/api/user', [\App\Http\Controllers\Api\UserController::class, 'store'])->name('api.user.store');
        Route::post('/api/user/{id}', [\App\Http\Controllers\Api\UserController::class, 'update'])->name('api.user.update');

        Route::get('/projects/import', [\App\Http\Controllers\Admin\ImportOldProjectsController::class, 'show'])->name('import.show_importoldprojects');
        Route::post('/projects/import', [\App\Http\Controllers\Admin\ImportOldProjectsController::class, 'store'])->name('import.oldprojects');

        Route::get('/projects/dmoran_import', [\App\Http\Controllers\Admin\DaveMoranImportController::class, 'show'])->name('import.show_moran_importer');
        Route::post('/projects/dmoran_import', [\App\Http\Controllers\Admin\DaveMoranImportController::class, 'store'])->name('import.moran_importer');

        Route::get('/projects/{category}', [\App\Http\Controllers\Admin\ProjectController::class, 'index'])->name('admin.project.index');
        Route::get('/projects/{category}/options', [\App\Http\Controllers\Admin\ProjectOptionsController::class, 'index'])->name('admin.project.bulk-options');
        Route::post('/projects/{category}/options', [\App\Http\Controllers\Admin\ProjectOptionsController::class, 'update'])->name('admin.project.bulk-options.update');

        Route::post('/projects/toggle-editing', [\App\Http\Controllers\Admin\ProjectEditingToggleController::class, 'update'])->name('admin.project.toggle_editing');

        Route::get('/choices/{category}', [\App\Http\Controllers\Admin\ChoiceController::class, 'index'])->name('admin.student.choices');

        Route::get('/course', [\App\Http\Controllers\Admin\CourseController::class, 'index'])->name('admin.course.index');
        Route::get('/course/create', [\App\Http\Controllers\Admin\CourseController::class, 'create'])->name('admin.course.create');
        Route::get('/course/{course}', [\App\Http\Controllers\Admin\CourseController::class, 'show'])->name('admin.course.show');
        Route::post('/course', [\App\Http\Controllers\Admin\CourseController::class, 'store'])->name('admin.course.store');
        Route::get('/course/{course}/edit', [\App\Http\Controllers\Admin\CourseController::class, 'edit'])->name('admin.course.edit');
        Route::post('/course/{course}', [\App\Http\Controllers\Admin\CourseController::class, 'update'])->name('admin.course.update');
        Route::delete('/course/{course}', [\App\Http\Controllers\Admin\CourseController::class, 'destroy'])->name('admin.course.destroy');

        Route::get('/course/{course}/enroll', [\App\Http\Controllers\Admin\EnrollmentController::class, 'create'])->name('admin.course.enrollment');
        Route::post('/course/{course}/enrollment', [\App\Http\Controllers\Admin\EnrollmentController::class, 'store'])->name('admin.course.enroll');
        Route::delete('/course/{id}/remove-students', [\App\Http\Controllers\Admin\EnrollmentController::class, 'destroy'])->name('course.remove_students');

        Route::get('/programme', [\App\Http\Controllers\Admin\ProgrammeController::class, 'index'])->name('admin.programme.index');
        Route::get('/programme/create', [\App\Http\Controllers\Admin\ProgrammeController::class, 'create'])->name('admin.programme.create');
        Route::post('/programme', [\App\Http\Controllers\Admin\ProgrammeController::class, 'store'])->name('admin.programme.store');
        Route::get('/programme/{id}', [\App\Http\Controllers\Admin\ProgrammeController::class, 'edit'])->name('admin.programme.edit');
        Route::post('/programme/{id}', [\App\Http\Controllers\Admin\ProgrammeController::class, 'update'])->name('admin.programme.update');
        Route::delete('/programme/{id}', [\App\Http\Controllers\Admin\ProgrammeController::class, 'destroy'])->name('admin.programme.destroy');

        Route::post('/bulk-accept', [\App\Http\Controllers\Admin\BulkAcceptanceController::class, 'store'])->name('project.bulk_accept');
        Route::get('/import-allocations', [\App\Http\Controllers\Admin\ImportAllocationController::class, 'show'])->name('project.import.allocations-page');
        Route::post('/import-allocations', [\App\Http\Controllers\Admin\ImportAllocationController::class, 'store'])->name('project.import.allocations');

        Route::post('/impersonate/{id}', [\App\Http\Controllers\Admin\ImpersonationController::class, 'store'])->name('impersonate.start');

        Route::get('/users/{category}', [\App\Http\Controllers\Admin\UserController::class, 'index'])->name('admin.users');
        Route::get('/user/{user}', [\App\Http\Controllers\Admin\UserController::class, 'show'])->name('admin.user.show');
        Route::delete('/user/{user}', [\App\Http\Controllers\Admin\UserController::class, 'destroy'])->name('admin.user.delete');

        Route::get('/user/{user}/edit', [\App\Http\Controllers\Admin\UserController::class, 'edit'])->name('admin.user.edit');
        Route::post('/user/{user}/edit', [\App\Http\Controllers\Admin\UserController::class, 'update'])->name('admin.user.update');


        Route::post('/user/{user}/toggle-admin', [\App\Http\Controllers\Admin\UserController::class, 'toggleAdmin'])->name('admin.users.toggle_admin');

        Route::delete('/students/remove/undergrad', [\App\Http\Controllers\Admin\BulkRemovalController::class, 'undergrads'])->name('students.remove_undergrad');
        Route::delete('/students/remove/postgrad', [\App\Http\Controllers\Admin\BulkRemovalController::class, 'postgrads'])->name('students.remove_postgrad');
        Route::delete('/students/remove-all', [\App\Http\Controllers\Admin\BulkRemovalController::class, 'all'])->name('students.remove_all');

        /* Exports */
        Route::get('/export/projects/{category}/{format}', [\App\Http\Controllers\Admin\Exports\ProjectController::class, 'export'])
            ->name('export.projects');

        Route::get('/export/courses/{format}', [\App\Http\Controllers\Admin\Exports\CourseController::class, 'export'])->name('export.courses');
        Route::get('/export/programmes/{format}', [\App\Http\Controllers\Admin\Exports\ProgrammeController::class, 'export'])->name('export.programmes');

        Route::get('/export/staff/{format}', [\App\Http\Controllers\Admin\Exports\StaffController::class, 'export'])->name('export.staff');
        Route::get('/export/undergrad/{format}', [\App\Http\Controllers\Admin\Exports\StudentController::class, 'undergrad'])->name('export.undergrad');
        Route::get('/export/postgrad/{format}', [\App\Http\Controllers\Admin\Exports\StudentController::class, 'postgrad'])->name('export.postgrad');

        Route::get('/gdpr/user/{user}', [\App\Http\Controllers\Gdpr\UserExportController::class, 'show'])->name('gdpr.export.user');

        Route::get('/import/second-supervisors', [\App\Http\Controllers\Admin\SecondSupervisorController::class, 'show'])->name('admin.import.second_supervisors.show');
        Route::post('/import/second-supervisors', [\App\Http\Controllers\Admin\SecondSupervisorController::class, 'store'])->name('admin.import.second_supervisors');

        Route::get('/import/placements', [\App\Http\Controllers\Admin\PlacementController::class, 'show'])->name('admin.import.placements.show');
        Route::post('/import/placements', [\App\Http\Controllers\Admin\PlacementController::class, 'store'])->name('admin.import.placements');

        Route::post('/project/{project}/add-student', [\App\Http\Controllers\Admin\ManualAcceptanceController::class, 'store'])->name('admin.project.add_student');

        Route::get('/activity', [\App\Http\Controllers\Admin\ActivityLogController::class, 'index'])->name('admin.activitylog');

        Route::get('/researcharea', [\App\Http\Controllers\Admin\ResearchAreaController::class, 'index'])->name('researcharea.index');
        Route::post('/researcharea', [\App\Http\Controllers\Admin\ResearchAreaController::class, 'store'])->name('researcharea.store');
        Route::post('/researcharea/{area}', [\App\Http\Controllers\Admin\ResearchAreaController::class, 'update'])->name('researcharea.update');
        Route::delete('/researcharea/{area}', [\App\Http\Controllers\Admin\ResearchAreaController::class, 'destroy'])->name('researcharea.destroy');
    });
});
