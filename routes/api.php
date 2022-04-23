<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

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

Route::middleware('auth:api')->get('/user', function (Request $request) {
    return $request->user();

});

Route::namespace ('App\Http\Controllers\API')->group(function () {
    Route::post('login', 'AdminController@login');
    Route::get('get-all-departments', 'AdminController@getAllDepartments');
    Route::get('get-all-courses/{dep_id}', 'AdminController@getAllCourses');
    Route::post('add-question-paper', 'AdminController@addQuestionPaper');
    Route::get('get-question-paper', 'AdminController@getAllQuestion');
    Route::post('get-student-question-paper', 'AdminController@getStudentQuestion');
    Route::post('accept-or-reject', 'AdminController@accptOrReject');

    
}

);
