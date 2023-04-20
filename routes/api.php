<?php

use App\Http\Controllers\Api\StudentController;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| is assigned the "api" middleware group. Enjoy building your API!
|
*/

// Note: all routes in this file are using the ApiKeyMiddleware - see Http/Kernel.php
Route::get('/students/{type}', [StudentController::class, 'index']);
